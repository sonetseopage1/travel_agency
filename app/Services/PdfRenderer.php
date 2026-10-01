<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

/**
 * Renders a Blade view to PDF using headless Chrome.
 *
 * Bengali is the reason this exists. Bengali conjuncts are not precomposed
 * Unicode characters; they are assembled at render time by OpenType shaping
 * tables in the font. dompdf performs no shaping and draws the halant as its
 * own visible glyph, so গ্রাহকের renders as গ্ রা হ ক ে র with dangling marks.
 * mPDF implements shaping but cannot parse the GSUB chaining-contextual
 * lookups that Noto Sans Bengali relies on, and aborts with a FontException.
 * Chrome shapes correctly, which was verified by confirming that the halant
 * glyph is never drawn on its own in its output.
 *
 * The view is rendered to a self-contained HTML file with the fonts inlined as
 * data URIs, so Chrome needs no filesystem access to resolve them.
 */
class PdfRenderer
{
    /** Where the Chrome executable was found, cached per request. */
    private static ?string $binary = null;

    /**
     * Render a view to raw PDF bytes.
     *
     * @param  string  $view  the Blade view to render
     * @param  array<string, mixed>  $data  view data
     */
    public function render(string $view, array $data = []): string
    {
        $html = view($view, $data)->render();

        $disk = Storage::disk(config('pdf.disk'));
        $dir = trim((string) config('pdf.temp_directory'), '/').'/'.Str::uuid()->toString();
        $disk->makeDirectory($dir);

        $htmlPath = $dir.'/receipt.html';
        $pdfPath = $dir.'/receipt.pdf';

        // The Chrome profile is a few hundred small files, so it goes to the
        // system temp directory rather than bloating the application's disk.
        $profileDir = rtrim(sys_get_temp_dir(), '/\\').'/banglatraveller-chrome-'.Str::uuid()->toString();

        try {
            // Fonts are inlined so the page is fully self-contained; Chrome
            // then needs no flag granting it access to local files.
            $disk->put($htmlPath, $this->inlineFonts($html));
            $this->makeDirectory($profileDir);

            $this->printToPdf($htmlPath, $pdfPath, $profileDir);

            if (! $disk->exists($pdfPath)) {
                throw new RuntimeException('Chrome did not produce a PDF for the receipt.');
            }

            return (string) $disk->get($pdfPath);
        } finally {
            $this->cleanUp($disk, $dir, $htmlPath, $pdfPath, $profileDir);
        }
    }

    /**
     * Remove everything the render wrote.
     *
     * The rendered HTML and PDF contain the customer's name, phone and email,
     * so they are deleted first and unconditionally. Chrome can still be
     * releasing handles on its profile when the process exits, so that part is
     * retried and treated as best effort rather than allowed to fail a render.
     */
    private function cleanUp(
        Filesystem $disk,
        string $dir,
        string $htmlPath,
        string $pdfPath,
        string $profileDir
    ): void {
        $disk->delete([$htmlPath, $pdfPath]);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $disk->deleteDirectory($dir);

            if (! $disk->exists($dir)) {
                break;
            }

            usleep(200_000);
        }

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->removeDirectory($profileDir);

            if (! is_dir($profileDir)) {
                break;
            }

            usleep(200_000);
        }
    }

    /**
     * Recursively delete a directory tree, tolerating locked entries.
     */
    private function removeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $entries = scandir($dir) ?: [];

        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }

            $path = $dir.'/'.$entry;

            // is_dir follows symlinks, so a link is unlinked rather than
            // followed, which keeps this from escaping the temp directory.
            if (is_link($path) || ! is_dir($path)) {
                @unlink($path);
            } else {
                $this->removeDirectory($path);
            }
        }

        @rmdir($dir);
    }

    /**
     * Create a directory, including any missing parents.
     */
    private function makeDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            mkdir($dir, 0o700, true);
        }
    }

    /**
     * Rewrite @font-face sources to inline base64 data URIs.
     */
    private function inlineFonts(string $html): string
    {
        return (string) preg_replace_callback(
            '#url\(\s*[\'"]?(file://)?/?([A-Za-z]:[\\\\/][^\'")]+)[\'"]?\s*\)#',
            function (array $m): string {
                $path = $this->normalisePath($m[2]);

                if (! is_file($path)) {
                    return "url('{$m[0]}')";
                }

                $data = base64_encode((string) file_get_contents($path));

                return "url('data:font/ttf;base64,{$data}')";
            },
            $html
        );
    }

    /**
     * Turn a native path into a readable one, whatever separator it uses.
     */
    private function normalisePath(string $path): string
    {
        $path = str_replace('file:///', '', $path);
        $path = preg_replace('#^file://#', '', $path) ?? $path;

        if (preg_match('#^[A-Za-z]:/#', $path)) {
            return str_replace('/', '\\', $path);
        }

        return $path;
    }

    /**
     * Drive Chrome's print-to-pdf against the rendered HTML.
     */
    private function printToPdf(string $htmlPath, string $pdfPath, string $profileDir): void
    {
        $root = Storage::disk(config('pdf.disk'))->path('');

        $htmlFile = $this->absolutePath($root, $htmlPath);
        $pdfFile = $this->absolutePath($root, $pdfPath);

        // The profile is already an absolute system temp path.
        $uri = 'file:///'.str_replace('\\', '/', $htmlFile);

        $process = new Process([
            $this->chromeBinary(),
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--no-first-run',
            '--no-default-browser-check',
            // Chrome otherwise prints its own header and footer with the
            // URL and page numbers on top of the document.
            '--no-pdf-header-footer',
            // Without a virtual time budget the print can happen before the
            // inlined fonts have finished decoding.
            '--virtual-time-budget=10000',
            '--user-data-dir='.$profileDir,
            '--print-to-pdf='.$pdfFile,
            $uri,
        ]);

        $process->setTimeout((float) config('pdf.timeout', 30));

        try {
            $process->mustRun();
        } catch (ProcessTimedOutException $e) {
            throw new RuntimeException('Rendering the receipt PDF timed out.', 0, $e);
        } catch (RuntimeException $e) {
            throw new RuntimeException(
                'Chrome failed to render the receipt PDF: '.$e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Join the storage root with a relative path, without doubling separators.
     */
    private function absolutePath(string $root, string $relative): string
    {
        $root = rtrim(str_replace('\\', '/', $root), '/');
        $relative = ltrim(str_replace('\\', '/', $relative), '/');

        return str_replace('/', DIRECTORY_SEPARATOR, $root.'/'.$relative);
    }

    /**
     * Locate the Chrome executable, or explain how to point at it.
     */
    public function chromeBinary(): string
    {
        if (self::$binary !== null) {
            return self::$binary;
        }

        $configured = config('pdf.chrome');

        if (is_string($configured) && $configured !== '') {
            $resolved = $this->resolveBinary($configured);

            if ($resolved !== null) {
                return self::$binary = $resolved;
            }
        }

        foreach ((array) config('pdf.chrome_candidates', []) as $candidate) {
            $resolved = $this->resolveBinary((string) $candidate);

            if ($resolved !== null) {
                return self::$binary = $resolved;
            }
        }

        throw new RuntimeException(
            'No Chrome executable was found for PDF rendering. Set PDF_CHROME_BINARY to the '
            .'full path of a Chrome or Chromium binary.'
        );
    }

    /**
     * Resolve a candidate path, accepting either a real file or a PATH command.
     */
    private function resolveBinary(string $candidate): ?string
    {
        if ($candidate === '') {
            return null;
        }

        // An explicit path that exists is used as-is.
        if (str_contains($candidate, '/') || str_contains($candidate, '\\')) {
            return is_file($candidate) ? $candidate : null;
        }

        // Otherwise treat it as a command name and let the shell resolve it.
        $lookup = PHP_OS_FAMILY === 'Windows' ? 'where' : 'command -v';

        try {
            $process = Process::fromShellCommandline($lookup.' '.escapeshellarg($candidate));
            $process->run();

            if (! $process->isSuccessful()) {
                return null;
            }

            // `where` can print several matches; the first is the one used.
            $found = trim((string) strtok($process->getOutput(), "\r\n"));

            return $found !== '' ? $found : null;
        } catch (\Throwable) {
            return null;
        }
    }
}

<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Headless Chrome executable
    |--------------------------------------------------------------------------
    |
    | PDFs are rendered by Chrome rather than a pure-PHP library because
    | Bengali conjuncts (গ্র, হ্ক, স্ট্য, শ্চ) are produced by OpenType shaping,
    | which no PHP PDF engine in this project implements. Chrome uses the same
    | shaping engine as the browser, so the receipt matches the site.
    |
    | The value may be a full path, or any of the command names below may be
    | found on PATH. The first one that resolves is used.
    |
    */

    'chrome' => env('PDF_CHROME_BINARY'),

    'chrome_candidates' => [
        // Windows
        'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
        'C:\\Program Files (x86)\\Google\\Chrome\\Application\\chrome.exe',
        // macOS
        '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
        '/Applications/Chromium.app/Contents/MacOS/Chromium',
        // Linux
        '/usr/bin/google-chrome',
        '/usr/bin/google-chrome-stable',
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
        '/snap/bin/chromium',
    ],

    /*
    |--------------------------------------------------------------------------
    | Paper
    |--------------------------------------------------------------------------
    |
    | Chrome honours the @page rule in the view, which is where the real
    | page size and margins are set. These are the fallbacks used to build the
    | print call.
    |
    */

    'paper' => 'A4',

    /*
    |--------------------------------------------------------------------------
    | Rendering timeout and working directory
    |--------------------------------------------------------------------------
    |
    | Chrome is given a private profile directory per render so concurrent
    | requests cannot collide over the default user data directory. Temporary
    | files are written to the Laravel filesystem under the disk below.
    |
    */

    'timeout' => (int) env('PDF_TIMEOUT', 30),

    'disk' => 'local',

    'temp_directory' => 'pdf',

];

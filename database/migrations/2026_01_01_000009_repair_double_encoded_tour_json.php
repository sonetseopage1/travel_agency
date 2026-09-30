<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The shape each column is supposed to hold.
     *
     * 'scalars' columns hold lists of strings, 'records' columns hold lists of
     * objects. Knowing the intended shape lets the repair tell real data apart
     * from a wrongly nested array instead of guessing.
     *
     * `features` is listed as both because the seeded data is inconsistent:
     * some tours store a list of labels, others a map of booleans.
     */
    private array $shapes = [
        'gallery' => ['scalars'],
        'includes' => ['scalars'],
        'excludes' => ['scalars'],
        'important_info' => ['scalars'],
        'itinerary' => ['records'],
        'faqs' => ['records'],
        'features' => ['map', 'scalars'],
    ];

    public function up(): void
    {
        $columns = array_keys($this->shapes);
        $fixed = 0;

        foreach (DB::table('tours')->select('id', ...$columns)->cursor() as $tour) {
            $updates = [];

            foreach ($columns as $column) {
                $decoded = $this->normalise($tour->{$column}, $this->shapes[$column]);

                if ($decoded !== null) {
                    $updates[$column] = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
            }

            if ($updates !== []) {
                DB::table('tours')->where('id', $tour->id)->update($updates);
                $fixed++;
            }
        }

        if ($fixed > 0) {
            $this->write("Repaired malformed JSON on {$fixed} tour(s).");
        }
    }

    public function down(): void
    {
        // The original values cannot be recovered, so this is intentionally a no-op.
    }

    /**
     * Decode a stored JSON value, undoing extra encoding layers.
     *
     * Repairs two shapes:
     *  - a JSON string wrapping JSON, e.g. "\"[\"a\"]\"", which is what the admin
     *    form produced by encoding on top of the model's array cast;
     *  - a list wrapped one level too deep, e.g. "[[a, b]]", or trailing scalars
     *    left over from a bad repair run.
     *
     * Wrapping is only undone when the nested value matches the column's
     * expected shape, so genuine data such as a one-day itinerary is untouched.
     *
     * @return mixed|null The corrected value, or null when nothing was wrong.
     */
    private function normalise(?string $raw, array $shapes): mixed
    {
        $raw = trim((string) $raw);

        if ($raw === '' || $raw === 'null') {
            return null;
        }

        $value = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        // Peel off repeated string wrapping: "\"[\"a\"]\"" -> "[\"a\"]" -> ["a"]
        while (is_string($value) && $this->looksLikeJson($value)) {
            $inner = json_decode($value, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                break;
            }

            $value = $inner;
        }

        // Discard trailing scalars left by a bad run: [["a", "b"], true]
        if (is_array($value) && $this->matchesShape($value, $shape)) {
            return $this->sameAsStored($raw, $value) ? null : $value;
        }

        $nested = is_array($value) ? array_values(array_filter($value, 'is_array')) : [];

        if (count($nested) === 1) {
            $candidate = $this->matchesShape($nested[0], $shape) ? $nested[0] : null;

            // Only collapse when the outer list held nothing but the junk.
            if ($candidate !== null && (count($value) === 1 || count($value) > count($nested))) {
                return $this->sameAsStored($raw, $candidate) ? null : $candidate;
            }
        }

        return null;
    }

    /**
     * Does a decoded value look like what this column is supposed to store?
     */
    private function matchesShape(mixed $value, array $shapes): bool
    {
        if (! is_array($value) || $value === []) {
            return false;
        }

        $isList = array_is_list($value);

        foreach ($shapes as $shape) {
            $ok = match ($shape) {
                'scalars' => $isList && ! array_filter($value, 'is_array'),
                'records' => $isList && ! $this->isList(reset($value)),
                'map' => ! $isList,
                default => false,
            };

            if ($ok) {
                return true;
            }
        }

        return false;
    }

    private function isList(mixed $value): bool
    {
        return is_array($value) && array_is_list($value);
    }

    private function looksLikeJson(string $value): bool
    {
        $value = trim($value);

        return $value !== ''
            && in_array(ltrim($value)[0] ?? '', ['[', '{'], true)
            && json_decode($value) !== null;
    }

    /**
     * True when the stored value already represents $value, so no write is needed.
     */
    private function sameAsStored(string $raw, mixed $value): bool
    {
        $stored = json_decode($raw, true);

        if (json_last_error() === JSON_ERROR_NONE && $stored === $value) {
            return true;
        }

        // Compare canonical JSON so key order and escaping do not trigger a write.
        return json_encode($stored) === json_encode($value);
    }

    private function write(string $message): void
    {
        if (function_exists('info')) {
            info($message);
        }
    }
};

<?php

declare(strict_types=1);

namespace App\Support;

use Closure;
use Illuminate\Support\Str;

final class SlugGenerator
{
    /**
     * @param  Closure(string): bool  $exists  returns true when the slug is already taken
     */
    public function unique(string $name, Closure $exists): string
    {
        // Non-Latin names (e.g. Bangla) can slugify to an empty string.
        $base = Str::slug($name) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while ($exists($slug)) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}

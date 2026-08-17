<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Symfony\Component\Finder\Finder;

class SupportedLocale implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $files = Finder::create()
                ->files()
                ->in(lang_path('spectacular'))
                ->name('*.json');

        foreach ($files as $file) {
            if ($file->getBasename('.json') === $value) {
                return;
            }
        }

        $fail(__('validation.in', ['attribute' => $attribute]));
    }
}

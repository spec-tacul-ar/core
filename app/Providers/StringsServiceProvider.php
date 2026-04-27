<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use League\HTMLToMarkdown\HtmlConverter;

class StringsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * Escape Markdown characters.
         * This can cause double escapes.
         **/
        Str::macro('escapeMarkdown', function (string $string) {
            $characters = ['\\', '`', '*', '_', '{', '}', '[', ']', '(', ')', '#', '+', '!', '>', '|', '~', '^'];

            $replacements = array_map(fn($character) => '\\' . $character, $characters);

            return str_replace($characters, $replacements, $string);
        });

        /* Convert HTML to Markdown */
        $converter = new HtmlConverter(['strip_tags' => true]);

        Str::macro('htmlToMarkdown', fn(string $html) => $converter->convert($html));
    }
}

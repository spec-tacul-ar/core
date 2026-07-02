@props(['title' => config('app.name')])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <meta name="color-scheme" content="light dark">
        <script>
            (function () {
                const media = window.matchMedia('(prefers-color-scheme: dark)');

                const applyTheme = () => document.documentElement.classList.toggle(
                    'dark',
                    localStorage.theme === 'dark'
                    || (!('theme' in localStorage) && media.matches),
                );

                applyTheme();

                media.addEventListener('change', applyTheme);
            })();
        </script>
        <link rel="icon" type="image/png" href="/favicon-96x96.png" sizes="96x96" />
        <link rel="icon" type="image/svg+xml" href="/favicon.svg" />
        <link rel="shortcut icon" href="/favicon.ico" />
        <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png" />
        <meta name="apple-mobile-web-app-title" content="Spectacular" />
        <link rel="manifest" href="/site.webmanifest" />
        <title>{{ $title }}</title>
        {{ $head ?? '' }}
    </head>
    <body class="bg-gray-50 text-gray-800 dark:bg-gray-950 dark:text-gray-400">
        {{ $slot }}
    </body>
</html>

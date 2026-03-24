<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1.0">
        <title>{{ config('app.name') }}</title>
        @vite(['resources/js/app.js'])

        <style>
            @font-face {
              font-family: 'Funnel Sans';
              src: url('/fonts/FunnelSans-VariableFont_wght.woff2') format('woff2');
              font-weight: 100 900;
              font-style: normal;
              font-display: swap;
            }

            @font-face {
              font-family: 'Funnel Sans';
              src: url('/fonts/FunnelSans-Italic-VariableFont_wght.woff2') format('woff2');
              font-weight: 100 900;
              font-style: italic;
              font-display: swap;
            }

            body {
                margin: 0;
                color: #1e2939;
                background-color: #f9fafb;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Crect x='7.5' width='0.5' height='16' fill='%23f3f4f6'/%3E%3Crect y='7.5' width='16' height='0.5' fill='%23f3f4f6'/%3E%3Crect width='0.5' height='16' fill='%23edeff2'/%3E%3Crect width='16' height='0.5' fill='%23edeff2'/%3E%3C/svg%3E");
                background-repeat: repeat;
                background-size: auto;
                background-attachment: fixed;
            }

            @media print {
                body {
                    background-color: #ffffff;
                    background-image: none;
                }
            }

            #splash {
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                margin: 0 1.5rem;
                font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", "Liberation Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            }

            #logo {
                width: auto;
                height: 55px;
            }

            #app {
                height: 100vh;
            }

            #app.hide {
                display: none;
            }
        </style>
    </head>
    <body>
        <noscript>
            <strong>We're sorry but {{ config('app.name') }} doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
        </noscript>

        <div id="splash">
            <img src="/images/logo.svg" id="logo">
        </div>

        <div id="app" class="hide"></div>
    </body>
</html>

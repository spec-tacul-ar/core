@use('Laravel\Fortify\Features')

<x-layout>
    <x-slot:head>
        <script>
            window.Spectacular = {{ Js::from([
                'registration' => Features::enabled(Features::registration()),
                'socialite' => array_keys(array_filter(config('spectacular.socialite'))),
                'verification' => Features::enabled(Features::emailVerification()),
            ]) }};
        </script>

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

            .dark body {
                color: #9ca3af;
                background-color: #030712;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 16 16'%3E%3Crect x='7.5' width='0.5' height='16' fill='%23111827'/%3E%3Crect y='7.5' width='16' height='0.5' fill='%23111827'/%3E%3Crect width='0.5' height='16' fill='%231f2937'/%3E%3Crect width='16' height='0.5' fill='%231f2937'/%3E%3C/svg%3E");
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

            .dark #logo {
                filter: brightness(0) invert(1);
            }

            #app {
                height: 100vh;
            }

            #app.hide {
                display: none;
            }
        </style>
    </x-slot:head>

    <noscript>
        <strong>We're sorry but {{ config('app.name') }} doesn't work properly without JavaScript enabled. Please enable it to continue.</strong>
    </noscript>

    <div id="splash">
        <img src="/images/logo.svg" id="logo">
    </div>

    <div id="app" class="hide"></div>
</x-layout>

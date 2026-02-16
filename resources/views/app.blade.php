<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" @class(['dark' => ($appearance ?? 'light') === 'dark'])>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        {{-- Apply persisted appearance immediately and default to light --}}
        <script>
            (function() {
                const serverAppearance = '{{ $appearance ?? "light" }}';
                let storedAppearance = null;

                try {
                    storedAppearance = localStorage.getItem('appearance');
                } catch (error) {
                    storedAppearance = null;
                }

                const appearance = storedAppearance === 'dark' || storedAppearance === 'light'
                    ? storedAppearance
                    : serverAppearance;

                document.documentElement.classList.toggle('dark', appearance === 'dark');
            })();
        </script>

        {{-- Inline style to avoid background flash before CSS loads --}}
        <style>
            html {
                background-color: hsl(210 40% 98%);
            }

            html.dark {
                background-color: hsl(224 36% 8%);
            }
        </style>

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

        @routes
        @vite(['resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead
    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>

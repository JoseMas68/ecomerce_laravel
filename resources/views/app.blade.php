<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'PawfectShop') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Material Symbols -->
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" />

        <!-- Vite -->
        @vite('resources/js/app.js')

        <!-- Styles -->
        <style>
            body {
                font-family: 'Plus Jakarta Sans', sans-serif;
            }

            .material-symbols-outlined {
                font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            }

            [data-inertia] .material-symbols-outlined {
                font-variation-settings: inherit;
            }
        </style>

        <!-- Inertia Head -->
        @inertiaHead
    </head>
    <body class="bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100">
        @inertia
    </body>
</html>

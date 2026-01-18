<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Agenda Sacramental')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <!-- Debug: Si ves esto, el HTML carga pero Vue no -->
    <noscript>JavaScript está deshabilitado</noscript>
    @yield('content')
    @stack('scripts')
</body>
</html>

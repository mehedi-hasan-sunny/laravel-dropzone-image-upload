<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('header')
    </head>
    <body>
        @yield('content')
        @yield('scripts')
    </body>
</html>

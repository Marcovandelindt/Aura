<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />

    <title>{{ config('APP.NAME', 'Aura') }}</title>

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body>
    <div id="app-wrapper">
        @include('layouts.partials.sidebar')
        <div class="app-content">
            @yield('content')
        </div>
    </div>
</body>

</html>
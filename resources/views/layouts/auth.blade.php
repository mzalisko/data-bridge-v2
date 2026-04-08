<!DOCTYPE html>
<html lang="uk" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DataBridge CRM')</title>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="auth-body">

    <div class="auth-center">
        @yield('content')
    </div>

    <script src="{{ asset('assets/js/layout.js') }}"></script>
</body>
</html>

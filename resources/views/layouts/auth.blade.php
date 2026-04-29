<!DOCTYPE html>
<html lang="uk" class="vibeB vibe">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DataBridge CRM')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">
</head>
<body class="auth-body">

    <div class="auth-center">
        @yield('content')
    </div>

    <script src="{{ asset('assets/js/layout.js') }}"></script>
</body>
</html>

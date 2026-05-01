<!DOCTYPE html>
<html lang="uk" {{ request()->cookie('theme') === 'dark' ? 'data-theme="dark"' : '' }}>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'DataBridge CRM')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script>
        (function(){
            try {
                var ck = document.cookie.split('; ').find(function(r){return r.indexOf('theme=')===0;});
                var theme = ck ? ck.split('=')[1] : (localStorage.getItem('theme') || 'light');
                if (theme === 'dark') document.documentElement.setAttribute('data-theme','dark');
            } catch(e) {}
        })();
    </script>
    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}?v={{ filemtime(public_path('assets/css/app.css')) }}">
</head>
<body class="auth-body">
    @yield('content')
</body>
</html>

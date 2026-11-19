<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Kumburus')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container nav-wrap">
            <a class="brand" href="{{ route('home') }}"><span class="brand-mark">K</span><span>Kumburus</span></a>
            <nav class="nav-links">
                <a href="{{ route('home') }}">Find a stay</a>
                @auth
                    <a href="{{ route('bookings.index') }}">My bookings</a>
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.hotels.index') }}">Admin</a>
                    @endif
                    <span class="nav-user">Hi, {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="inline-form">
                        @csrf
                        <button class="link-button" type="submit">Sign out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}">Sign in</a>
                    <a class="button button-small" href="{{ route('register') }}">Join Kumburus</a>
                @endauth
            </nav>
        </div>
    </header>

    <main>
        @if(session('success'))
            <div class="container"><div class="flash flash-success">{{ session('success') }}</div></div>
        @endif
        @if(session('error'))
            <div class="container"><div class="flash flash-error">{{ session('error') }}</div></div>
        @endif
        @if($errors->any())
            <div class="container">
                <div class="flash flash-error">
                    <strong>Please check the form.</strong>
                    <ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-wrap"><span>© {{ date('Y') }} Kumburus</span><span>Simple stays, carefully booked.</span></div>
    </footer>
</body>
</html>

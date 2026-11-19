@extends('layouts.app')

@section('title', 'Sign in — Kumburus')

@section('content')
<section class="auth-shell"><div class="auth-card"><p class="eyebrow">WELCOME BACK</p><h1>Sign in to Kumburus</h1><p class="muted">Your next stay is closer than you think.</p><form method="POST" action="{{ route('login.store') }}" class="stack-form">@csrf<label>Email address<input type="email" name="email" value="{{ old('email') }}" required autofocus></label><label>Password<input type="password" name="password" required></label><label class="checkbox"><input type="checkbox" name="remember" value="1"> Remember me</label><button class="button button-full" type="submit">Sign in</button></form><p class="form-footer">New here? <a href="{{ route('register') }}">Create an account</a></p></div></section>
@endsection

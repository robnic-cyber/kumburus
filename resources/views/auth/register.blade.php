@extends('layouts.app')

@section('title', 'Create an account — Kumburus')

@section('content')
<section class="auth-shell"><div class="auth-card"><p class="eyebrow">MAKE YOURSELF AT HOME</p><h1>Create your account</h1><p class="muted">Save your bookings and keep every trip detail together.</p><form method="POST" action="{{ route('register.store') }}" class="stack-form">@csrf<label>Your name<input type="text" name="name" value="{{ old('name') }}" required autofocus></label><label>Email address<input type="email" name="email" value="{{ old('email') }}" required></label><label>Password<input type="password" name="password" required></label><label>Confirm password<input type="password" name="password_confirmation" required></label><button class="button button-full" type="submit">Create account</button></form><p class="form-footer">Already have an account? <a href="{{ route('login') }}">Sign in</a></p></div></section>
@endsection

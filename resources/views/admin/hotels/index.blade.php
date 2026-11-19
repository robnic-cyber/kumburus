@extends('layouts.app')

@section('title', 'Manage hotels — Kumburus')

@section('content')
<section class="container section-space"><div class="admin-heading"><div><p class="eyebrow">ADMIN CONSOLE</p><h1>Manage stays</h1><p class="muted">Create, update, and archive the rooms guests can book.</p></div><a class="button" href="{{ route('admin.hotels.create') }}">Add hotel <span>＋</span></a></div><div class="admin-list">@forelse($hotels as $hotel)<article class="admin-hotel"><div><span class="status status-{{ $hotel->is_active ? 'confirmed' : 'cancelled' }}">{{ $hotel->is_active ? 'Active' : 'Archived' }}</span><h2>{{ $hotel->name }}</h2><p>{{ $hotel->city }}, {{ $hotel->country }} · {{ $hotel->rooms_count }} rooms</p></div><div class="admin-actions"><a class="button button-small button-outline" href="{{ route('admin.rooms.create', $hotel) }}">Add room</a><a class="button button-small button-outline" href="{{ route('admin.hotels.edit', $hotel) }}">Edit hotel</a>@if($hotel->is_active)<form method="POST" action="{{ route('admin.hotels.destroy', $hotel) }}" class="inline-form">@csrf @method('DELETE')<button class="link-button danger" type="submit">Archive</button></form>@endif</div></article>@empty<div class="empty-state"><h3>No hotels yet.</h3><a class="button" href="{{ route('admin.hotels.create') }}">Add your first hotel</a></div>@endforelse</div></section>
@endsection

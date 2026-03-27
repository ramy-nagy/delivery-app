@php($title = 'Admin - ' . ucfirst($resource))

@extends('admin.layouts.app')

@section('content')
    <div class="nav">
        <a href="{{ route('admin.restaurants') }}" class="{{ $resource === 'restaurants' ? 'active' : '' }}">Restaurants</a>
        <a href="{{ route('admin.shops') }}" class="{{ $resource === 'shops' ? 'active' : '' }}">Shops</a>
        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn ghost">Logout</button>
        </form>
    </div>

    <h1 style="margin-top:18px;">{{ ucfirst($resource) }}</h1>

    @livewire($componentClass)
@endsection


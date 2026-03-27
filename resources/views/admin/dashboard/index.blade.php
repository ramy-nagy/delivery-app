@php($title = 'Admin Dashboard')

@extends('admin.layouts.app')

@section('content')
    <div class="nav">
        <a href="{{ route('admin.restaurants') }}" class="{{ request()->routeIs('admin.restaurants') ? 'active' : '' }}">Restaurants</a>
        <a href="{{ route('admin.shops') }}" class="{{ request()->routeIs('admin.shops') ? 'active' : '' }}">Shops</a>
        <form method="POST" action="{{ route('admin.logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn ghost">Logout</button>
        </form>
    </div>

    <h1 style="margin-top:18px;">Admin Dashboard</h1>

    <p style="color:#444;">Use the CRUD screens to manage multi-vendor vendors. Soft-deleted records can be restored.</p>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px;">
        <div style="border:1px solid #e5e5e5; border-radius:12px; padding:14px;">
            <h3 style="margin:0 0 8px;">Restaurants</h3>
            @livewire(\App\Livewire\Admin\Statics\RestaurantStats::class)
        </div>

        <div style="border:1px solid #e5e5e5; border-radius:12px; padding:14px;">
            <h3 style="margin:0 0 8px;">Shops</h3>
            @livewire(\App\Livewire\Admin\Statics\ShopStats::class)
        </div>
    </div>
@endsection


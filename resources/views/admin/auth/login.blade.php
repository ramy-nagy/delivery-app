@php($title = 'Admin Login')

@extends('admin.layouts.app')

@section('content')
    <h1 style="margin-top:0;">Admin Login</h1>

    @if ($errors->any())
        <div class="error">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <div class="field">
            <label>Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required>
        </div>
        <div class="field">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        <div style="margin-top:16px;">
            <button class="btn primary" type="submit">Login</button>
        </div>
    </form>
@endsection


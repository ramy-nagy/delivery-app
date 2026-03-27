<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $title ?? config('app.name', 'Laravel') }} (Admin)</title>

        @livewireStyles
        <style>
            body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif; margin: 0; padding: 20px; background: #f6f6f6; }
            .container { max-width: 1100px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 12px; }
            .nav a { margin-right: 12px; text-decoration: none; color: #111; }
            .nav a.active { font-weight: 700; }
            table { width: 100%; border-collapse: collapse; margin-top: 12px; }
            th, td { border: 1px solid #e5e5e5; padding: 10px; font-size: 14px; }
            th { background: #fafafa; text-align: left; }
            .row-actions button { margin-right: 6px; }
            .btn { display: inline-block; padding: 7px 10px; border-radius: 8px; border: 1px solid #dcdcdc; background: #fff; cursor: pointer; }
            .btn.primary { background: #111; color: #fff; border-color: #111; }
            .btn.danger { background: #b91c1c; color: #fff; border-color: #b91c1c; }
            .btn.ghost { background: transparent; }
            .error { color: #b91c1c; margin: 6px 0; }
            .field { margin-top: 10px; }
            .field label { display: block; font-size: 12px; color: #444; margin-bottom: 4px; }
            input[type="text"], input[type="email"], textarea, select, input[type="number"] {
                width: 100%; padding: 8px; border: 1px solid #dcdcdc; border-radius: 8px;
            }
            textarea { min-height: 80px; }
        </style>
    </head>
    <body>
        <div class="container">
            @yield('content')
        </div>

        @livewireScripts
        @stack('scripts')
    </body>
</html>


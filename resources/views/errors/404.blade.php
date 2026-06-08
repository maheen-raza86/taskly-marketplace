<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Not Found — Taskly</title>
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <style>
        .error-page {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: calc(100vh - 56px);
            padding: 2rem 1rem;
            text-align: center;
        }
        .error-code {
            font-size: 7rem;
            font-weight: 800;
            color: #e5e7eb;
            line-height: 1;
            margin-bottom: .25rem;
            letter-spacing: -4px;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: .65rem;
        }
        .error-msg {
            font-size: .95rem;
            color: #6b7280;
            max-width: 420px;
            line-height: 1.65;
            margin-bottom: 2rem;
        }
        .error-actions {
            display: flex;
            gap: .85rem;
            flex-wrap: wrap;
            justify-content: center;
        }
        .btn-home {
            display: inline-block;
            padding: .65rem 1.5rem;
            background: #4f46e5;
            color: #fff;
            border-radius: 8px;
            font-size: .9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background .15s;
        }
        .btn-home:hover { background: #4338ca; text-decoration: none; }
        .btn-back {
            display: inline-block;
            padding: .65rem 1.5rem;
            background: #f3f4f6;
            color: #374151;
            border-radius: 8px;
            font-size: .9rem;
            font-weight: 600;
            text-decoration: none;
            border: 1.5px solid #e5e7eb;
            cursor: pointer;
            font-family: inherit;
            transition: background .15s;
        }
        .btn-back:hover { background: #e5e7eb; text-decoration: none; }
    </style>
</head>
<body>

@include('layouts.navbar')

<div class="error-page">
    <div class="error-code">404</div>
    <h1 class="error-title">Page not found</h1>
    <p class="error-msg">
        The page you're looking for doesn't exist or may have been moved.
        Let's get you back on track.
    </p>
    <div class="error-actions">
        <a href="{{ route('home') }}" class="btn-home">← Go Home</a>
        <button onclick="history.back()" class="btn-back">Go Back</button>
    </div>
</div>

</body>
</html>

<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <title>{{ $title ?? 'Mikabouzu Mini' }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body{font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;background:#f6f7fb;margin:0;}
    .wrap{max-width:900px;margin:24px auto;padding:0 16px;}
    .card{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:16px;margin-bottom:16px;}
    .row{display:flex;justify-content:space-between;align-items:center;}
    .btn{display:inline-block;padding:6px 10px;border-radius:8px;border:1px solid #c7cdd8;background:#fff;cursor:pointer;font-size:12px}
    .btn.primary{background:#4f46e5;color:#fff;border-color:#4f46e5}
    .btn.success{background:#059669;color:#fff;border-color:#059669}
    .tag{font-size:11px;padding:2px 8px;border-radius:999px;border:1px solid #e5e7eb;background:#f3f4f6}
    .muted{color:#6b7280;font-size:12px}
    .input, select, textarea{width:100%;padding:8px;border:1px solid #d1d5db;border-radius:8px}
    .mb8{margin-bottom:8px}.mb12{margin-bottom:12px}.mt8{margin-top:8px}.mt16{margin-top:16px}
    .err{color:#dc2626;font-size:12px}
    .flash{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0;padding:8px;border-radius:8px;margin-bottom:12px}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="row mb12">
      <h1 style="margin:0;font-size:20px;">app</h1>
      <div>
      <a class="btn" href="{{ route('dashboard') }}">Dashboard</a>
      <a class="btn primary" href="{{ route('plans.create') }}">New Plan</a>
      </div>
    </div>

    @if (session('status')) <div class="flash">{{ session('status') }}</div> @endif
    @yield('content')
  </div>
</body>
</html>

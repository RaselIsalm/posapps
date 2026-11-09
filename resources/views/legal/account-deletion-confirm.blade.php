<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f8f8">
    <meta name="referrer" content="no-referrer">
    <title>Confirm account deletion · ONEPOS</title>
    <style>
        :root{color-scheme:light;--brand:#308e87;--ink:#18303c;--muted:#60727e;--line:#e2ebeb;--surface:#fff}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;background:#f4f8f8;color:var(--ink);font:16px/1.6 Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}main{width:min(100%,540px);padding:clamp(22px,5vw,40px);border:1px solid var(--line);border-radius:24px;background:var(--surface);box-shadow:0 18px 55px #173e4712}.brand{color:var(--brand);font-size:14px;font-weight:800;letter-spacing:.12em;text-decoration:none}h1{margin:20px 0 10px;font-size:30px;line-height:1.15;letter-spacing:-.04em}p{color:var(--muted)}button{width:100%;min-height:50px;margin-top:16px;border:0;border-radius:12px;background:var(--brand);color:white;font:inherit;font-weight:700;cursor:pointer}.notice{padding:12px 14px;border-radius:12px;background:#fff3e8;color:#8a3b08}a{color:#207a73}
    </style>
</head>
<body>
<main>
    <a class="brand" href="{{ route('landing') }}">ONEPOS</a>
    <h1>Confirm account deletion request</h1>
    @if ($isValid)
        <p>Confirm that you control the email address connected to this ONEPOS account. The request will then be reviewed by an administrator.</p>
        <form method="post" action="{{ route('account-deletion.verify', ['deletionRequest' => $deletionRequest->id, 'token' => $token]) }}">
            @csrf
            <button type="submit">Confirm request</button>
        </form>
    @else
        <p class="notice">This confirmation link has expired or was already used. Request a new link to continue.</p>
        <p><a href="{{ route('account-deletion.create') }}">Request a new link</a></p>
    @endif
    <p><a href="{{ route('privacy') }}">Privacy policy</a></p>
</main>
</body>
</html>

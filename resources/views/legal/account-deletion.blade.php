<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f8f8">
    <title>Account deletion · ONEPOS</title>
    <style>
        :root{color-scheme:light;--brand:#308e87;--ink:#18303c;--muted:#60727e;--line:#e2ebeb;--surface:#fff}
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;background:#f4f8f8;color:var(--ink);font:16px/1.6 Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
        main{width:min(100%,540px);padding:clamp(22px,5vw,40px);border:1px solid var(--line);border-radius:24px;background:var(--surface);box-shadow:0 18px 55px #173e4712}.brand{color:var(--brand);font-size:14px;font-weight:800;letter-spacing:.12em;text-decoration:none}h1{margin:20px 0 10px;font-size:32px;line-height:1.15;letter-spacing:-.04em}p{color:var(--muted)}label{display:block;margin:22px 0 7px;font-size:14px;font-weight:700}input{width:100%;min-height:50px;padding:12px 14px;border:1px solid #d4e0e3;border-radius:12px;font:inherit}button{width:100%;min-height:50px;margin-top:16px;border:0;border-radius:12px;background:var(--brand);color:white;font:inherit;font-weight:700;cursor:pointer}.notice{margin:18px 0;padding:12px 14px;border-radius:12px;background:#eef9f5;color:#1b604c}.error{margin:8px 0;color:#b42318;font-size:14px}a{color:#207a73}.foot{margin-top:24px;font-size:14px}
    </style>
</head>
<body>
<main>
    <a class="brand" href="{{ route('landing') }}">ONEPOS</a>
    <h1>Request account deletion</h1>
    <p>Enter the email address used for your ONEPOS account. If it matches an eligible account, we will email a confirmation link. A verified request is reviewed by a ONEPOS administrator.</p>
    <p>If approved, personal profile data is removed. Business records required for operational or legal purposes may remain in de-identified form.</p>

    @if (session('status'))<div class="notice" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="notice" role="alert">{{ $errors->first() }}</div>@endif

    <form method="post" action="{{ route('account-deletion.store') }}">
        @csrf
        <label for="email">Account email</label>
        <input id="email" name="email" type="email" autocomplete="email" maxlength="255" required value="{{ old('email') }}">
        <button type="submit">Email me a confirmation link</button>
    </form>

    <p class="foot"><a href="{{ route('privacy') }}">Privacy policy</a> · <a href="{{ route('login') }}">Back to sign in</a></p>
</main>
</body>
</html>

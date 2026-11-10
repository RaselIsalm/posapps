<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f4f8f8">
    <title>Privacy Policy · ONEPOS</title>
    <style>
        :root{color-scheme:light;--brand:#308e87;--ink:#18303c;--muted:#60727e;--line:#e2ebeb;--surface:#fff}
        *{box-sizing:border-box}body{margin:0;background:#f4f8f8;color:var(--ink);font:16px/1.65 Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",sans-serif}
        main{width:min(100% - 32px,850px);margin:36px auto;padding:clamp(22px,5vw,48px);border:1px solid var(--line);border-radius:24px;background:var(--surface);box-shadow:0 18px 55px #173e4712}
        .brand{color:var(--brand);font-size:14px;font-weight:800;letter-spacing:.12em;text-decoration:none}h1{margin:18px 0 4px;font-size:clamp(30px,5vw,42px);line-height:1.15;letter-spacing:-.04em}h2{margin:30px 0 8px;font-size:20px}p,li{color:var(--muted)}.updated{font-size:14px}a{color:#207a73}.links{display:flex;gap:16px;flex-wrap:wrap;margin-top:34px;padding-top:20px;border-top:1px solid var(--line)}
    </style>
</head>
<body>
<main>
    <a class="brand" href="{{ route('landing') }}">ONEPOS</a>
    <h1>Privacy Policy</h1>
    <p class="updated">Last updated: {{ now()->toDateString() }}</p>
    <p>This policy describes how ONETECH handles information when you use the ONEPOS website and Android application. ONEPOS is a business management service used by store owners and staff.</p>

    <h2>Information processed</h2>
    <p>ONEPOS processes account details such as your name, email address, phone number, role and profile image; credentials needed to authenticate you; and the sales, inventory, customer, supplier, purchase, employee and accounting information that your business enters into the service.</p>
    <p>The service and WebView may also process session cookies, IP address, device/browser details, security and activity logs, files you choose to upload, and reports you choose to download or print.</p>

    <h2>How information is used</h2>
    <p>Information is used to sign users in, provide ONEPOS business features, maintain business records, protect accounts, troubleshoot the service, and respond to support or privacy requests. The app opens the ONEPOS website in a WebView and uses its existing account session; it does not ask the Flutter shell to store your password.</p>

    <h2>Service providers and security</h2>
    <p>Information is hosted by the infrastructure provider used for ONEPOS and may be processed by email, SMS, backup or other service providers that the organization configures for the service. ONETECH does not sell account information for advertising. Connections to the production service use HTTPS, and access to business information is controlled by account permissions.</p>

    <h2>Retention and deletion</h2>
    <p>Business transaction records may be retained where needed for accounting, operational or legal purposes. You can submit an account deletion request from your profile or through the <a href="{{ route('account-deletion.create') }}">public account deletion page</a>. After verifying control of the account email, an administrator reviews the request. When approved, personal profile information is removed and historical records that must remain are de-identified. A Super Admin account must be handled by the organization through its separate administrative process.</p>

    <h2>Children and contact</h2>
    <p>ONEPOS is a business service intended for authorized store owners and staff, not children. For privacy questions, contact ONETECH through <a href="https://onetech.com.bd/contact" rel="noopener">onetech.com.bd/contact</a>.</p>

    <div class="links">
        <a href="{{ route('account-deletion.create') }}">Request account deletion</a>
        <a href="{{ route('login') }}">Sign in to ONEPOS</a>
    </div>
</main>
</body>
</html>

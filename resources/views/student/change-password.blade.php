<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Set a new password</title>
    <style>
        body{font-family:system-ui,sans-serif;background:#f5f5f4;color:#1c1917;margin:0;padding:2rem}
        main{max-width:30rem;margin:8vh auto;background:#fff;padding:2rem;border-radius:1rem;box-shadow:0 8px 24px #0000000d}
        label{display:block;font-weight:600;margin:1rem 0 .4rem}input{box-sizing:border-box;width:100%;padding:.75rem;border:1px solid #d6d3d1;border-radius:.5rem}
        button{margin-top:1.4rem;background:#a16207;color:#fff;padding:.8rem 1rem;border:0;border-radius:.5rem;font-weight:700;cursor:pointer}
        .error{color:#b91c1c;margin-top:.5rem;font-size:.9rem}
    </style>
</head>
<body>
<main>
    <h1>Set a new password</h1>
    <p>For account security, replace the temporary password before continuing.</p>
    <form method="post" action="{{ route('student.password.update') }}">
        @csrf
        <label for="current_password">Temporary password</label>
        <input id="current_password" name="current_password" type="password" required autocomplete="current-password">
        @error('current_password')<div class="error">{{ $message }}</div>@enderror
        <label for="password">New password (at least 12 characters)</label>
        <input id="password" name="password" type="password" required minlength="12" autocomplete="new-password">
        @error('password')<div class="error">{{ $message }}</div>@enderror
        <label for="password_confirmation">Confirm new password</label>
        <input id="password_confirmation" name="password_confirmation" type="password" required minlength="12" autocomplete="new-password">
        <button type="submit">Save new password</button>
    </form>
</main>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student sign in</title>
    <style>
        body{font-family:system-ui,sans-serif;background:#f5f5f4;color:#1c1917;margin:0;padding:2rem}
        main{max-width:26rem;margin:8vh auto;background:#fff;padding:2rem;border-radius:1rem;box-shadow:0 8px 24px #0000000d}
        label{display:block;font-weight:600;margin:1rem 0 .4rem}input{box-sizing:border-box;width:100%;padding:.75rem;border:1px solid #d6d3d1;border-radius:.5rem}
        button{margin-top:1.4rem;width:100%;background:#a16207;color:#fff;padding:.8rem;border:0;border-radius:.5rem;font-weight:700;cursor:pointer}
        .error{color:#b91c1c;margin-top:.5rem;font-size:.9rem}a{color:#854d0e}
    </style>
</head>
<body>
<main>
    <h1>Student sign in</h1>
    <p>Use the admission number and temporary password provided by the school.</p>
    <form method="post" action="{{ route('student.authenticate') }}">
        @csrf
        <label for="username">Admission number</label>
        <input id="username" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>
        @error('username')<div class="error">{{ $message }}</div>@enderror
        <label for="password">Password</label>
        <input id="password" name="password" type="password" required autocomplete="current-password">
        @error('password')<div class="error">{{ $message }}</div>@enderror
        <button type="submit">Sign in</button>
    </form>
</main>
</body>
</html>

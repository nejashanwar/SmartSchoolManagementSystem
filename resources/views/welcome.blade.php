<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $schoolName }}</title>
    <style>
        *{box-sizing:border-box}body{margin:0;font-family:system-ui,sans-serif;background:#fafaf9;color:#1c1917}
        header,main,footer{max-width:68rem;margin:auto;padding:1.5rem}header{display:flex;justify-content:space-between;align-items:center}
        .brand{font-weight:750;font-size:1.1rem}.admin{color:#57534e;text-decoration:none}
        main{padding-top:8vh;padding-bottom:12vh}.eyebrow{color:#a16207;font-weight:700;letter-spacing:.12em;text-transform:uppercase;font-size:.8rem}
        h1{font-size:clamp(2.5rem,7vw,5rem);line-height:1.02;max-width:13ch;margin:.8rem 0 1rem}
        p{max-width:42rem;color:#57534e;font-size:1.1rem;line-height:1.7}.button{display:inline-block;margin-top:1.2rem;background:#a16207;color:white;padding:.85rem 1.2rem;border-radius:.6rem;text-decoration:none;font-weight:700}
        footer{border-top:1px solid #d8d0cc;color:#78716c;font-size:.9rem}
    </style>
</head>
<body>
<header>
    <div class="brand">{{ $schoolName }}</div>
    <nav style="display:flex;gap:1rem">
        <a class="admin" href="{{ route('student.login') }}">Student sign in</a>
        <a class="admin" href="{{ route('teacher.login') }}">Teacher sign in</a>
        <a class="admin" href="{{ route('parent.login') }}">Parent sign in</a>
        <a class="admin" href="/admin/login">Staff sign in</a>
    </nav>
</header>
<main>
    <div class="eyebrow">School admissions</div>
    <h1>Learning with purpose.</h1>
    <p>Apply for admission to {{ $schoolName }}. Applications are accepted only during the school’s configured registration period.</p>
    <a class="button" href="{{ route('admissions.create') }}">Apply for admission</a>
</main>
<footer>{{ $schoolName }} · Grades 1–8</footer>
</body>
</html>

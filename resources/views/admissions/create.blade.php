<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student admission application</title>
    <style>
        body{font-family:system-ui,sans-serif;background:#f5f5f4;color:#1c1917;margin:0;padding:2rem}
        main{max-width:42rem;margin:auto;background:#fff;padding:2rem;border-radius:1rem;box-shadow:0 8px 24px #0000000d}
        h1{margin-top:0}.grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        label{display:block;font-weight:600;margin:.75rem 0 .35rem}input,select{box-sizing:border-box;width:100%;padding:.7rem;border:1px solid #d6d3d1;border-radius:.5rem}
        button{margin-top:1.5rem;background:#a16207;color:#fff;padding:.75rem 1.1rem;border:0;border-radius:.5rem;font-weight:700;cursor:pointer}
        .error{color:#b91c1c;font-size:.9rem}.note{color:#57534e}.span-2{grid-column:span 2}
        @media(max-width:600px){body{padding:1rem}.grid{grid-template-columns:1fr}.span-2{grid-column:auto}}
    </style>
</head>
<body>
<main>
    <h1>Student admission application</h1>
    <p class="note">Complete the form during the registration period. Submitting an application does not guarantee admission.</p>

    @if ($errors->any())
        <div class="error">Please correct the highlighted form information and submit again.</div>
    @endif

    <form method="post" action="{{ route('admissions.store') }}">
        @csrf
        <div class="grid">
            <div class="span-2">
                <label for="student_name">Student full name</label>
                <input id="student_name" name="student_name" value="{{ old('student_name') }}" required maxlength="255" autocomplete="name">
                @error('student_name')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="grade_id">Applying grade</label>
                <select id="grade_id" name="grade_id" required>
                    <option value="">Choose a grade</option>
                    @foreach ($grades as $grade)
                        <option value="{{ $grade->id }}" @selected(old('grade_id') == $grade->id)>{{ $grade->name }}</option>
                    @endforeach
                </select>
                @error('grade_id')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="academic_year_id">Academic year</label>
                <select id="academic_year_id" name="academic_year_id" required>
                    <option value="">Choose an academic year</option>
                    @foreach ($academicYears as $academicYear)
                        <option value="{{ $academicYear->id }}" @selected(old('academic_year_id') == $academicYear->id)>{{ $academicYear->name }}</option>
                    @endforeach
                </select>
                @error('academic_year_id')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="span-2">
                <label for="guardian_name">Parent or guardian full name</label>
                <input id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}" required maxlength="255" autocomplete="name">
                @error('guardian_name')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="guardian_phone">Parent or guardian phone</label>
                <input id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}" required maxlength="40" autocomplete="tel">
                @error('guardian_phone')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div>
                <label for="guardian_email">Parent or guardian email (optional)</label>
                <input id="guardian_email" type="email" name="guardian_email" value="{{ old('guardian_email') }}" maxlength="255" autocomplete="email">
                @error('guardian_email')<div class="error">{{ $message }}</div>@enderror
            </div>
            <div class="span-2">
                <label for="previous_school">Previous school (optional)</label>
                <input id="previous_school" name="previous_school" value="{{ old('previous_school') }}" maxlength="255">
                @error('previous_school')<div class="error">{{ $message }}</div>@enderror
            </div>
        </div>
        <button type="submit">Submit application</button>
    </form>
</main>
</body>
</html>

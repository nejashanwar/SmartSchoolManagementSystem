<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student dashboard</title>
    <style>
        *{box-sizing:border-box}body{font-family:system-ui,sans-serif;background:#f5f5f4;color:#1c1917;margin:0;padding:1.5rem}
        main{max-width:58rem;margin:2rem auto}.top{display:flex;justify-content:space-between;align-items:center;gap:1rem}
        .card{background:#fff;border-radius:.9rem;padding:1.5rem;margin-top:1rem;box-shadow:0 8px 24px #0000000d}
        dl{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}dt{color:#78716c;font-size:.9rem}dd{margin:.25rem 0 0;font-weight:650}
        button{background:#a16207;color:white;border:0;border-radius:.5rem;padding:.65rem 1rem;font-weight:650;cursor:pointer}
        .status{color:#166534}.notice{color:#57534e}@media(max-width:600px){dl{grid-template-columns:1fr}.top{align-items:flex-start}}
    </style>
</head>
<body>
<main>
    <div class="top">
        <div><p>Madrasaa Ifaa Islaamaa</p><h1>Welcome, {{ $student->full_name }}</h1></div>
        <form method="post" action="{{ route('student.logout') }}">@csrf<button type="submit">Sign out</button></form>
    </div>
    @if (session('status'))<p class="status">{{ session('status') }}</p>@endif
    <section class="card" aria-labelledby="profile-heading">
        <h2 id="profile-heading">Student profile</h2>
        <dl>
            <div><dt>Admission number</dt><dd>{{ $student->admission_number }}</dd></div>
            <div><dt>Grade</dt><dd>{{ $student->grade->name }}</dd></div>
            <div><dt>Academic year</dt><dd>{{ $student->academicYear->name }}</dd></div>
            <div><dt>Section</dt><dd>{{ $student->section?->name ?? 'Not assigned yet' }}</dd></div>
            <div><dt>Parent/guardian</dt><dd>{{ $student->application->guardian_name }}</dd></div>
            <div><dt>Guardian phone</dt><dd>{{ $student->application->guardian_phone }}</dd></div>
            <div><dt>Application status</dt><dd>{{ ucfirst($student->application->status) }}</dd></div>
        </dl>
    </section>
    <section class="card">
        <h2>Attendance summary</h2>
        <p class="notice">This month: {{ $monthlyAttendance['attendance_percentage'] === null ? '—' : $monthlyAttendance['attendance_percentage'].'%' }} attendance across {{ $monthlyAttendance['session_count'] }} completed sessions. <a href="{{ route('student.attendance') }}">View monthly report</a></p>
        @if ($attendanceSummary->isEmpty())
            <p class="notice">Attendance has not been recorded yet.</p>
        @else
            <dl>@foreach (['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'] as $key => $label)<div><dt>{{ $label }}</dt><dd>{{ $attendanceSummary[$key] ?? 0 }}</dd></div>@endforeach</dl>
        @endif
    </section>
    <section class="card">
        <h2>Examination schedule</h2>
        @if ($exams->isEmpty())
            <p class="notice">No examinations are scheduled for your section yet.</p>
        @else
            <div style="overflow-x:auto"><table style="width:100%;text-align:left"><thead><tr><th>Exam</th><th>Term</th><th>Date</th><th>Status</th><th>Subjects</th></tr></thead><tbody>
                @foreach ($exams as $exam)<tr><td>{{ $exam->name }}</td><td>{{ $exam->term?->name ?? '—' }}</td><td>{{ $exam->exam_date?->format('M j, Y') ?? '—' }}</td><td>{{ ucfirst(str_replace('_', ' ', $exam->status)) }}</td><td>{{ $exam->subjects->pluck('subject.name')->join(', ') ?: '—' }}</td></tr>@endforeach
            </tbody></table></div>
        @endif
    </section>
    <section class="card">
        <h2>Published results</h2>
        @if ($results->isEmpty())
            <p class="notice">No results have been published yet.</p>
        @else
            <div style="overflow-x:auto"><table style="width:100%;text-align:left"><thead><tr><th>Examination</th><th>Term</th><th>Total</th><th>Average</th><th>Grade</th><th>Report card</th></tr></thead><tbody>
                @foreach ($results as $result)<tr><td>{{ $result->exam->name }}</td><td>{{ $result->exam->term?->name ?? '—' }}</td><td>{{ $result->total_marks }}/{{ $result->maximum_marks }}</td><td>{{ $result->average_percentage }}%</td><td>{{ $result->grade_label }}</td><td><a href="{{ route('student.results.show',$result) }}">View / print</a></td></tr>@endforeach
            </tbody></table></div>
        @endif
    </section>
</main>
</body>
</html>

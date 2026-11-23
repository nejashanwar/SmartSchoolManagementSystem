<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student portal · {{ $student->full_name }}</title>
    <style>
        *{box-sizing:border-box}html{scroll-behavior:auto}body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;background:#f4f6fa;color:#172033;margin:0}
        .topbar{position:sticky;top:0;z-index:30;min-height:70px;display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.75rem 1.5rem;background:#fff;border-bottom:1px solid #e5eaf1;box-shadow:0 2px 10px #1720330b}.brand{display:flex;align-items:center;gap:.75rem}.brand-mark{display:grid;place-items:center;width:40px;height:40px;border-radius:12px;background:#e7efff;color:#244b93;font-weight:850}.brand p{margin:0;color:#728097;font-size:.76rem}.brand h1{margin:.1rem 0 0;font-size:1rem;color:#18243a}.top-actions{display:flex;align-items:center;gap:1rem}.student-name{color:#4b5870;font-size:.9rem;font-weight:600}button{background:#244b93;color:#fff;border:0;border-radius:.55rem;padding:.62rem .95rem;font-weight:700;cursor:pointer}button:hover{background:#193b78}
        .portal-layout{display:grid;grid-template-columns:258px minmax(0,1fr);min-height:calc(100vh - 70px)}.sidebar{position:sticky;top:70px;align-self:start;height:calc(100vh - 70px);overflow-y:auto;padding:1rem .85rem;background:#fff;border-right:1px solid #e5eaf1}.nav-label{margin:1rem .65rem .4rem;color:#8a96a9;font-size:.68rem;font-weight:800;letter-spacing:.09em;text-transform:uppercase}.nav-label:first-child{margin-top:.25rem}.nav-link{display:flex;align-items:center;gap:.65rem;margin:.15rem 0;padding:.65rem .72rem;border-radius:.6rem;color:#4b5870;text-decoration:none;font-size:.9rem}.nav-link:hover,.nav-link:focus,.nav-link[aria-current="page"]{background:#eaf0fb;color:#244b93}.nav-link[aria-current="page"]{font-weight:750}.nav-icon{width:1.35rem;text-align:center;color:#7b8ba7;font-weight:700}.nav-link small{display:block;margin-top:.14rem;color:#8995a8;font-size:.73rem}.sidebar-empty{padding:.35rem .7rem;color:#9aa4b4;font-size:.8rem}
        .content{min-width:0;max-width:none;padding:1.5rem}.welcome{display:flex;justify-content:space-between;align-items:end;gap:1rem;margin:0 0 1rem}.welcome h2{margin:0;font-size:1.45rem;color:#18243a}.welcome p{margin:.3rem 0 0;color:#728097;font-size:.9rem}.card{margin-top:1rem;padding:1.35rem 1.5rem;border:1px solid #e6eaf0;border-radius:14px;background:#fff;box-shadow:0 5px 18px #17203308}.card h2{margin:0 0 .9rem;font-size:1.05rem;color:#1d2b43}.profile-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}.profile-grid dt{color:#7b8799;font-size:.78rem}.profile-grid dd{margin:.25rem 0 0;color:#25334a;font-weight:650}.notice{color:#63718a}.status{margin:0 0 1rem;padding:.8rem 1rem;border:1px solid #bbebce;border-radius:9px;background:#edfff3;color:#167344}.metric{color:#244b93;font-weight:750}table{width:100%;border-collapse:collapse;text-align:left;font-size:.88rem}th{padding:.75rem .7rem;background:#f6f8fb;color:#59677d;font-size:.75rem;font-weight:750;white-space:nowrap}td{padding:.75rem .7rem;border-top:1px solid #edf0f4;color:#344258;vertical-align:middle}a{color:#244b93}
        @media(max-width:900px){.portal-layout{grid-template-columns:1fr}.sidebar{position:sticky;top:70px;z-index:20;width:100%;height:auto;max-height:110px;display:flex;align-items:center;gap:.25rem;overflow-x:auto;overflow-y:hidden;padding:.45rem .75rem;border-right:0;border-bottom:1px solid #e5eaf1}.nav-label{display:none}.nav-link{flex:0 0 auto;margin:0;padding:.58rem .7rem;white-space:nowrap}.nav-icon{width:auto}.nav-link small{display:none}.sidebar-empty{display:none}.content{padding:1rem}}
        @media(max-width:620px){.topbar{min-height:62px;padding:.65rem .8rem}.brand-mark{width:36px;height:36px}.brand p{font-size:.68rem}.brand h1{font-size:.9rem}.student-name{display:none}.portal-layout{min-height:calc(100vh - 62px)}.sidebar{top:62px}.content{padding:.75rem}.welcome{align-items:flex-start}.welcome h2{font-size:1.2rem}.card{padding:1rem;margin-top:.75rem}.profile-grid{grid-template-columns:repeat(2,minmax(0,1fr))}table{min-width:660px}}
    </style>
</head>
<body>
<header class="topbar">
    <div class="brand"><span class="brand-mark">M</span><div><p>Madrasaa Ifaa Islaamaa</p><h1>Student portal</h1></div></div>
    <div class="top-actions"><span class="student-name">{{ $student->full_name }}</span><form method="post" action="{{ route('student.logout') }}">@csrf<button type="submit">Sign out</button></form></div>
</header>

<div class="portal-layout">
    <aside class="sidebar" aria-label="Student navigation">
        <div class="nav-label">Student menu</div>
        <a class="nav-link" href="#student-overview" data-page-link="student-overview"><span class="nav-icon">⌂</span>Overview</a>
        <a class="nav-link" href="#student-subjects" data-page-link="student-subjects"><span class="nav-icon">▦</span>My subjects</a>
        <a class="nav-link" href="#student-attendance" data-page-link="student-attendance"><span class="nav-icon">✓</span>Attendance</a>
        <a class="nav-link" href="{{ route('student.attendance') }}"><span class="nav-icon">▤</span>Monthly attendance report</a>
        <a class="nav-link" href="#student-exams" data-page-link="student-exams"><span class="nav-icon">▣</span>Examination schedule</a>
        <a class="nav-link" href="#student-results" data-page-link="student-results"><span class="nav-icon">▥</span>Published results</a>

        @if ($results->isNotEmpty())
            <div class="nav-label">Report cards</div>
            @foreach ($results as $result)
                <a class="nav-link" href="{{ route('student.results.show', $result) }}">
                    <span class="nav-icon">↗</span>
                    <span>{{ $result->exam->name }}<small>{{ $result->published_at?->format('M j, Y') }}</small></span>
                </a>
            @endforeach
        @endif
    </aside>

    <main class="content">
        @if (session('status'))<p class="status" data-page="student-overview" role="status">{{ session('status') }}</p>@endif

        <div class="welcome" data-page="student-overview">
            <div><h2>Welcome, {{ $student->full_name }}</h2><p>Your academic information and school updates.</p></div>
        </div>

        <section class="card" id="student-overview" data-page="student-overview" aria-labelledby="profile-heading">
            <h2 id="profile-heading">Student profile</h2>
            <dl class="profile-grid">
                <div><dt>Admission number</dt><dd>{{ $student->admission_number }}</dd></div>
                <div><dt>Grade</dt><dd>{{ $student->grade->name }}</dd></div>
                <div><dt>Academic year</dt><dd>{{ $student->academicYear->name }}</dd></div>
                <div><dt>Section</dt><dd>{{ $student->section?->name ?? 'Not assigned yet' }}</dd></div>
                <div><dt>Parent/guardian</dt><dd>{{ $student->application->guardian_name }}</dd></div>
                <div><dt>Guardian phone</dt><dd>{{ $student->application->guardian_phone }}</dd></div>
                <div><dt>Application status</dt><dd>{{ ucfirst($student->application->status) }}</dd></div>
            </dl>
        </section>

        <section class="card" id="student-subjects" data-page="student-subjects" aria-labelledby="subjects-heading" hidden>
            <h2 id="subjects-heading">My subjects and teachers</h2>
            <dl class="profile-grid" style="margin:0 0 1.25rem;padding:1rem;background:#f6f8fb;border-radius:10px">
                <div><dt>Student</dt><dd>{{ $student->full_name }}</dd></div>
                <div><dt>Admission ID</dt><dd>{{ $student->admission_number }}</dd></div>
                <div><dt>Academic year</dt><dd>{{ $student->academicYear?->name ?? '—' }}</dd></div>
                <div><dt>Grade</dt><dd>{{ $student->grade?->name ?? '—' }}</dd></div>
                <div><dt>Section</dt><dd>{{ $student->section?->name ?? 'Not assigned' }}</dd></div>
            </dl>
            <p class="notice">Subjects assigned to your section and the teachers responsible for them.</p>
            @if ($subjectAssignments->isEmpty())
                <p class="notice">No subjects have been assigned to your section yet. Please contact the school administrator.</p>
            @else
                <div style="overflow-x:auto"><table><thead><tr><th>Subject</th><th>Subject code</th><th>Teacher</th></tr></thead><tbody>
                    @foreach ($subjectAssignments as $assignment)
                        <tr>
                            <td>{{ $assignment->subject?->name ?? '—' }}</td>
                            <td>{{ $assignment->subject?->code ?? '—' }}</td>
                            <td>{{ $assignment->teacherProfile?->user?->name ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody></table></div>
            @endif
        </section>

        <section class="card" id="student-attendance" data-page="student-attendance" hidden>
            <h2>Attendance summary</h2>
            <p class="notice">This month: <span class="metric">{{ $monthlyAttendance['attendance_percentage'] === null ? '—' : $monthlyAttendance['attendance_percentage'].'%' }}</span> attendance across {{ $monthlyAttendance['session_count'] }} completed sessions. <a href="{{ route('student.attendance') }}">View monthly report</a></p>
            @if ($attendanceSummary->isEmpty())
                <p class="notice">Attendance has not been recorded yet.</p>
            @else
                <dl class="profile-grid">@foreach (['present' => 'Present', 'absent' => 'Absent', 'late' => 'Late', 'excused' => 'Excused'] as $key => $label)<div><dt>{{ $label }}</dt><dd>{{ $attendanceSummary[$key] ?? 0 }}</dd></div>@endforeach</dl>
            @endif
        </section>

        <section class="card" id="student-exams" data-page="student-exams" hidden>
            <h2>Examination schedule</h2>
            @if ($exams->isEmpty())
                <p class="notice">No examinations are scheduled for your section yet.</p>
            @else
                <div style="overflow-x:auto"><table><thead><tr><th>Exam</th><th>Term</th><th>Date</th><th>Status</th><th>Subjects</th></tr></thead><tbody>
                    @foreach ($exams as $exam)<tr><td>{{ $exam->name }}</td><td>{{ $exam->term?->name ?? '—' }}</td><td>{{ $exam->exam_date?->format('M j, Y') ?? '—' }}</td><td>{{ ucfirst(str_replace('_', ' ', $exam->status)) }}</td><td>{{ $exam->subjects->pluck('subject.name')->join(', ') ?: '—' }}</td></tr>@endforeach
                </tbody></table></div>
            @endif
        </section>

        <section class="card" id="student-results" data-page="student-results" hidden>
            <h2>Published results</h2>
            @if ($results->isEmpty())
                <p class="notice">No results have been published yet.</p>
            @else
                <div style="overflow-x:auto"><table><thead><tr><th>Examination</th><th>Term</th><th>Total</th><th>Average</th><th>Section rank</th><th>Grade</th><th>Report card</th></tr></thead><tbody>
                    @foreach ($results as $result)<tr><td>{{ $result->exam->name }}</td><td>{{ $result->exam->term?->name ?? '—' }}</td><td>{{ $result->total_marks }}/{{ $result->maximum_marks }}</td><td>{{ $result->average_percentage }}%</td><td>{{ $result->section_rank ?? '—' }}</td><td>{{ $result->grade_label }}</td><td><a href="{{ route('student.results.show',$result) }}">View / print</a></td></tr>@endforeach
                </tbody></table></div>
            @endif
        </section>
    </main>
</div>
<script>
    (() => {
        const pages = [...document.querySelectorAll('[data-page]')];
        const links = [...document.querySelectorAll('[data-page-link]')];

        function showPage(pageId) {
            const selectedId = pages.some((page) => page.dataset.page === pageId) ? pageId : 'student-overview';
            pages.forEach((page) => { page.hidden = page.dataset.page !== selectedId; });
            links.forEach((link) => {
                if (link.dataset.pageLink === selectedId) link.setAttribute('aria-current', 'page');
                else link.removeAttribute('aria-current');
            });
        }

        links.forEach((link) => link.addEventListener('click', (event) => {
            event.preventDefault();
            const pageId = link.dataset.pageLink;
            window.history.pushState({ studentPage: pageId }, '', `#${pageId}`);
            showPage(pageId);
        }));

        window.addEventListener('popstate', () => showPage(window.location.hash.slice(1)));
        showPage(window.location.hash.slice(1));
    })();
</script>
</body>
</html>

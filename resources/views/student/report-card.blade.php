<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Report card · {{ $card['student']->full_name }}</title>
    <style>
    :root{
        --text:#1c1917;
        --muted:#57534e;
        --border:#d6d3d1;
        --primary:#166534;
        --primary-soft:#f0fdf4;
        --row-alt:#fafaf9;
    }
    *{box-sizing:border-box}
    body{
        font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
        font-size:16px;
        line-height:1.5;
        color:var(--text);
        background:#fff;
        margin:2rem;
    }
    main{max-width:70rem;margin:auto}

    /* Actions */
    .actions{display:flex;justify-content:space-between;align-items:center;margin-bottom:1.25rem}
    .actions a{color:var(--primary);font-weight:600;text-decoration:none}
    .actions a:hover{text-decoration:underline}
    .actions button{
        background:var(--primary);color:#fff;border:0;
        padding:.55rem 1.1rem;border-radius:.5rem;
        font-size:.9rem;font-weight:600;cursor:pointer;
    }
    .actions button:hover{background:#14532d}

    /* Headings */
    h1{font-size:1.75rem;font-weight:800;color:var(--primary);margin:0;text-align:center}
    h2{font-size:1.1rem;font-weight:600;color:var(--muted);margin:.25rem 0 1rem;text-align:center}
    .student-meta{
        color:var(--muted);font-size:.95rem;
        text-align:center;margin:0 0 1rem;
        padding-bottom:1rem;border-bottom:2px solid var(--primary);
    }
    .student-meta strong{color:var(--text);font-weight:700}

    /* Summary cards */
    .summary{
        display:grid;
        grid-template-columns:repeat(4,minmax(120px,1fr));
        gap:.75rem;margin:1.5rem 0;
    }
    .summary div{
        border:1px solid var(--border);
        background:var(--primary-soft);
        padding:.75rem .9rem;border-radius:.6rem;
        font-size:.8rem;font-weight:500;color:var(--muted);
        text-transform:uppercase;letter-spacing:.03em;
    }
    .summary strong{
        display:block;margin-top:.35rem;
        font-size:1.2rem;font-weight:700;
        color:var(--text);text-transform:none;letter-spacing:0;
    }

    /* Table */
    table{
        width:100%;border-collapse:separate;border-spacing:0;
        text-align:left;font-size:.95rem;
        border:1px solid var(--border);border-radius:.6rem;overflow:hidden;
    }
    th,td{padding:.7rem 1rem;border-bottom:1px solid var(--border)}
    thead th{
        background:var(--primary);color:#fff;
        font-size:.85rem;font-weight:700;
        text-transform:uppercase;letter-spacing:.04em;
    }
    th:not(:first-child),td:not(:first-child){text-align:center}
    tbody td:first-child{font-weight:600}
    tbody tr:nth-child(even){background:var(--row-alt)}
    tbody tr:hover{background:var(--primary-soft)}
    tbody tr:last-child td{border-bottom:0}
    td:last-child{font-weight:700}
    .subject-row{background:#f5f5f4;font-weight:700}

    /* Notice */
    .notice{
        padding:.8rem 1rem;background:#fffbeb;
        border:1px solid #facc15;border-left:4px solid #eab308;
        border-radius:.5rem;color:#854d0e;font-size:.9rem;
    }
    main > p:last-child{color:var(--muted);font-size:.85rem;margin-top:1rem;text-align:right}

    /* Responsive */
    @media(max-width:900px){
        .summary{grid-template-columns:repeat(2,minmax(120px,1fr))}
    }
    @media(max-width:600px){
        body{margin:1rem}
        h1{font-size:1.4rem}
        .summary{grid-template-columns:repeat(2,minmax(100px,1fr))}
        table{min-width:600px}
        .table-wrap{overflow-x:auto}
    }

    /* Print */
    @media print{
        .actions,.notice{display:none}
        body{margin:0;font-size:13px}
        main{max-width:none}
        thead th{background:#e7e5e4 !important;color:#000 !important;
            -webkit-print-color-adjust:exact;print-color-adjust:exact}
        .summary div{background:#fff;border-color:#a8a29e}
        tbody tr:hover{background:transparent}
    }
</style>
</head>
<body>
<main>
    <div class="actions"><a href="{{ auth()->user()->hasRole('PARENT') ? route('parent.children.show',$card['student']) : route('student.dashboard') }}">← Back</a><button onclick="window.print()">Print report card</button></div>
    <h1>Madrasaa Ifaa Islaamaa</h1>
    <h2>Student report card · {{ $card['exam']->academicYear->name }}</h2>
    <p class="student-meta"><strong>{{ $card['student']->full_name }}</strong> · {{ $card['student']->admission_number }} · {{ $card['exam']->section->grade->name }} {{ $card['exam']->section->name }}</p>

    @if (! $card['isComplete'] && $card['secondSemesterResult'])
        <p class="notice">The cumulative report card will be complete after Semester 1 results have also been approved and published.</p>
    @endif

   

    <div class="table-wrap">
        <table>
            <thead><tr><th>Subject</th><th>{{ $card['firstSemesterLabel'] }}</th><th>{{ $card['secondSemesterLabel'] }}</th><th>Cumulative average</th></tr></thead>
            <tbody>
            @forelse ($card['subjects'] as $subject)
                <tr>
                    <td>{{ $subject['name'] }}</td>
                    <td>{{ $subject['semester1'] !== null ? number_format($subject['semester1'], 2) : '—' }}</td>
                    <td>{{ $subject['semester2'] !== null ? number_format($subject['semester2'], 2) : '—' }}</td>
                    <td>{{ $subject['cumulative'] !== null ? number_format($subject['cumulative'], 2) : '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No subject marks are available.</td></tr>
            @endforelse
            </tbody>
        </table>
         <div class="summary">
        <div>{{ $card['firstSemesterLabel'] }} total<strong>{{ $card['firstSemesterResult'] ? number_format((float) $card['firstSemesterResult']->total_marks, 2).' / '.number_format((float) $card['firstSemesterResult']->maximum_marks, 2) : '—' }}</strong></div>
        <div>{{ $card['firstSemesterLabel'] }} average<strong>{{ $card['firstSemesterResult'] ? number_format((float) $card['firstSemesterResult']->average_percentage, 2).'%' : '—' }}</strong></div>
        <div>{{ $card['firstSemesterLabel'] }} rank<strong>{{ $card['firstSemesterResult']?->section_rank ? '#'.$card['firstSemesterResult']->section_rank : '—' }}</strong></div>
        <div>{{ $card['secondSemesterLabel'] }} total<strong>{{ $card['secondSemesterResult'] ? number_format((float) $card['secondSemesterResult']->total_marks, 2).' / '.number_format((float) $card['secondSemesterResult']->maximum_marks, 2) : '—' }}</strong></div>
        <div>{{ $card['secondSemesterLabel'] }} average<strong>{{ $card['secondSemesterResult'] ? number_format((float) $card['secondSemesterResult']->average_percentage, 2).'%' : '—' }}</strong></div>
        <div>{{ $card['secondSemesterLabel'] }} rank<strong>{{ $card['secondSemesterResult']?->section_rank ? '#'.$card['secondSemesterResult']->section_rank : '—' }}</strong></div>
        <div>Cumulative average<strong>{{ $card['isComplete'] ? number_format((float) $card['cumulativeAverage'], 2).'%' : '—' }}</strong></div>
        <div>Cumulative rank<strong>{{ $card['isComplete'] && $card['cumulativeRank'] ? '#'.$card['cumulativeRank'] : '—' }}</strong></div>
    </div>
    </div>
    <p>Published {{ $card['publishedAt']?->format('M j, Y') ?? '—' }}</p>
</main>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Attendance reports</title>
    <style>
        *{box-sizing:border-box}body{font-family:system-ui,sans-serif;background:#f5f5f4;color:#1c1917;margin:0;padding:1.5rem}
        main{max-width:72rem;margin:1rem auto}.card{background:white;border-radius:1rem;padding:1.25rem;margin:1rem 0;box-shadow:0 8px 24px #0000000d}
        .toolbar,form{display:flex;gap:.75rem;align-items:end;flex-wrap:wrap}.toolbar{justify-content:space-between}.filters label{display:block;font-size:.85rem;font-weight:650}.filters input,.filters select{padding:.6rem;border:1px solid #d6d3d1;border-radius:.45rem}
        a.button,button{display:inline-block;padding:.65rem .9rem;border:0;border-radius:.5rem;background:#a16207;color:#fff;text-decoration:none;font-weight:650;cursor:pointer}.exports a{margin:.2rem}
        .stats{display:flex;gap:1rem;flex-wrap:wrap;color:#57534e}.stats strong{color:#1c1917}table{width:100%;border-collapse:collapse;text-align:left}th,td{padding:.6rem;border-bottom:1px solid #e7e5e4}th{font-size:.85rem}.muted{color:#78716c}.status{display:inline-block;padding:.2rem .55rem;border-radius:99px;background:#f5f5f4}
        .print-only{display:none}
        @media(max-width:650px){body{padding:.75rem}.card{padding:.85rem}table{font-size:.86rem}}
        @media print{body{background:white;padding:0}main{max-width:none;margin:0}.no-print{display:none!important}.print-only{display:block}.card{box-shadow:none;border:1px solid #ddd;break-inside:avoid}table{font-size:9pt}h1{font-size:20pt}}
    </style>
</head>
<body>
<main>
    <div class="toolbar no-print">
        <div><a href="{{ $isTeacher ? route('teacher.dashboard') : '/admin' }}">← Dashboard</a><h1>Attendance reports</h1></div>
        <div class="exports">
            @php($downloadFilters = ['from' => $from, 'until' => $until, 'section_id' => $selectedSection])
            <a class="button" href="{{ route('attendance.reports.download', ['format' => 'pdf'] + $downloadFilters) }}">Download PDF</a>
            <a class="button" href="{{ route('attendance.reports.download', ['format' => 'xlsx'] + $downloadFilters) }}">Download Excel</a>
            <a class="button" href="{{ route('attendance.reports.download', ['format' => 'csv'] + $downloadFilters) }}">Download CSV</a>
        </div>
    </div>
    <section class="card filters no-print">
        <form method="get" action="{{ route('attendance.reports.show') }}">
            <label>From<input type="date" name="from" value="{{ $from }}" required></label>
            <label>Until<input type="date" name="until" value="{{ $until }}" required></label>
            <label>Section<select name="section_id"><option value="">All assigned sections</option>@foreach($sections as $section)<option value="{{ $section->id }}" @selected((string)$selectedSection === (string)$section->id)>{{ $section->grade->name }} · {{ $section->name }}</option>@endforeach</select></label>
            <button type="submit">Show report</button><button type="button" onclick="window.print()">Print</button>
        </form>
    </section>
    <h1 class="print-only">Madrasaa Ifaa Islaamaa · Attendance report</h1>
    <p class="muted">Period: {{ $from }} to {{ $until }} · Sessions: {{ $reports->count() }}</p>
    @forelse($reports as $report)
        @php($session = $report['session'])
        <section class="card">
            <h2>{{ $session->session_date->format('M j, Y') }} · {{ $session->name }} · {{ $session->section->grade->name }} {{ $session->section->name }}</h2>
            <div class="stats">
                <span>Teacher: <strong>{{ $session->teacherProfile?->user?->name ?? 'Unassigned' }}</strong></span>
                <span>Session: <strong>{{ ucfirst($session->status) }}</strong></span>
                <span>Completion: <strong>{{ $report['completion'] }}%</strong></span>
                <span>Marked: <strong>{{ $report['marked'] }}/{{ $report['total'] }}</strong></span>
                <span>Present: <strong>{{ $report['counts']['present'] }}</strong></span>
                <span>Absent: <strong>{{ $report['counts']['absent'] }}</strong></span>
                <span>Late: <strong>{{ $report['counts']['late'] }}</strong></span>
                <span>Excused: <strong>{{ $report['counts']['excused'] }}</strong></span>
                <span>Not recorded: <strong>{{ $report['counts']['not_recorded'] }}</strong></span>
            </div>
            <div style="overflow-x:auto;margin-top:1rem"><table>
                <thead><tr><th>Student</th><th>Admission number</th><th>Attendance</th><th>Recorded by</th></tr></thead>
                <tbody>@forelse($report['rows'] as $row)<tr><td>{{ $row['student']->full_name }}</td><td>{{ $row['student']->admission_number }}</td><td><span class="status">{{ ucfirst(str_replace('_',' ',$row['status'])) }}</span></td><td>{{ $row['recorded_by'] ?? '—' }}</td></tr>@empty<tr><td colspan="4">No students are assigned to this section.</td></tr>@endforelse</tbody>
            </table></div>
        </section>
    @empty
        <section class="card"><p>No attendance sessions were found for this date range and section.</p></section>
    @endforelse
</main>
</body>
</html>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>{{ $student->full_name }} · Parent portal</title>
    <style>
        body{font-family:system-ui,sans-serif;background:#f5f5f4;color:#1c1917;margin:0;padding:1.5rem}main{max-width:60rem;margin:2rem auto}.card{background:white;border-radius:1rem;padding:1.5rem;margin-top:1rem;box-shadow:0 8px 24px #0001}a{color:#92400e}table{width:100%;text-align:left;border-collapse:collapse}td,th{padding:.65rem;border-bottom:1px solid #e7e5e4}form{display:flex;gap:.6rem;align-items:center;margin:1rem 0}button{padding:.55rem .8rem}@media print{body{background:white;padding:0}.print-hide{display:none}}
    </style>
</head>
<body>
<main>
    <a class="print-hide" href="{{ route('parent.dashboard') }}">← Parent dashboard</a>
    <h1>{{ $student->full_name }}</h1>
    <p>{{ $student->grade->name }} · {{ $student->section?->name ?? 'No section' }} · {{ $student->academicYear->name }}</p>
    <section class="card">
        <h2>Monthly attendance</h2>
        <form class="print-hide" method="get">
            <label for="month">Month</label>
            <input id="month" type="month" name="month" value="{{ $report['month'] }}">
            <button>View</button>
            <button type="button" onclick="window.print()">Print attendance report</button>
        </form>
        <p>Attendance: <strong>{{ $report['attendance_percentage'] === null ? '—' : $report['attendance_percentage'].'%' }}</strong> (present or late out of {{ $report['session_count'] }} completed sessions)</p>
        <table>
            <thead><tr><th>Date</th><th>Teacher</th><th>Session</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($report['sessions'] as $session)
                <tr>
                    <td>{{ $session->session_date->format('M j, Y') }}</td>
                    <td>{{ $session->teacherProfile?->user?->name ?? 'Unassigned' }}</td>
                    <td>{{ $session->name }}</td>
                    <td>{{ ucfirst(str_replace('_',' ',$session->records->first()?->status ?? 'not recorded')) }}</td>
                </tr>
            @empty
                <tr><td colspan="4">No completed sessions for this month.</td></tr>
            @endforelse
            </tbody>
        </table>
    </section>
    <section class="card">
        <h2>Published results</h2>
        @if($results->isEmpty())
            <p>No results have been published yet.</p>
        @else
            <table>
                <thead><tr><th>Examination</th><th>Term</th><th>Average</th><th>Grade</th><th>Report card</th></tr></thead>
                <tbody>
                @foreach($results as $result)
                    <tr>
                        <td>{{ $result->exam->name }}</td>
                        <td>{{ $result->exam->term?->name ?? '—' }}</td>
                        <td>{{ $result->average_percentage }}%</td>
                        <td>{{ $result->grade_label }}</td>
                        <td><a href="{{ route('parent.results.show',[$student,$result]) }}">Print</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </section>
</main>
</body>
</html>

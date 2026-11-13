<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Attendance report</title>
    <style>
        body{font-family:system-ui,sans-serif;background:#f5f5f4;color:#1c1917;margin:0;padding:1.5rem}main{max-width:56rem;margin:2rem auto}.card{background:white;border-radius:1rem;padding:1.5rem;margin-top:1rem}table{width:100%;text-align:left;border-collapse:collapse}td,th{padding:.65rem;border-bottom:1px solid #e7e5e4}form{display:flex;gap:.5rem;align-items:center;margin:1rem 0}@media print{body{background:white;padding:0}.print-hide{display:none}}
    </style>
</head>
<body>
<main>
    <a class="print-hide" href="{{ route('student.dashboard') }}">← Student dashboard</a>
    <h1>Attendance · {{ $student->full_name }}</h1>
    <section class="card">
        <form class="print-hide" method="get">
            <label for="month">Month</label>
            <input id="month" type="month" name="month" value="{{ $report['month'] }}">
            <button>View</button>
            <button type="button" onclick="window.print()">Print</button>
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
</main>
</body>
</html>

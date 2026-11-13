<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Report card · {{ $result->student->full_name }}</title>
    <style>
        body{font-family:system-ui,sans-serif;color:#1c1917;margin:2rem}main{max-width:58rem;margin:auto}.summary{display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;margin:1.5rem 0}.summary div{border:1px solid #d6d3d1;padding:1rem;border-radius:.5rem}table{width:100%;border-collapse:collapse;text-align:left}td,th{border-bottom:1px solid #d6d3d1;padding:.7rem}.actions{display:flex;justify-content:space-between;margin-bottom:1rem}.subject-row{background:#f5f5f4;font-weight:700}@media print{.actions{display:none}body{margin:0}}
    </style>
</head>
<body>
<main>
    <div class="actions"><a href="{{ auth()->user()->hasRole('PARENT') ? route('parent.children.show',$result->student) : route('student.dashboard') }}">← Back</a><button onclick="window.print()">Print report card</button></div>
    <h1>Madrasaa Ifaa Islaamaa</h1>
    <h2>Student report card</h2>
    <p><strong>{{ $result->student->full_name }}</strong> · {{ $result->student->admission_number }} · {{ $result->exam->section->grade->name }} {{ $result->exam->section->name }}</p>
    <p>{{ $result->exam->examType?->name ?? 'Examination' }}: {{ $result->exam->name }} · {{ $result->exam->term?->name ?? $result->exam->academicYear->name }}</p>
    <div class="summary">
        <div>Total<br><strong>{{ $result->total_marks }} / {{ $result->maximum_marks }}</strong></div>
        <div>Average<br><strong>{{ $result->average_percentage }}%</strong></div>
        <div>Grade<br><strong>{{ $result->grade_label }}</strong></div>
    </div>
    <table>
        <thead><tr><th>Subject / assessment</th><th>Mark</th><th>Maximum</th><th>Weight / contribution</th></tr></thead>
        <tbody>
        @foreach($result->exam->subjects as $examSubject)
            @php
                $subjectPercentage = 0;
                foreach ($examSubject->components as $component) {
                    $componentMark = $component->marks->first();
                    if ($componentMark && (float) $component->max_marks > 0) {
                        $subjectPercentage += ((float) $componentMark->marks_obtained / (float) $component->max_marks) * (float) $component->weight;
                    }
                }
            @endphp
            <tr class="subject-row"><td colspan="3">{{ $examSubject->subject->name }}</td><td>{{ number_format($subjectPercentage, 2) }} / 100%</td></tr>
            @foreach($examSubject->components as $component)
                @php
                    $componentMark = $component->marks->first();
                @endphp
                <tr>
                    <td>↳ {{ $component->name }}</td>
                    <td>{{ $componentMark?->marks_obtained ?? '—' }}</td>
                    <td>{{ $component->max_marks }}</td>
                    <td>{{ $component->weight }}% · {{ $componentMark ? number_format(((float) $componentMark->marks_obtained / (float) $component->max_marks) * (float) $component->weight, 2).'%' : '—' }}</td>
                </tr>
            @endforeach
        @endforeach
        </tbody>
    </table>
    <p>Published {{ $result->published_at?->format('M j, Y') }}</p>
</main>
</body>
</html>

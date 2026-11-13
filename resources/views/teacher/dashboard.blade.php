<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher dashboard</title>
    <style>
        *{box-sizing:border-box}body{font-family:system-ui,sans-serif;background:#f5f5f4;color:#1c1917;margin:0;padding:1.5rem}
        main{max-width:56rem;margin:2rem auto}.top{display:flex;justify-content:space-between;align-items:center;gap:1rem}
        .card{background:#fff;border-radius:.9rem;padding:1.5rem;margin-top:1rem;box-shadow:0 8px 24px #0000000d}
        dl{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}dt{color:#78716c;font-size:.9rem}dd{margin:.25rem 0 0;font-weight:650}
        button{background:#a16207;color:white;border:0;border-radius:.5rem;padding:.55rem .8rem;font-weight:650;cursor:pointer}input,select{padding:.5rem;border:1px solid #d6d3d1;border-radius:.4rem;max-width:100%}.entry{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;padding:.7rem 0;border-bottom:1px solid #e7e5e4}.muted{color:#78716c;font-size:.9rem}.status{background:#dcfce7;padding:.75rem;border-radius:.5rem}.error{background:#fee2e2;color:#991b1b;padding:.75rem;border-radius:.5rem}table{width:100%;text-align:left;border-collapse:collapse}th,td{padding:.65rem .55rem;border-bottom:1px solid #e7e5e4;vertical-align:middle}
        @media(max-width:600px){dl{grid-template-columns:1fr}.top{align-items:flex-start}}
    </style>
</head>
<body>
<main>
    <div class="top">
        <div><p>Madrasaa Ifaa Islaamaa</p><h1>Welcome, {{ $teacher->user->name }}</h1></div>
        <form method="post" action="{{ route('teacher.logout') }}">@csrf<button type="submit">Sign out</button></form>
    </div>
    @if (session('status'))<p class="status">{{ session('status') }}</p>@endif
    @if ($errors->any())<div class="error"><strong>Please fix these items:</strong><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    <section class="card">
        <h2>Teacher profile</h2>
        <dl>
            <div><dt>Email</dt><dd>{{ $teacher->user->email }}</dd></div>
            <div><dt>Phone</dt><dd>{{ $teacher->phone ?: '—' }}</dd></div>
            <div><dt>Employment status</dt><dd>{{ ucfirst($teacher->employment_status) }}</dd></div>
            <div><dt>Date joined</dt><dd>{{ $teacher->joined_on?->format('M j, Y') ?: '—' }}</dd></div>
        </dl>
    </section>
    <section class="card">
        <h2>My teaching assignments</h2>
        @if ($teacher->assignments->isEmpty())
            <p>No classes or subjects have been assigned yet. Please contact the school administrator.</p>
        @else
            <div style="overflow-x:auto"><table style="width:100%;text-align:left;border-collapse:collapse"><thead><tr><th>Academic year</th><th>Grade / section</th><th>Subject</th></tr></thead><tbody>
                @foreach ($teacher->assignments as $assignment)
                    <tr><td>{{ $assignment->section->academicYear->name }}</td><td>{{ $assignment->section->grade->name }} · {{ $assignment->section->name }}</td><td>{{ $assignment->subject->name }}</td></tr>
                @endforeach
            </tbody></table></div>
        @endif
    </section>
    <section class="card">
        <h2>Attendance register</h2>
        <p class="muted">One register for all your assigned sections. Ended sessions remain visible as read-only attendance reports.</p>
        <div style="margin-bottom:1rem"><a href="{{ route('attendance.reports.show', ['from' => now()->startOfMonth()->toDateString(), 'until' => today()->toDateString()]) }}">Open monthly attendance report and downloads</a></div>
        @if ($attendanceSessions->isEmpty())
            <p class="muted">No attendance sessions have been created for your assigned classes this month.</p>
        @elseif ($attendanceRowCount === 0)
            <p class="muted">There are no students assigned to the sessions yet.</p>
        @else
            <div style="overflow-x:auto"><table><thead><tr><th>Date / session report</th><th>Student</th><th>Grade / section</th><th>Attendance</th><th>Recorded by</th><th>Session</th><th>Action</th></tr></thead><tbody>
                @foreach ($attendanceSessions as $attendanceSession)
                    @php
                        $sessionSummary = $attendanceSession->attendanceReportSummary();
                    @endphp
                    @foreach ($attendanceSession->section->students->sortBy('full_name') as $student)
                        @php
                            $attendanceRecord = $attendanceSession->records->firstWhere('student_id', $student->id);
                            $savedStatus = $attendanceRecord?->status ?? '';
                            $attendanceFormId = 'attendance-'.$attendanceSession->id.'-'.$student->id;
                        @endphp
                        <tr>
                            <td><form id="{{ $attendanceFormId }}" method="post" action="{{ route('teacher.attendance.store') }}">@csrf<input type="hidden" name="attendance_session_id" value="{{ $attendanceSession->id }}"><input type="hidden" name="student_id" value="{{ $student->id }}"></form>{{ $attendanceSession->session_date->format('M j, Y') }} · {{ $attendanceSession->name }}<br><span class="muted">{{ $sessionSummary['marked'] }}/{{ $sessionSummary['total'] }} marked · P {{ $sessionSummary['counts']['present'] }} · A {{ $sessionSummary['counts']['absent'] }} · L {{ $sessionSummary['counts']['late'] }} · E {{ $sessionSummary['counts']['excused'] }} · {{ $sessionSummary['completion'] }}%</span></td>
                            <td>{{ $student->full_name }}<br><span class="muted">{{ $student->admission_number }}</span></td>
                            <td>{{ $attendanceSession->section->grade->name }} · {{ $attendanceSession->section->name }}</td>
                            <td>@if($attendanceSession->isEditableByTeacher())<select form="{{ $attendanceFormId }}" name="status" required aria-label="Attendance for {{ $student->full_name }}"><option value="" disabled @selected($savedStatus === '')>Select status</option><option value="present" @selected($savedStatus === 'present')>Present</option><option value="absent" @selected($savedStatus === 'absent')>Absent</option><option value="late" @selected($savedStatus === 'late')>Late</option><option value="excused" @selected($savedStatus === 'excused')>Excused</option></select>@else{{ $savedStatus ? ucfirst(str_replace('_',' ',$savedStatus)) : 'Not recorded' }}@endif</td>
                            <td>{{ $attendanceRecord?->recorder?->name ?? '—' }}@if($attendanceRecord)<br><span class="muted">{{ $attendanceRecord->updated_at->format('M j, H:i') }}</span>@endif</td>
                            <td>{{ $attendanceSession->status === 'completed' ? 'Completed' : ($attendanceSession->isEditableByTeacher() ? 'Open' : 'Ended · read only') }}</td>
                            <td>@if($attendanceSession->isEditableByTeacher())<button form="{{ $attendanceFormId }}" type="submit">Save</button>@else<span class="muted">Locked</span>@endif</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody></table></div>
        @endif
    </section>
    @foreach ($teacher->assignments as $assignment)
        <section class="card">
            <h2>Marks · {{ $assignment->section->grade->name }} {{ $assignment->section->name }} · {{ $assignment->subject->name }}</h2>
            @php
                $examSubjects = $assignment->section->exams->flatMap(fn ($exam) => $exam->subjects->where('subject_id', $assignment->subject_id));
            @endphp
            @forelse ($examSubjects as $examSubject)
                @php
                    $components = $examSubject->components;
                    $hasSavedMarks = $components->contains(fn ($component) => $component->marks->isNotEmpty());
                    $teacherCanEditMarks = in_array($examSubject->marks_status, ['draft', 'returned'], true) && ! in_array($examSubject->exam->status, ['completed', 'published'], true);
                    $registerStatus = ['draft' => 'Draft', 'submitted' => 'Waiting for Admin review', 'returned' => 'Returned for correction', 'approved' => 'Approved'];
                @endphp
                <div style="border-top:1px solid #e7e5e4;margin-top:1rem;padding-top:1rem">
                    <h3>{{ $examSubject->exam->name }} · {{ $examSubject->subject->name }}</h3>
                    <p class="muted">Register status: <strong>{{ $registerStatus[$examSubject->marks_status] ?? ucfirst($examSubject->marks_status) }}</strong></p>
                    @if($examSubject->marks_status === 'returned' && $examSubject->review_note)<p class="error">Admin feedback: {{ $examSubject->review_note }}</p>@endif
                    @if ($teacherCanEditMarks)
                        <form method="post" action="{{ route('teacher.assessment-components.save', $examSubject) }}" data-assessment-editor>
                            @csrf
                            <div data-component-rows>
                                @foreach ($components as $index => $component)
                                    <div class="entry component-row">
                                        <input type="hidden" name="components[{{ $index }}][id]" value="{{ $component->id }}">
                                        <label>Assessment <input type="text" name="components[{{ $index }}][name]" value="{{ $component->name }}" maxlength="80" required></label>
                                        <label>Weight % <input type="number" name="components[{{ $index }}][weight]" value="{{ $component->weight }}" min="0.01" max="100" step="0.01" required></label>
                                        <label>Maximum mark <input type="number" name="components[{{ $index }}][max_marks]" value="{{ $component->max_marks }}" min="0.01" step="0.01" required></label>
                                        <button type="button" data-remove-component>Remove</button>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" data-add-component>Add assessment type</button>
                            <button type="submit">Save types and weights</button>
                            <span class="muted">Weights must total 100%. Score contribution = mark ÷ maximum mark × weight. A type with saved marks cannot be removed or lowered below a saved mark.</span>
                        </form>
                    @endif

                    @if ($components->isEmpty())
                        <p class="muted">Add assessment types and save their weights before entering marks.</p>
                    @else
                        <div style="overflow-x:auto;margin-top:1rem">
                            <table>
                                <thead><tr><th>Student</th>@foreach ($components as $component)<th>{{ $component->name }}<br><span class="muted">{{ $component->weight }}% · max {{ $component->max_marks }}</span></th>@endforeach<th>Current total</th>@if($teacherCanEditMarks)<th>Save</th>@endif</tr></thead>
                                <tbody>
                                @foreach ($assignment->section->students->sortBy('full_name') as $student)
                                    @php
                                        $markFormId = 'marks-'.$examSubject->id.'-'.$student->id;
                                    @endphp
                                    @php
                                        $studentWeightedTotal = 0;
                                        $studentCompletedComponents = 0;
                                        foreach ($components as $component) {
                                            $componentMark = $component->marks->firstWhere('student_id', $student->id);
                                            if ($componentMark && (float) $component->max_marks > 0) {
                                                $studentWeightedTotal += ((float) $componentMark->marks_obtained / (float) $component->max_marks) * (float) $component->weight;
                                                $studentCompletedComponents++;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $student->full_name }}<br><span class="muted">{{ $student->admission_number }}</span><form id="{{ $markFormId }}" method="post" action="{{ route('teacher.marks.store') }}">@csrf<input type="hidden" name="exam_subject_id" value="{{ $examSubject->id }}"><input type="hidden" name="student_id" value="{{ $student->id }}"></form></td>
                                        @foreach ($components as $component)
                                            @php($existingMark = $component->marks->firstWhere('student_id', $student->id))
                                            <td>@if($teacherCanEditMarks)<input form="{{ $markFormId }}" type="number" name="marks[{{ $component->id }}]" min="0" max="{{ $component->max_marks }}" step="0.01" value="{{ $existingMark?->marks_obtained }}" aria-label="{{ $component->name }} for {{ $student->full_name }}">@else{{ $existingMark?->marks_obtained ?? '—' }}@endif</td>
                                        @endforeach
                                        <td>{{ $studentCompletedComponents }}/{{ $components->count() }} · {{ number_format($studentWeightedTotal, 2) }}%</td>
                                        @if($teacherCanEditMarks)<td><button form="{{ $markFormId }}" type="submit">Save draft</button></td>@endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($teacherCanEditMarks && $hasSavedMarks)
                            <form method="post" action="{{ route('teacher.assessment-marks.submit', $examSubject) }}" style="margin-top:1rem">@csrf<button type="submit">Submit completed register to Admin</button></form>
                        @endif
                        @if($examSubject->marks_status === 'submitted')<p class="muted">The register is locked until Admin approves it or returns it for correction.</p>@endif
                    @endif
                </div>
            @empty
                <p class="muted">No exam subject has been scheduled for this assignment yet.</p>
            @endforelse
        </section>
    @endforeach
</main>
<script>
document.querySelectorAll('[data-assessment-editor]').forEach((editor) => {
    const rows = editor.querySelector('[data-component-rows]');
    let nextIndex = rows.querySelectorAll('.component-row').length;
    editor.querySelector('[data-add-component]').addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'entry component-row';
        const fields = [
            ['Assessment', 'name', 'text', '', ''],
            ['Weight %', 'weight', 'number', '0.01', '100'],
            ['Maximum mark', 'max_marks', 'number', '0.01', '999999.99'],
        ];
        fields.forEach(([label, field, type, min, max]) => {
            const wrapper = document.createElement('label');
            wrapper.textContent = label + ' ';
            const input = document.createElement('input');
            input.type = type;
            input.name = `components[${nextIndex}][${field}]`;
            input.required = true;
            if (type === 'number') {
                input.min = min;
                input.max = max;
                input.step = '0.01';
                if (field === 'max_marks') input.value = '100';
            } else {
                input.maxLength = 80;
            }
            wrapper.append(input);
            row.append(wrapper);
        });
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.textContent = 'Remove';
        remove.addEventListener('click', () => row.remove());
        row.append(remove);
        rows.append(row);
        nextIndex += 1;
    });
    editor.querySelectorAll('[data-remove-component]').forEach((button) => {
        button.addEventListener('click', () => button.closest('.component-row').remove());
    });
});
</script>
</body>
</html>

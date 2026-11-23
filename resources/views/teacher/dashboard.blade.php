<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Teacher dashboard</title>
    <style>
        *{box-sizing:border-box}html{scroll-behavior:smooth;scroll-padding-top:84px}body{font-family:system-ui,sans-serif;background:#f5f5f4;color:#1c1917;margin:0;padding:0}
        .portal-topbar{position:sticky;top:0;z-index:20;min-height:68px;display:flex;align-items:center;justify-content:space-between;gap:1rem;padding:.75rem 1.5rem;background:#fff;border-bottom:1px solid #e7e5e4;box-shadow:0 2px 8px #0000000a}.portal-brand{display:flex;align-items:center;gap:.75rem}.portal-brand-mark{display:grid;place-items:center;width:38px;height:38px;border-radius:.7rem;background:#fef3c7;color:#92400e;font-weight:800}.portal-brand p{margin:0;color:#78716c;font-size:.82rem}.portal-brand h1{margin:.1rem 0 0;font-size:1.05rem}.portal-top-actions{display:flex;align-items:center;gap:1rem}.portal-top-actions form{margin:0}.teacher-name{color:#57534e;font-size:.9rem}
        .portal-layout{display:grid;grid-template-columns:260px minmax(0,1fr);min-height:calc(100vh - 68px)}.sidebar{position:sticky;top:68px;height:calc(100vh - 68px);overflow-y:auto;padding:1.1rem .85rem;background:#fff;border-right:1px solid #e7e5e4}.sidebar-label{margin:1.1rem .65rem .4rem;color:#a8a29e;font-size:.7rem;font-weight:750;letter-spacing:.08em;text-transform:uppercase}.sidebar-label:first-child{margin-top:.2rem}.nav-link{display:block;margin:.15rem 0;padding:.62rem .7rem;border-radius:.55rem;color:#44403c;text-decoration:none;font-size:.9rem}.nav-link:hover,.nav-link:focus{background:#f5f5f4;color:#92400e}.nav-link small{display:block;margin-top:.18rem;color:#78716c;font-size:.75rem}.sidebar-empty{padding:.4rem .7rem;color:#a8a29e;font-size:.8rem}.portal-content{min-width:0;max-width:none;margin:0;padding:1.5rem}.card{background:#fff;border-radius:.9rem;padding:1.5rem;margin-top:1rem;box-shadow:0 8px 24px #0000000d;scroll-margin-top:86px}.attendance-section-block{margin-top:1.1rem;padding:1rem;border:1px solid #e7e5e4;border-radius:.7rem;background:#fafaf9}.attendance-section-heading{display:flex;justify-content:space-between;align-items:center;gap:1rem;margin:0 0 .7rem}.attendance-section-heading h3{margin:0;font-size:1rem}.attendance-section-heading span{color:#78716c;font-size:.8rem}
        dl{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}dt{color:#78716c;font-size:.9rem}dd{margin:.25rem 0 0;font-weight:650}
        button{background:#a16207;color:white;border:0;border-radius:.5rem;padding:.55rem .8rem;font-weight:650;cursor:pointer}input,select{padding:.5rem;border:1px solid #d6d3d1;border-radius:.4rem;max-width:100%}.entry{display:flex;align-items:center;gap:.5rem;flex-wrap:wrap;padding:.7rem 0;border-bottom:1px solid #e7e5e4}.muted{color:#78716c;font-size:.9rem}.status{background:#dcfce7;padding:.75rem;border-radius:.5rem}.error{background:#fee2e2;color:#991b1b;padding:.75rem;border-radius:.5rem}.saved-note{margin-top:.45rem;color:#166534;font-size:.82rem;font-weight:650}table{width:100%;text-align:left;border-collapse:collapse}th,td{padding:.65rem .55rem;border-bottom:1px solid #e7e5e4;vertical-align:middle}
        @media(max-width:820px){.portal-layout{grid-template-columns:1fr}.sidebar{position:sticky;top:68px;z-index:15;width:100%;height:auto;max-height:190px;display:flex;align-items:center;gap:.25rem;overflow-x:auto;overflow-y:hidden;padding:.5rem .75rem;border-right:0;border-bottom:1px solid #e7e5e4}.sidebar-label{display:none}.nav-link{flex:0 0 auto;margin:0;padding:.55rem .65rem;white-space:nowrap}.sidebar-empty{display:none}.portal-content{padding:1rem}}
        @media(max-width:600px){dl{grid-template-columns:1fr}.portal-topbar{padding:.7rem .85rem}.teacher-name{display:none}.portal-brand h1{font-size:.95rem}.portal-brand p{font-size:.72rem}.portal-content{padding:.75rem}.card{padding:1rem}}
    </style>
</head>
<body>
<header class="portal-topbar">
    <div class="portal-brand"><span class="portal-brand-mark">M</span><div><p>Madrasaa Ifaa Islaamaa</p><h1>Teacher portal</h1></div></div>
    <div class="portal-top-actions"><span class="teacher-name">{{ $teacher->user->name }}</span><form method="post" action="{{ route('teacher.logout') }}">@csrf<button type="submit">Sign out</button></form></div>
</header>
<div class="portal-layout">
    <aside class="sidebar" aria-label="Teacher navigation">
        <div class="sidebar-label">Main menu</div>
        <a class="nav-link" href="#dashboard-top">Dashboard</a>
        <a class="nav-link" href="#attendance-register">Attendance register</a>
        <a class="nav-link" href="{{ route('attendance.reports.show', ['from' => now()->startOfMonth()->toDateString(), 'until' => today()->toDateString()]) }}">Attendance reports</a>

        <div class="sidebar-label">Assigned sections & subjects</div>
        @forelse ($teacher->assignments as $assignment)
            <a class="nav-link" href="#assignment-{{ $assignment->id }}">
                {{ $assignment->section->grade->name }} · {{ $assignment->section->name }}
                <small>{{ $assignment->subject->name }}</small>
            </a>
        @empty
            <div class="sidebar-empty">No teaching assignments.</div>
        @endforelse

        <div class="sidebar-label">Attendance sessions</div>
        @forelse ($attendanceSessions as $attendanceSession)
            <a class="nav-link" href="#attendance-session-{{ $attendanceSession->id }}">
                {{ $attendanceSession->session_date->format('M j') }} · {{ $attendanceSession->name }}
                <small>{{ $attendanceSession->section->grade->name }} · {{ $attendanceSession->section->name }}</small>
            </a>
        @empty
            <div class="sidebar-empty">No sessions this month.</div>
        @endforelse
    </aside>
    <main class="portal-content">
    @if (session('status'))<p class="status">{{ session('status') }}</p>@endif

    <section class="card" id="dashboard-top">
        <h2>Teacher profile</h2>
        <dl>
            <div><dt>Email</dt><dd>{{ $teacher->user->email }}</dd></div>
            <div><dt>Phone</dt><dd>{{ $teacher->phone ?: '—' }}</dd></div>
            <div><dt>Employment status</dt><dd>{{ ucfirst($teacher->employment_status) }}</dd></div>
            <div><dt>Date joined</dt><dd>{{ $teacher->joined_on?->format('M j, Y') ?: '—' }}</dd></div>
        </dl>
    </section>
    <section class="card" id="teaching-assignments">
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
    <section class="card" id="attendance-register">
        <h2>Attendance register</h2>
        <p class="muted">One register for all your assigned sections. Ended sessions remain visible as read-only attendance reports.</p>
        <div style="margin-bottom:1rem"><a href="{{ route('attendance.reports.show', ['from' => now()->startOfMonth()->toDateString(), 'until' => today()->toDateString()]) }}">Open monthly attendance report and downloads</a></div>
        @if ($attendanceSessions->isEmpty())
            <p class="muted">No attendance sessions have been created for your assigned classes this month.</p>
        @elseif ($attendanceRowCount === 0)
            <p class="muted">There are no students assigned to the sessions yet.</p>
        @else
            @foreach ($attendanceSessions->groupBy('section_id') as $sectionSessions)
                @php
                    $section = $sectionSessions->first()->section;
                @endphp
                <div class="attendance-section-block">
                    <div class="attendance-section-heading">
                        <h3>{{ $section->grade->name }} · {{ $section->name }}</h3>
                        <span>{{ $sectionSessions->count() }} attendance {{ \Illuminate\Support\Str::plural('session', $sectionSessions->count()) }}</span>
                    </div>
                    <div style="overflow-x:auto"><table><thead><tr><th>Date / session report</th><th>Student</th><th>Attendance</th><th>Recorded by</th><th>Session</th><th>Action</th></tr></thead><tbody>
                        @foreach ($sectionSessions as $attendanceSession)
                            @php
                                $sessionSummary = $attendanceSession->attendanceReportSummary();
                            @endphp
                            @foreach ($attendanceSession->section->students->sortBy('full_name') as $student)
                                @php
                                    $attendanceRecord = $attendanceSession->records->firstWhere('student_id', $student->id);
                                    $savedStatus = $attendanceRecord?->status ?? '';
                                    $attendanceFormId = 'attendance-'.$attendanceSession->id.'-'.$student->id;
                                @endphp
                                <tr id="{{ $attendanceFormId }}-row">
                                    <td>@if($loop->first)<span id="attendance-session-{{ $attendanceSession->id }}"></span>@endif<form id="{{ $attendanceFormId }}" method="post" action="{{ route('teacher.attendance.store') }}">@csrf<input type="hidden" name="attendance_session_id" value="{{ $attendanceSession->id }}"><input type="hidden" name="student_id" value="{{ $student->id }}"></form>{{ $attendanceSession->session_date->format('M j, Y') }} · {{ $attendanceSession->name }}<br><span class="muted">Window: {{ $attendanceSession->starts_at?->format('M j, H:i') ?? '—' }} – {{ $attendanceSession->ends_at?->format('M j, H:i') ?? '—' }}</span><br><span class="muted">{{ $sessionSummary['marked'] }}/{{ $sessionSummary['total'] }} marked · P {{ $sessionSummary['counts']['present'] }} · A {{ $sessionSummary['counts']['absent'] }} · L {{ $sessionSummary['counts']['late'] }} · E {{ $sessionSummary['counts']['excused'] }} · {{ $sessionSummary['completion'] }}%</span></td>
                                    <td>{{ $student->full_name }}<br><span class="muted">{{ $student->admission_number }}</span></td>
                                    <td>@if($attendanceSession->isEditableByTeacher())<select form="{{ $attendanceFormId }}" name="status" required aria-label="Attendance for {{ $student->full_name }}"><option value="" disabled @selected($savedStatus === '')>Select status</option><option value="present" @selected($savedStatus === 'present')>Present</option><option value="absent" @selected($savedStatus === 'absent')>Absent</option><option value="late" @selected($savedStatus === 'late')>Late</option><option value="excused" @selected($savedStatus === 'excused')>Excused</option></select>@else{{ $savedStatus ? ucfirst(str_replace('_',' ',$savedStatus)) : 'Not recorded' }}@endif</td>
                                    <td>{{ $attendanceRecord?->recorder?->name ?? '—' }}@if($attendanceRecord)<br><span class="muted">{{ $attendanceRecord->updated_at->format('M j, H:i') }}</span>@endif</td>
                                    <td>{{ $attendanceSession->status === 'completed' ? 'Completed' : ($attendanceSession->isEditableByTeacher() ? 'Open' : 'Ended · read only') }}</td>
                                    <td>@if($attendanceSession->isEditableByTeacher())<button form="{{ $attendanceFormId }}" type="submit">Save</button>@else<span class="muted">Locked</span>@endif
                                        @if(session('attendance_saved_'.$attendanceSession->id.'_'.$student->id))<div class="saved-note" role="status">{{ session('attendance_saved_'.$attendanceSession->id.'_'.$student->id) }}</div>@endif
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody></table></div>
                </div>
            @endforeach
        @endif
    </section>
    @foreach ($teacher->assignments as $assignment)
        <section class="card" id="assignment-{{ $assignment->id }}">
            <h2>Marks · {{ $assignment->section->grade->name }} {{ $assignment->section->name }} · {{ $assignment->term?->name ?? 'Semester not assigned' }} · {{ $assignment->subject->name }}</h2>
            @php
                $examSubjects = $assignment->section->exams
                    ->filter(fn ($exam) => ! $assignment->term_id || (int) $assignment->term_id === (int) $exam->term_id)
                    ->flatMap(fn ($exam) => $exam->subjects->where('subject_id', $assignment->subject_id));
            @endphp
            @forelse ($examSubjects as $examSubject)
                @php
                    $components = $examSubject->components;
                    $termIsOpen = $termEntryOpen[$examSubject->id] ?? true;
                    $teacherCanEditMarks = $termIsOpen && in_array($examSubject->marks_status, ['draft', 'returned'], true) && ! in_array($examSubject->exam->status, ['completed', 'published'], true);
                    $registerStatus = ['draft' => 'Draft', 'submitted' => 'Waiting for Admin review', 'returned' => 'Returned for correction', 'approved' => 'Approved'];
                @endphp
                <div style="border-top:1px solid #e7e5e4;margin-top:1rem;padding-top:1rem">
                    <h3>{{ $examSubject->exam->name }} · {{ $examSubject->exam->term?->name ?? 'No semester' }} · {{ $examSubject->subject->name }}</h3>
                    <p class="muted">Register status: <strong>{{ $registerStatus[$examSubject->marks_status] ?? ucfirst($examSubject->marks_status) }}</strong></p>
                    @if($examSubject->marks_status === 'returned' && $examSubject->review_note)<p class="error">Admin feedback: {{ $examSubject->review_note }}</p>@endif
                    @if(! $termIsOpen)<p class="muted">This semester is locked until every teacher has submitted the previous semester registers.</p>@endif
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
                                    <tr id="{{ $markFormId }}-row">
                                        <td>{{ $student->full_name }}<br><span class="muted">{{ $student->admission_number }}</span><form id="{{ $markFormId }}" method="post" action="{{ route('teacher.marks.store') }}">@csrf<input type="hidden" name="exam_subject_id" value="{{ $examSubject->id }}"><input type="hidden" name="student_id" value="{{ $student->id }}"></form></td>
                                        @foreach ($components as $component)
                                            @php
                                                $existingMark = $component->marks->firstWhere('student_id', $student->id);
                                            @endphp
                                            <td>@if($teacherCanEditMarks)<input form="{{ $markFormId }}" type="number" name="marks[{{ $component->id }}]" min="0" max="{{ $component->max_marks }}" step="0.01" value="{{ $existingMark?->marks_obtained }}" aria-label="{{ $component->name }} for {{ $student->full_name }}">@else{{ $existingMark?->marks_obtained ?? '—' }}@endif</td>
                                        @endforeach
                                        <td>{{ $studentCompletedComponents }}/{{ $components->count() }} · {{ number_format($studentWeightedTotal, 2) }}%</td>
                                        @if($teacherCanEditMarks)<td><button form="{{ $markFormId }}" type="submit">Save draft</button>
                                            @if(session('marks_saved_'.$examSubject->id.'_'.$student->id))<div class="saved-note" role="status">{{ session('marks_saved_'.$examSubject->id.'_'.$student->id) }}</div>@endif
                                        </td>@endif
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($teacherCanEditMarks)
                            <form method="post" action="{{ route('teacher.assessment-marks.submit', $examSubject) }}" style="margin-top:1rem">@csrf<button type="submit">Submit completed register to Admin</button></form>
                            @if($errors->has('marks_register_'.$examSubject->id))<p id="marks-register-error-{{ $examSubject->id }}" class="error" role="alert">{{ $errors->first('marks_register_'.$examSubject->id) }}</p>@endif
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
</div>
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

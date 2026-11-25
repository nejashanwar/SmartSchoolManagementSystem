<x-filament-panels::page>
    <div style="display: grid; gap: 20px;">
        <div style="max-width: 520px;">
            <label for="exam-filter" style="display: block; margin-bottom: 7px; font-size: 14px; font-weight: 600;">Examination</label>
            <select id="exam-filter" wire:model.live="examId" style="width: 100%; min-height: 42px; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; color: #111827;">
                @foreach ($exams as $option)
                    <option value="{{ $option->id }}">{{ $option->name }} · {{ $option->section->grade->name ?? '' }} {{ $option->section->name ?? '' }}</option>
                @endforeach
            </select>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 14px;">
            <div style="min-width: 240px; flex: 1;">
                <label for="marks-student-search" style="display: block; margin-bottom: 7px; font-size: 14px; font-weight: 600;">Search student</label>
                <input id="marks-student-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Name or student ID" style="width: 100%; min-height: 42px; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; color: #111827;">
            </div>
            <div style="min-width: 220px;">
                <label for="marks-student-filter" style="display: block; margin-bottom: 7px; font-size: 14px; font-weight: 600;">Student marks</label>
                <select id="marks-student-filter" wire:model.live="studentFilter" style="width: 100%; min-height: 42px; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; color: #111827;">
                    <option value="all">All students</option>
                    <option value="incomplete">Incomplete marks</option>
                    <option value="ready">Ready for verification</option>
                    <option value="verified">Verified</option>
                </select>
            </div>
        </div>

        @if (! $exam)
            <div style="padding: 24px; border: 1px solid #e5e7eb; border-radius: 12px; background: white; color: #4b5563;">No examinations with subjects are available yet.</div>
        @else
            @if ($exam->term && $semesterProgress)
                <section style="padding: 14px 18px; border: 1px solid {{ $semesterGateOpen ? '#86efac' : '#fcd34d' }}; border-radius: 10px; background: {{ $semesterGateOpen ? '#f0fdf4' : '#fffbeb' }}; color: #374151;">
                    <strong>{{ $exam->term->name }} marks entry: {{ $semesterGateOpen ? 'Open' : 'Locked' }}</strong>
                    <div style="margin-top: 5px;">{{ $progressTerm->name }} registers submitted: {{ $semesterProgress['submitted'] }} / {{ $semesterProgress['expected'] }} subjects.</div>
                    @if ($semesterProgress['pending_subjects']->isNotEmpty())
                        <div style="margin-top: 5px;">Still waiting for: {{ $semesterProgress['pending_subjects']->join(', ') }}</div>
                    @elseif (! $semesterProgress['complete'])
                        <div style="margin-top: 5px;">At least one register still has incomplete student marks.</div>
                    @endif
                </section>
            @endif
            @php
                $registersSubmitted = $exam->subjects->isNotEmpty()
                    && $exam->subjects->every(fn ($subject) => in_array($subject->marks_status, ['submitted', 'approved'], true));
                $subjectHeaderStyle = 'padding: 12px 14px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; background: #eaf0f7; color: #1f2937; text-align: center; font-weight: 700; white-space: nowrap;';
                $componentHeaderStyle = 'min-width: 105px; padding: 10px 8px; border-bottom: 1px solid #d1d5db; border-left: 1px solid #d1d5db; background: #f8fafc; color: #374151; text-align: center; white-space: nowrap;';
                $cellStyle = 'min-width: 105px; padding: 12px 8px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center; white-space: nowrap; color: #374151;';
            @endphp

            <section style="overflow: hidden; border: 1px solid #d1d5db; border-radius: 12px; background: white; box-shadow: 0 1px 3px rgba(15, 23, 42, .08);">
                <header style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
                    <h2 style="margin: 0; font-size: 17px; font-weight: 700; color: #111827;">{{ $exam->name }} · {{ $exam->section->grade->name ?? '' }} {{ $exam->section->name ?? '' }}</h2>
                    <p style="margin: 5px 0 0; font-size: 13px; color: #6b7280;">Each subject has a weighted average. The overall average and rank appear after results are generated.</p>
                </header>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; min-width: max-content; border-collapse: separate; border-spacing: 0; font-size: 14px;">
                        <thead>
                            <tr>
                                <th rowspan="2" style="position: sticky; left: 0; z-index: 3; min-width: 210px; padding: 14px 16px; border-bottom: 1px solid #cbd5e1; background: #eaf0f7; text-align: left; color: #1f2937;">Student</th>
                                @foreach ($exam->subjects as $subject)
                                    <th colspan="{{ max(1, $subject->components->count() + 1) }}" style="{{ $subjectHeaderStyle }}">{{ $subject->subject->name ?? 'Subject' }}</th>
                                @endforeach
                                <th rowspan="2" style="padding: 14px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; background: #eaf0f7; text-align: right; color: #1f2937;">Total<br><small style="font-weight: 400;">subject points</small></th>
                                <th rowspan="2" style="padding: 14px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; background: #eaf0f7; text-align: center; color: #1f2937;">Overall Avg</th>
                                <th rowspan="2" style="padding: 14px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; background: #eaf0f7; text-align: center; color: #1f2937;">Rank</th>
                                <th rowspan="2" style="padding: 14px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; background: #eaf0f7; text-align: center; color: #1f2937;">Action</th>
                            </tr>
                            <tr>
                                @foreach ($exam->subjects as $subject)
                                    @foreach ($subject->components as $component)
                                        @php
                                            $shortName = match (strtolower(trim($component->name))) {
                                                'assignment 1' => 'A1',
                                                'assignment 2' => 'A2',
                                                'quiz/test', 'quiz', 'test' => 'Quiz',
                                                'mid-term', 'midterm' => 'Mid',
                                                'final exam', 'final' => 'Final',
                                                default => \Illuminate\Support\Str::limit($component->name, 10, ''),
                                            };
                                        @endphp
                                        <th title="{{ $component->name }} · maximum {{ number_format((float) $component->max_marks, 2) }}" style="{{ $componentHeaderStyle }}">
                                            {{ $shortName }}<br>
                                            <small style="font-size: 11px; font-weight: 400; color: #6b7280;">{{ number_format((float) $component->weight, 2) }}% · /{{ number_format((float) $component->max_marks, 2) }}</small>
                                        </th>
                                    @endforeach
                                    <th title="Weighted average out of 100 for {{ $subject->subject->name ?? 'this subject' }}" style="{{ $componentHeaderStyle }} background: #edf7ed;">Avg<br><small style="font-size: 11px; font-weight: 400; color: #6b7280;">/100</small></th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($students as $student)
                                @php($total = 0)
                                @php($allStudentMarksVerified = $components->isNotEmpty())
                                @php($allStudentMarksSubmitted = $components->isNotEmpty())
                                @foreach ($components as $component)
                                    @php($studentMark = $marks->get($student->id.'-'.$component->id))
                                    @if (! $studentMark || $studentMark->status !== 'verified')
                                        @php($allStudentMarksVerified = false)
                                    @endif
                                    @if (! $studentMark || ! in_array($studentMark->status, ['submitted', 'verified'], true))
                                        @php($allStudentMarksSubmitted = false)
                                    @endif
                                @endforeach
                                <tr style="background: {{ $loop->odd ? '#ffffff' : '#f8fafc' }};">
                                    <th style="position: sticky; left: 0; z-index: 2; min-width: 210px; padding: 12px 16px; border-bottom: 1px solid #e5e7eb; background: {{ $loop->odd ? '#ffffff' : '#f8fafc' }}; text-align: left; color: #111827;">
                                        <span style="display: block; white-space: normal;">{{ $student->full_name }}</span>
                                        <small style="display: block; margin-top: 3px; font-size: 11px; font-weight: 400; color: #6b7280;">{{ $student->admission_number }}</small>
                                    </th>
                                    @foreach ($exam->subjects as $subject)
                                        @foreach ($subject->components as $component)
                                            @php($mark = $marks->get($student->id.'-'.$component->id))
                                            <td style="{{ $cellStyle }}">
                                                @if ($mark)
                                                    {{ number_format((float) $mark->marks_obtained, 2) }}
                                                    <span title="{{ ucfirst($mark->status) }}" style="font-size: 12px; color: {{ $mark->status === 'verified' ? '#15803d' : '#9ca3af' }};">{{ $mark->status === 'verified' ? '✓' : '·' }}</span>
                                                @else
                                                    <span style="color: #9ca3af;">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        @php($subjectAverage = $subjectAverages[$student->id][$subject->id] ?? null)
                                        <td style="{{ $cellStyle }} background: #f2faf2; font-weight: 700; color: #166534;">
                                            {{ $subjectAverage !== null ? number_format($subjectAverage, 2).'%' : 'Pending' }}
                                        </td>
                                    @endforeach
                                    @php($result = $results->get($student->id))
                                    <td style="padding: 12px 14px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: right; white-space: nowrap; font-weight: 700; color: #111827;">
                                        {{ $result ? number_format((float) $result->total_marks, 2).' / '.number_format((float) $result->maximum_marks, 2) : 'Pending' }}
                                    </td>
                                    <td style="padding: 12px 14px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center; white-space: nowrap; font-weight: 700; color: #166534;">
                                        {{ $result ? number_format((float) $result->average_percentage, 2).'%' : 'Pending' }}
                                    </td>
                                    <td style="padding: 12px 14px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center; white-space: nowrap; font-weight: 700; color: #1d4ed8;">
                                        {{ $result?->section_rank ? '#'.$result->section_rank : 'Pending' }}
                                    </td>
                                    <td style="padding: 10px 12px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center; white-space: nowrap;">
                                        @if ($allStudentMarksVerified)
                                            <x-filament::badge color="success" icon="heroicon-m-check-circle">Verified</x-filament::badge>
                                        @elseif ($registersSubmitted && $allStudentMarksSubmitted && ! in_array($exam->status, ['completed', 'published'], true))
                                            <x-filament::button size="sm" color="success" icon="heroicon-m-check" wire:click="verifyStudentMarks({{ $student->id }})" wire:loading.attr="disabled" wire:target="verifyStudentMarks({{ $student->id }})">Verify</x-filament::button>
                                        @else
                                            <x-filament::badge color="gray">{{ ! $registersSubmitted ? 'Awaiting submission' : 'Incomplete' }}</x-filament::badge>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $components->count() + $exam->subjects->count() + 5 }}" style="padding: 36px 16px; text-align: center; color: #6b7280;">No students are assigned to this section.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <footer style="padding: 11px 18px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280;">
                    Subject Avg = weighted assessment score out of 100. Total = sum of subject averages. Overall Avg = Total ÷ subject count. Rank is within this section. Draft results and rank appear after all subject registers are approved.
                </footer>
            </section>
        @endif
    </div>
</x-filament-panels::page>

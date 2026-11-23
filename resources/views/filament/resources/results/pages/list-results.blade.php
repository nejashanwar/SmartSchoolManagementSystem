<x-filament-panels::page>
    <div style="display: grid; gap: 18px;">
        <div style="display: flex; flex-wrap: wrap; align-items: end; gap: 14px;">
            <div style="min-width: 280px; max-width: 520px; flex: 1;">
                <label for="results-exam-filter" style="display: block; margin-bottom: 7px; font-size: 14px; font-weight: 600;">Examination</label>
                <select id="results-exam-filter" wire:model.live="examId" style="width: 100%; min-height: 42px; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; color: #111827;">
                    @foreach ($exams as $option)
                        <option value="{{ $option->id }}">{{ $option->name }} · {{ $option->section->grade->name ?? '' }} {{ $option->section->name ?? '' }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width: 170px;">
                <label for="results-status-filter" style="display: block; margin-bottom: 7px; font-size: 14px; font-weight: 600;">Result status</label>
                <select id="results-status-filter" wire:model.live="statusFilter" style="width: 100%; min-height: 42px; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; color: #111827;">
                    <option value="all">All results</option>
                    <option value="draft">Draft</option>
                    <option value="published">Published</option>
                </select>
            </div>
            <div style="min-width: 220px;">
                <label for="results-search" style="display: block; margin-bottom: 7px; font-size: 14px; font-weight: 600;">Search student</label>
                <input id="results-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Name or student ID" style="width: 100%; min-height: 42px; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 8px; background: white; color: #111827;">
            </div>
        </div>

        @if (! $exam)
            <div style="padding: 24px; border: 1px solid #e5e7eb; border-radius: 12px; background: white; color: #4b5563;">No results have been generated yet.</div>
        @else
            <section style="overflow: hidden; border: 1px solid #d1d5db; border-radius: 12px; background: white; box-shadow: 0 1px 3px rgba(15, 23, 42, .08);">
                <header style="padding: 16px 20px; border-bottom: 1px solid #e5e7eb;">
                    <h2 style="margin: 0; font-size: 17px; font-weight: 700; color: #111827;">{{ $exam->name }} · {{ $exam->section->grade->name ?? '' }} {{ $exam->section->name ?? '' }}</h2>
                    <p style="margin: 5px 0 0; font-size: 13px; color: #6b7280;">Each subject has its own column. Subject values are weighted averages out of 100; overall average and rank come from the approved examination results.</p>
                </header>

                <div style="overflow-x: auto;">
                    <table style="width: 100%; min-width: max-content; border-collapse: separate; border-spacing: 0; font-size: 14px;">
                        <thead>
                            <tr style="background: #eaf0f7; color: #1f2937;">
                                <th style="min-width: 150px; padding: 13px 14px; border-bottom: 1px solid #cbd5e1; text-align: left;">Student ID</th>
                                <th style="min-width: 200px; padding: 13px 14px; border-bottom: 1px solid #cbd5e1; text-align: left;">Student</th>
                                @foreach ($exam->subjects as $subject)
                                    <th title="Weighted average for {{ $subject->subject->name ?? 'subject' }}" style="min-width: 130px; padding: 13px 12px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; text-align: center; white-space: nowrap;">
                                        {{ $subject->subject->name ?? 'Subject' }}<br>
                                        <small style="font-size: 11px; font-weight: 400; color: #6b7280;">Average / 100</small>
                                    </th>
                                @endforeach
                                <th style="min-width: 125px; padding: 13px 12px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; text-align: center;">Total</th>
                                <th style="min-width: 125px; padding: 13px 12px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; text-align: center;">Overall Avg</th>
                                <th style="min-width: 90px; padding: 13px 12px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; text-align: center;">Rank</th>
                                @if ($isFinalSemester)
                                    <th style="min-width: 130px; padding: 13px 12px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; text-align: center;">Cumulative Avg</th>
                                    <th style="min-width: 100px; padding: 13px 12px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; text-align: center;">Cumulative Rank</th>
                                @endif
                                <th style="min-width: 90px; padding: 13px 12px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; text-align: center;">Grade</th>
                                <th style="min-width: 100px; padding: 13px 12px; border-bottom: 1px solid #cbd5e1; border-left: 1px solid #cbd5e1; text-align: center;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($results as $result)
                                <tr style="background: {{ $loop->odd ? '#ffffff' : '#f8fafc' }};">
                                    <td style="padding: 12px 14px; border-bottom: 1px solid #e5e7eb; white-space: nowrap;">{{ $result->student->admission_number }}</td>
                                    <td style="padding: 12px 14px; border-bottom: 1px solid #e5e7eb; font-weight: 600;">{{ $result->student->full_name }}</td>
                                    @foreach ($exam->subjects as $subject)
                                        @php($subjectAverage = $subjectAverages[$result->student_id][$subject->id] ?? null)
                                        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center; white-space: nowrap; font-variant-numeric: tabular-nums; {{ $subjectAverage === null ? 'color: #9ca3af;' : 'font-weight: 600; color: #166534;' }}">
                                            {{ $subjectAverage !== null ? number_format($subjectAverage, 2).'%' : '—' }}
                                        </td>
                                    @endforeach
                                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center; white-space: nowrap; font-variant-numeric: tabular-nums;">{{ number_format((float) $result->total_marks, 2) }} / {{ number_format((float) $result->maximum_marks, 2) }}</td>
                                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center; white-space: nowrap; font-weight: 700; font-variant-numeric: tabular-nums; color: #166534;">{{ number_format((float) $result->average_percentage, 2) }}%</td>
                                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center; white-space: nowrap; font-weight: 700; color: #1d4ed8;">#{{ $result->section_rank ?? '—' }}</td>
                                    @if ($isFinalSemester)
                                        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center;">{{ $result->cumulative_average_percentage !== null ? number_format((float) $result->cumulative_average_percentage, 2).'%' : '—' }}</td>
                                        <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center;">{{ $result->cumulative_rank ? '#'.$result->cumulative_rank : '—' }}</td>
                                    @endif
                                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center; font-weight: 700;">{{ $result->grade_label ?? '—' }}</td>
                                    <td style="padding: 12px; border-bottom: 1px solid #e5e7eb; border-left: 1px solid #e5e7eb; text-align: center;">{{ ucfirst($result->status) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="{{ $exam->subjects->count() + 7 + ($isFinalSemester ? 2 : 0) }}" style="padding: 36px 16px; text-align: center; color: #6b7280;">No results match these filters.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <footer style="padding: 11px 18px; border-top: 1px solid #e5e7eb; font-size: 12px; color: #6b7280;">Subject average = weighted assessment scores. Total = sum of subject averages. Overall Avg = Total ÷ number of subjects. Rank is within this section.</footer>
            </section>
        @endif
    </div>
</x-filament-panels::page>

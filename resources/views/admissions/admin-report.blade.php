<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Admission applications report') }}</title>
    <style>
        body { color: #172033; font: 13px/1.45 Arial, sans-serif; margin: 24px auto; padding: 0 20px; }
        .toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
        button, .toolbar a { background: #1f4d3a; border: 0; border-radius: 4px; color: #fff; cursor: pointer; padding: 10px 14px; text-decoration: none; }
        h1 { margin-bottom: 4px; }
        .meta { color: #475569; margin-top: 0; }
        .table-wrap { border: 1px solid #cbd5e1; overflow-x: auto; }
        table { border-collapse: collapse; min-width: 2600px; width: 100%; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #e2e8f0; font-size: 11px; white-space: nowrap; }
        td { max-width: 280px; overflow-wrap: anywhere; white-space: pre-wrap; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        @page { size: landscape; margin: 8mm; }
        @media print {
            body { font-size: 6pt; margin: 0; padding: 0; }
            .toolbar { display: none; }
            .table-wrap { border: 0; overflow: visible; }
            table { min-width: 0; table-layout: auto; }
            th, td { font-size: 6pt; padding: 2px; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
        }
        @media (max-width: 700px) {
            body { padding: 0 12px; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">{{ __('Print / Save as PDF') }}</button>
        <a href="{{ $excelUrl }}">{{ __('Download Excel') }}</a>
        <a href="{{ route('filament.admin.resources.admission-applications.index') }}">{{ __('Back') }}</a>
    </div>

    <main>
        <h1>{{ __('Admission applications report') }}</h1>
        <p class="meta">
            {{ __('Generated') }}: {{ $generatedAt->format('Y-m-d H:i') }} ·
            {{ __('Applications') }}: {{ $applications->count() }}
            @if (filled($search))
                · {{ __('Search') }}: {{ $search }}
            @endif
        </p>

        <div class="table-wrap">
            <table aria-label="{{ __('Admission applications') }}">
                <thead>
                    <tr>
                        <th>{{ __('Reference') }}</th>
                        <th>{{ __('Submitted at') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Student full name') }}</th>
                        <th>{{ __('First name') }}</th>
                        <th>{{ __('Father name') }}</th>
                        <th>{{ __('Grandfather name') }}</th>
                        <th>{{ __('Gender') }}</th>
                        <th>{{ __('Date of birth') }}</th>
                        <th>{{ __('Place of birth') }}</th>
                        <th>{{ __('Nationality') }}</th>
                        <th>{{ __('Region') }}</th>
                        <th>{{ __('Zone') }}</th>
                        <th>{{ __('Woreda') }}</th>
                        <th>{{ __('Legacy zone / woreda') }}</th>
                        <th>{{ __('Kebele') }}</th>
                        <th>{{ __('Student address') }}</th>
                        <th>{{ __('Applying grade') }}</th>
                        <th>{{ __('Academic year') }}</th>
                        <th>{{ __('Parent / guardian') }}</th>
                        <th>{{ __('Guardian phone') }}</th>
                        <th>{{ __('Guardian email') }}</th>
                        <th>{{ __('Guardian relationship') }}</th>
                        <th>{{ __('Guardian occupation') }}</th>
                        <th>{{ __('Guardian address') }}</th>
                        <th>{{ __('Emergency contact') }}</th>
                        <th>{{ __('Emergency contact phone') }}</th>
                        <th>{{ __('Previous school') }}</th>
                        <th>{{ __('Previous grade') }}</th>
                        <th>{{ __('Transfer reason') }}</th>
                        <th>{{ __('Health notes') }}</th>
                        <th>{{ __('Photo attached') }}</th>
                        <th>{{ __('Birth certificate attached') }}</th>
                        <th>{{ __('Previous transcript attached') }}</th>
                        <th>{{ __('Reviewed by') }}</th>
                        <th>{{ __('Reviewed at') }}</th>
                        <th>{{ __('Review reason') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($applications as $application)
                        <tr>
                            <td>{{ $application->reference_number }}</td>
                            <td>{{ $application->submitted_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ str($application->status)->replace('_', ' ')->title() }}</td>
                            <td>{{ $application->student_name }}</td>
                            <td>{{ $application->first_name }}</td>
                            <td>{{ $application->father_name }}</td>
                            <td>{{ $application->grandfather_name }}</td>
                            <td>{{ $application->gender }}</td>
                            <td>{{ $application->date_of_birth?->format('Y-m-d') }}</td>
                            <td>{{ $application->place_of_birth }}</td>
                            <td>{{ $application->nationality }}</td>
                            <td>{{ $application->region }}</td>
                            <td>{{ $application->zone }}</td>
                            <td>{{ $application->woreda }}</td>
                            <td>{{ $application->zone_woreda }}</td>
                            <td>{{ $application->kebele }}</td>
                            <td>{{ $application->address }}</td>
                            <td>{{ $application->grade?->name }}</td>
                            <td>{{ $application->academicYear?->name }}</td>
                            <td>{{ $application->guardian_name }}</td>
                            <td>{{ $application->guardian_phone }}</td>
                            <td>{{ $application->guardian_email }}</td>
                            <td>{{ $application->guardian_relationship }}</td>
                            <td>{{ $application->guardian_occupation }}</td>
                            <td>{{ $application->guardian_address }}</td>
                            <td>{{ $application->emergency_contact_name }}</td>
                            <td>{{ $application->emergency_contact_phone }}</td>
                            <td>{{ $application->previous_school }}</td>
                            <td>{{ $application->previous_grade }}</td>
                            <td>{{ $application->transfer_reason }}</td>
                            <td>{{ $application->health_notes }}</td>
                            <td>{{ filled($application->photo_path) ? __('Available') : __('Not provided') }}</td>
                            <td>{{ filled($application->birth_certificate_path) ? __('Available') : __('Not provided') }}</td>
                            <td>{{ filled($application->previous_transcript_path) ? __('Available') : __('Not provided') }}</td>
                            <td>{{ $application->reviewer?->name }}</td>
                            <td>{{ $application->reviewed_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $application->review_reason }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="37">{{ __('No applications match the selected filters.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Student admission application') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{
            --text:#1c1917;
            --muted:#57534e;
            --primary:#166534;
            --primary-dark:#14532d;
            --primary-light:#22c55e;
            --accent:#a16207;
            --accent-light:#f59e0b;
            --bg:#fafaf9;
            --border:#e7e5e4;
        }
        *{box-sizing:border-box}
        html{scroll-behavior:smooth}
        body{
            margin:0;
            min-height:100vh;
            display:flex;
            flex-direction:column;
            font-family:"Poppins",system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;
            font-size:16px;
            line-height:1.5;
            color:var(--text);
            background:var(--bg);
            overflow-x:hidden;
        }
        .ar{font-family:"Amiri","Traditional Arabic",serif;direction:rtl}

        /* Duubaa sochii qabu */
        body::before,body::after{
            content:"";position:fixed;z-index:-1;border-radius:50%;
            filter:blur(70px);opacity:.5;pointer-events:none;
        }
        body::before{
            width:26rem;height:26rem;top:-6rem;right:-6rem;
            background:radial-gradient(circle,#86efac,transparent 70%);
            animation:float 12s ease-in-out infinite;
        }
        body::after{
            width:24rem;height:24rem;bottom:-6rem;left:-6rem;
            background:radial-gradient(circle,#fde68a,transparent 70%);
            animation:float 15s ease-in-out infinite reverse;
        }

        /* Topbar */
        .topbar{
            position:sticky;top:0;z-index:50;
            background:rgba(250,250,249,.85);
            backdrop-filter:blur(12px);-webkit-backdrop-filter:blur(12px);
            border-bottom:1px solid var(--border);
            transition:box-shadow .3s ease;
        }
        .topbar.scrolled{box-shadow:0 6px 24px rgba(22,101,52,.12)}
        .topbar-inner{
            max-width:68rem;margin:auto;padding:.8rem 1.5rem;
            display:flex;justify-content:space-between;align-items:center;gap:1rem;flex-wrap:wrap;
            animation:slideDown .7s ease both;
        }
        .brand{display:flex;align-items:center;gap:.6rem;text-decoration:none;font-weight:800;font-size:1.2rem}
        .brand .logo{
            width:2.4rem;height:2.4rem;border-radius:50%;display:grid;place-items:center;font-size:1.3rem;
            background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;
            box-shadow:0 6px 14px rgba(22,101,52,.3);animation:glow 3s ease-in-out infinite;
        }
        .brand .name{
            background:linear-gradient(90deg,var(--primary),var(--primary-light),var(--accent));
            background-size:200% auto;-webkit-background-clip:text;background-clip:text;color:transparent;
            animation:shimmer 6s linear infinite;
        }
        .top-actions{display:flex;align-items:center;gap:.4rem;flex-wrap:wrap}
        .pill{
            color:var(--muted);text-decoration:none;font-size:.88rem;font-weight:600;
            padding:.5rem .9rem;border-radius:999px;border:1px solid transparent;
            transition:color .25s,background .25s,border-color .25s,transform .25s,box-shadow .25s;
        }
        .pill:hover{
            color:var(--primary);background:#f0fdf4;border-color:#bbf7d0;
            transform:translateY(-2px);box-shadow:0 6px 14px rgba(22,101,52,.12);
        }
        .progress{position:absolute;left:0;bottom:-1px;height:3px;width:0;background:linear-gradient(90deg,var(--primary-light),var(--accent-light))}

        /* Page header */
        .page{width:100%;max-width:60rem;margin:0 auto;padding:2.5rem 1.25rem 4rem;flex:1}
        .hero{text-align:center;margin-bottom:2rem}
        .bismillah{
            font-size:clamp(1.5rem,4vw,2.1rem);color:var(--primary);margin:0 0 .5rem;
            text-shadow:0 2px 12px rgba(34,197,94,.25);animation:fadeUp .8s ease both;
        }
        .eyebrow{
            display:inline-flex;align-items:center;gap:.5rem;color:var(--accent);
            background:#fef3c7;border:1px solid #fde68a;padding:.4rem .9rem;border-radius:999px;
            font-weight:700;letter-spacing:.12em;text-transform:uppercase;font-size:.72rem;
            animation:fadeUp .8s ease .1s both;
        }
        .eyebrow::before{
            content:"";width:.5rem;height:.5rem;border-radius:50%;background:var(--accent-light);
            animation:pulse 1.8s ease-out infinite;
        }
        h1{
            font-size:clamp(1.9rem,5vw,3.2rem);font-weight:800;line-height:1.1;letter-spacing:-.02em;
            margin:1rem 0 .7rem;
            background:linear-gradient(120deg,var(--text) 0%,var(--primary) 55%,var(--primary-light) 100%);
            -webkit-background-clip:text;background-clip:text;color:transparent;
            animation:fadeUp .8s ease .2s both;
        }
        .note{
            max-width:40rem;margin:0 auto;color:var(--muted);font-size:1rem;line-height:1.7;
            animation:fadeUp .8s ease .3s both;
        }
        .divider{text-align:center;color:var(--accent-light);letter-spacing:.6em;font-size:1rem;margin:1.2rem 0 0}

        /* Form card */
        .card-main{
            background:rgba(255,255,255,.92);border:1px solid var(--border);border-radius:1.5rem;
            padding:1.8rem;box-shadow:0 22px 50px rgba(22,101,52,.1);position:relative;overflow:hidden;
            animation:fadeUp .8s ease .4s both;
        }
        .card-main::before{
            content:"";position:absolute;inset:0 0 auto 0;height:5px;
            background:linear-gradient(90deg,var(--primary-light),var(--accent-light),var(--primary-light));
            background-size:200% auto;animation:shimmer 6s linear infinite;
        }

        .grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}
        .span-2{grid-column:span 2}

        fieldset{
            margin:1.4rem 0 0;padding:1.3rem 1.2rem 1.4rem;border:1px solid var(--border);border-radius:1.1rem;
            background:#fff;transition:border-color .3s,box-shadow .3s;min-width:0;
        }
        fieldset:first-of-type{margin-top:.5rem}
        fieldset:hover,fieldset:focus-within{border-color:#bbf7d0;box-shadow:0 12px 28px rgba(22,101,52,.08)}
        legend{
            padding:.35rem .9rem;font-weight:700;font-size:.95rem;color:var(--primary-dark);
            background:#f0fdf4;border:1px solid #bbf7d0;border-radius:999px;
        }
        legend::before{margin-right:.4rem}
        .f-student legend::before{content:"🎓"}
        .f-parent legend::before{content:"👨‍👩‍👧"}
        .f-school legend::before{content:"📚"}
        .f-admission legend::before{content:"🕌"}

        label{display:block;font-weight:600;font-size:.9rem;margin:.75rem 0 .35rem;color:var(--primary-dark)}
        label:has(+ :required)::after{content:" *";color:#b91c1c}
        input,select,textarea{
            display:block;width:100%;padding:.75rem .85rem;font:inherit;font-size:.95rem;color:var(--text);
            background:#fafaf9;border:1.5px solid #d6d3d1;border-radius:.7rem;
            transition:border-color .25s,box-shadow .25s,background .25s,transform .25s;
        }
        input:hover,select:hover,textarea:hover{border-color:#86efac}
        input:focus,select:focus,textarea:focus{
            outline:none;background:#fff;border-color:var(--primary-light);
            box-shadow:0 0 0 4px rgba(34,197,94,.18);transform:translateY(-1px);
        }
        input:disabled,select:disabled{background:#f5f5f4;color:#a8a29e;cursor:not-allowed}
        textarea{resize:vertical;min-height:4.5rem}
        input[type=file]{padding:.5rem;background:#f0fdf4;border-style:dashed;border-color:#86efac;cursor:pointer}
        input[type=file]::file-selector-button{
            margin-right:.8rem;padding:.5rem .9rem;border:0;border-radius:.5rem;font-weight:700;cursor:pointer;
            color:#fff;background:linear-gradient(135deg,var(--primary),var(--primary-light));
            transition:filter .25s,transform .25s;
        }
        input[type=file]::file-selector-button:hover{filter:brightness(1.08);transform:translateY(-1px)}

        .error{color:#b91c1c;font-size:.85rem;margin-top:.3rem}
        .alert{
            background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:.9rem;
            padding:.9rem 1.1rem;margin-bottom:.5rem;font-weight:600;animation:shake .5s ease;
        }
        .alert::before{content:"⚠️ "}

        /* Buttons */
        .actions{display:flex;flex-wrap:wrap;gap:.9rem;align-items:center;margin-top:1.8rem}
        button[type=submit]{
            position:relative;display:inline-flex;align-items:center;gap:.6rem;overflow:hidden;
            background:linear-gradient(135deg,var(--primary),var(--primary-light));color:#fff;
            padding:.95rem 1.8rem;border:0;border-radius:.75rem;font:inherit;font-weight:700;font-size:1rem;cursor:pointer;
            box-shadow:0 8px 20px rgba(22,101,52,.28);
            transition:transform .25s ease,box-shadow .25s ease,filter .25s ease;
        }
        button[type=submit]::before{
            content:"";position:absolute;top:0;left:-80%;width:50%;height:100%;
            background:linear-gradient(120deg,transparent,rgba(255,255,255,.45),transparent);transform:skewX(-20deg);
        }
        button[type=submit]::after{content:"→";transition:transform .25s ease}
        button[type=submit]:hover{transform:translateY(-3px) scale(1.02);box-shadow:0 14px 30px rgba(22,101,52,.38);filter:brightness(1.05)}
        button[type=submit]:hover::before{left:130%;transition:left .7s ease}
        button[type=submit]:hover::after{transform:translateX(5px)}
        button[type=submit]:active{transform:translateY(0) scale(.98)}
        button[type=submit]:disabled{filter:grayscale(.4);cursor:wait}
        .back{
            color:var(--primary);text-decoration:none;font-weight:600;padding:.9rem 1.2rem;border-radius:.75rem;
            border:2px solid var(--primary-light);transition:.25s;
        }
        .back:hover{background:#f0fdf4;transform:translateY(-2px)}
        button:focus-visible,.pill:focus-visible,.back:focus-visible{outline:3px solid #86efac;outline-offset:3px}

        .dua{text-align:center;margin-top:1.6rem;color:var(--accent);font-size:1.5rem}
        .dua small{display:block;font-family:"Poppins",sans-serif;direction:ltr;font-size:.8rem;color:var(--muted);margin-top:.2rem}

        /* Footer */
        .site-footer{background:#0f2e1a;color:#bbf7d0;border-top:4px solid var(--accent-light);text-align:center;padding:1.6rem 1rem;font-size:.9rem}
        .site-footer .ar{font-size:1.3rem;color:#fde68a;margin-bottom:.3rem}
        .site-footer a{color:#fde68a;text-decoration:none}

        /* Reveal on scroll */
        .reveal{opacity:0;transform:translateY(28px);transition:opacity .7s ease,transform .7s ease}
        .reveal.show{opacity:1;transform:none}

        /* Animations */
        @keyframes fadeUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
        @keyframes slideDown{from{opacity:0;transform:translateY(-20px)}to{opacity:1;transform:translateY(0)}}
        @keyframes float{0%,100%{transform:translate(0,0) scale(1)}50%{transform:translate(30px,40px) scale(1.12)}}
        @keyframes shimmer{to{background-position:200% center}}
        @keyframes pulse{0%{box-shadow:0 0 0 0 rgba(245,158,11,.6)}100%{box-shadow:0 0 0 10px rgba(245,158,11,0)}}
        @keyframes glow{0%,100%{box-shadow:0 6px 14px rgba(22,101,52,.3)}50%{box-shadow:0 0 22px rgba(34,197,94,.7)}}
        @keyframes shake{0%,100%{transform:translateX(0)}20%,60%{transform:translateX(-6px)}40%,80%{transform:translateX(6px)}}

        /* Responsive */
        @media(max-width:600px){
            .page{padding:1.5rem .9rem 3rem}
            .card-main{padding:1.1rem;border-radius:1.1rem}
            .grid{grid-template-columns:1fr}
            .span-2{grid-column:auto}
            .topbar-inner{padding:.7rem 1rem}
            body::before,body::after{opacity:.3}
            .actions{flex-direction:column;align-items:stretch}
            button[type=submit],.back{width:100%;justify-content:center;text-align:center}
        }

        @media(prefers-reduced-motion:reduce){
            *,*::before,*::after{animation:none !important;transition:none !important}
            .reveal{opacity:1;transform:none}
        }
    </style>
</head>
<body>

<div class="topbar" id="topbar">
    <div class="topbar-inner">
        <a class="brand" href="{{ url('/') }}">
            <span class="logo">☪</span>
            <span class="name">{{ $schoolName ?? config('app.name') }}</span>
        </a>
        <div class="top-actions">
            <a class="pill" href="{{ url('/') }}">← Mana</a>
            <a class="pill" href="{{ route('lang.switch', 'om') }}">Afaan Oromoo</a>
            <a class="pill" href="{{ route('lang.switch', 'en') }}">English</a>
        </div>
    </div>
    <div class="progress" id="progress"></div>
</div>

<div class="page">
    <div class="hero">
        <p class="bismillah ar" lang="ar">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
        <span class="eyebrow">School admissions</span>
        <h1>{{ __('Student admission application') }}</h1>
        <p class="note">{{ __('Complete the form during the registration period. Submitting an application does not guarantee admission.') }}</p>
        <p class="divider" aria-hidden="true">✦ ❖ ۞ ❖ ✦</p>
    </div>

    <main class="card-main">
        @if ($errors->any())
            <div class="alert">{{ __('Please correct the highlighted form information and submit again.') }}</div>
        @endif

        <form method="post" action="{{ route('admissions.store') }}" enctype="multipart/form-data" id="admissionForm">
            @csrf
            <div class="grid">
                <fieldset class="span-2 f-student reveal">
                    <legend>{{ __('Student information') }}</legend>
                    <div class="grid">
                        <div>
                            <label for="first_name">{{ __('First name') }}</label>
                            <input id="first_name" name="first_name" value="{{ old('first_name') }}" required maxlength="100" autocomplete="given-name">
                            @error('first_name')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="father_name">{{ __('Father name') }}</label>
                            <input id="father_name" name="father_name" value="{{ old('father_name') }}" required maxlength="100">
                            @error('father_name')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="grandfather_name">{{ __('Grandfather name ') }}</label>
                            <input id="grandfather_name" name="grandfather_name" value="{{ old('grandfather_name') }}" maxlength="100">
                            @error('grandfather_name')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="gender">{{ __('Gender') }}</label>
                            <select id="gender" name="gender">
                                <option value="">{{ __('Choose a gender') }}</option>
                                <option value="male" @selected(old('gender') == 'male')>{{ __('Male') }}</option>
                                <option value="female" @selected(old('gender') == 'female')>{{ __('Female') }}</option>
                            </select>
                            @error('gender')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="date_of_birth">{{ __('Date of birth ') }}</label>
                            <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth') }}">
                            @error('date_of_birth')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="nationality">{{ __('Nationality ') }}</label>
                            <select id="nationality" name="nationality">
                                <option value="">{{ __('Choose a nationality') }}</option>
                                @foreach ($countries as $countryCode => $countryName)
                                    <option value="{{ $countryCode }}" @selected(old('nationality') === $countryCode)>{{ $countryName }}</option>
                                @endforeach
                            </select>
                            @error('nationality')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div id="ethiopian-location-fields" class="span-2" hidden>
                            <div class="grid">
                                <div>
                                    <label for="region_code">{{ __('Region') }}</label>
                                    <select id="region_code" name="region_code" disabled>
                                        <option value="">{{ __('Choose a region') }}</option>
                                        @foreach ($ethiopiaRegions as $regionCode => $region)
                                            <option value="{{ $regionCode }}" @selected(old('region_code') === $regionCode)>{{ $region['name'] }}</option>
                                        @endforeach
                                    </select>
                                    @error('region_code')<div class="error">{{ $message }}</div>@enderror
                                </div>
                                <div>
                                    <label for="zone_code">{{ __('Zone') }}</label>
                                    <select id="zone_code" name="zone_code" disabled>
                                        <option value="">{{ __('Choose a zone') }}</option>
                                    </select>
                                    @error('zone_code')<div class="error">{{ $message }}</div>@enderror
                                </div>
                                <div>
                                    <label for="woreda_code">{{ __('Woreda') }}</label>
                                    <select id="woreda_code" name="woreda_code" disabled>
                                        <option value="">{{ __('Choose a woreda') }}</option>
                                    </select>
                                    @error('woreda_code')<div class="error">{{ $message }}</div>@enderror
                                </div>
                                <div>
                                    <label for="kebele">{{ __('Kebele') }}</label>
                                    <input id="kebele" name="kebele" value="{{ old('kebele') }}" maxlength="100" disabled>
                                    @error('kebele')<div class="error">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div>
                            <label for="photo_path">{{ __('Student photo (JPG, PNG, or WebP; up to 2 MB)') }}</label>
                            <input id="photo_path" type="file" name="photo_path" accept="image/jpeg,image/png,image/webp">
                            @error('photo_path')<div class="error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="span-2 f-parent reveal">
                    <legend>{{ __('Parent or guardian') }}</legend>
                    <div class="grid">
                        <div class="span-2">
                            <label for="guardian_name">{{ __('Parent or guardian full name') }}</label>
                            <input id="guardian_name" name="guardian_name" value="{{ old('guardian_name') }}" required maxlength="255" autocomplete="name">
                            @error('guardian_name')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="guardian_phone">{{ __('Parent or guardian phone') }}</label>
                            <input id="guardian_phone" name="guardian_phone" value="{{ old('guardian_phone') }}" required maxlength="40" autocomplete="tel">
                            @error('guardian_phone')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="guardian_email">{{ __('Parent or guardian email (optional)') }}</label>
                            <input id="guardian_email" type="email" name="guardian_email" value="{{ old('guardian_email') }}" maxlength="255" autocomplete="email">
                            @error('guardian_email')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="guardian_relationship">{{ __('Relationship (optional)') }}</label>
                            <input id="guardian_relationship" name="guardian_relationship" value="{{ old('guardian_relationship') }}" maxlength="50">
                            @error('guardian_relationship')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="guardian_occupation">{{ __('Occupation (optional)') }}</label>
                            <input id="guardian_occupation" name="guardian_occupation" value="{{ old('guardian_occupation') }}" maxlength="100">
                            @error('guardian_occupation')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div class="span-2">
                            <label for="guardian_address">{{ __('Guardian address ') }}</label>
                            <textarea id="guardian_address" name="guardian_address" rows="2" maxlength="5000">{{ old('guardian_address') }}</textarea>
                            @error('guardian_address')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="emergency_contact_name">{{ __('Emergency contact name (optional)') }}</label>
                            <input id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}" maxlength="150">
                            @error('emergency_contact_name')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="emergency_contact_phone">{{ __('Emergency contact phone (optional)') }}</label>
                            <input id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}" maxlength="25">
                            @error('emergency_contact_phone')<div class="error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="span-2 f-school reveal">
                    <legend>{{ __('Previous school and health') }}</legend>
                    <div class="grid">
                        <div>
                            <label for="previous_school">{{ __('Previous school (optional)') }}</label>
                            <input id="previous_school" name="previous_school" value="{{ old('previous_school') }}" maxlength="255">
                            @error('previous_school')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="previous_grade">{{ __('Previous grade (optional)') }}</label>
                            <input id="previous_grade" name="previous_grade" value="{{ old('previous_grade') }}" maxlength="100">
                            @error('previous_grade')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div class="span-2">
                            <label for="transfer_reason">{{ __('Reason for transfer (optional)') }}</label>
                            <textarea id="transfer_reason" name="transfer_reason" rows="2" maxlength="5000">{{ old('transfer_reason') }}</textarea>
                            @error('transfer_reason')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div class="span-2">
                            <label for="health_notes">{{ __('Health notes (optional)') }}</label>
                            <textarea id="health_notes" name="health_notes" rows="2" maxlength="5000">{{ old('health_notes') }}</textarea>
                            @error('health_notes')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="birth_certificate_path">{{ __('Birth certificate (PDF, JPG, or PNG; up to 2 MB)') }}</label>
                            <input id="birth_certificate_path" type="file" name="birth_certificate_path" accept="application/pdf,image/jpeg,image/png">
                            @error('birth_certificate_path')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="previous_transcript_path">{{ __('Previous transcript (PDF, JPG, or PNG; up to 2 MB)') }}</label>
                            <input id="previous_transcript_path" type="file" name="previous_transcript_path" accept="application/pdf,image/jpeg,image/png">
                            @error('previous_transcript_path')<div class="error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </fieldset>

                <fieldset class="span-2 f-admission reveal">
                    <legend>{{ __('Admission requested') }}</legend>
                    <div class="grid">
                        <div>
                            <label for="grade_id">{{ __('Applying grade') }}</label>
                            <select id="grade_id" name="grade_id" required>
                                <option value="">{{ __('Choose a grade') }}</option>
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade->id }}" @selected(old('grade_id') == $grade->id)>{{ $grade->name }}</option>
                                @endforeach
                            </select>
                            @error('grade_id')<div class="error">{{ $message }}</div>@enderror
                        </div>
                        <div>
                            <label for="academic_year_id">{{ __('Academic year') }}</label>
                            <select id="academic_year_id" name="academic_year_id" required>
                                <option value="">{{ __('Choose an academic year') }}</option>
                                @foreach ($academicYears as $academicYear)
                                    <option value="{{ $academicYear->id }}" @selected(old('academic_year_id') == $academicYear->id)>{{ $academicYear->name }}</option>
                                @endforeach
                            </select>
                            @error('academic_year_id')<div class="error">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </fieldset>
            </div>

            <div class="actions">
                <button type="submit" id="submitBtn">{{ __('Submit application') }}</button>
                <a class="back" href="{{ url('/') }}">← Mana</a>
            </div>
        </form>

        <div class="dua ar" lang="ar">رَبِّ زِدْنِي عِلْمًا
            <small>“Yaa Gooftaa koo, beekumsa naaf dabali.” (Ṭaha 20:114)</small>
        </div>
    </main>
</div>

<div class="site-footer">
    <div class="ar" lang="ar">جَزَاكُمُ اللَّهُ خَيْرًا</div>
    <div>{{ $schoolName ?? config('app.name') }} · Grades 1–8 · © {{ date('Y') }}</div>
</div>

<script>
    const nationalitySelect = document.getElementById('nationality');
    const ethiopianLocationFields = document.getElementById('ethiopian-location-fields');
    const regionSelect = document.getElementById('region_code');
    const zoneSelect = document.getElementById('zone_code');
    const woredaSelect = document.getElementById('woreda_code');
    const kebeleInput = document.getElementById('kebele');
    const ethiopiaRegions = {{ \Illuminate\Support\Js::from($ethiopiaRegions) }};
    const chooseZoneLabel = {{ \Illuminate\Support\Js::from(__('Choose a zone')) }};
    const chooseWoredaLabel = {{ \Illuminate\Support\Js::from(__('Choose a woreda')) }};
    const oldZoneCode = {{ \Illuminate\Support\Js::from(old('zone_code', '')) }};
    const oldWoredaCode = {{ \Illuminate\Support\Js::from(old('woreda_code', '')) }};

    function replaceOptions(select, entries, placeholder, selectedCode = '') {
        select.replaceChildren(new Option(placeholder, ''));

        for (const [code, entry] of Object.entries(entries)) {
            const option = new Option(typeof entry === 'string' ? entry : entry.name, code);
            option.selected = code === selectedCode;
            select.add(option);
        }
    }

    function updateWoredas(selectedWoredaCode = '') {
        const zone = ethiopiaRegions[regionSelect.value]?.zones?.[zoneSelect.value];
        const woredas = zone?.woredas ?? {};

        replaceOptions(woredaSelect, woredas, chooseWoredaLabel, selectedWoredaCode);
        woredaSelect.disabled = nationalitySelect.value !== 'ET' || Object.keys(woredas).length === 0;
    }

    function updateZones(selectedZoneCode = '', selectedWoredaCode = '') {
        const region = ethiopiaRegions[regionSelect.value];
        const zones = region?.zones ?? {};

        replaceOptions(zoneSelect, zones, chooseZoneLabel, selectedZoneCode);
        zoneSelect.disabled = nationalitySelect.value !== 'ET' || Object.keys(zones).length === 0;
        updateWoredas(selectedWoredaCode);
    }

    function updateEthiopianLocation(restorePreviousSelection = false) {
        const isEthiopia = nationalitySelect.value === 'ET';

        ethiopianLocationFields.hidden = !isEthiopia;
        regionSelect.disabled = !isEthiopia;
        regionSelect.required = isEthiopia;
        zoneSelect.required = isEthiopia;
        woredaSelect.required = isEthiopia;
        kebeleInput.disabled = !isEthiopia;

        if (!isEthiopia) {
            regionSelect.value = '';
            kebeleInput.value = '';
            updateZones();

            return;
        }

        if (!restorePreviousSelection) {
            regionSelect.value = '';
        }

        updateZones(
            restorePreviousSelection ? oldZoneCode : '',
            restorePreviousSelection ? oldWoredaCode : '',
        );
    }

    nationalitySelect.addEventListener('change', () => updateEthiopianLocation());
    regionSelect.addEventListener('change', () => updateZones());
    zoneSelect.addEventListener('change', () => updateWoredas());
    updateEthiopianLocation(true);

    /* ===== Design helpers ===== */
    (function () {
        const topbar = document.getElementById('topbar');
        const bar = document.getElementById('progress');

        function onScroll() {
            const h = document.documentElement;
            const max = h.scrollHeight - h.clientHeight;
            topbar.classList.toggle('scrolled', window.scrollY > 10);
            bar.style.width = (max > 0 ? (window.scrollY / max) * 100 : 0) + '%';
        }
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        const io = new IntersectionObserver((entries) => {
            entries.forEach((e) => {
                if (e.isIntersecting) {
                    e.target.classList.add('show');
                    io.unobserve(e.target);
                }
            });
        }, { threshold: 0.08 });
        document.querySelectorAll('.reveal').forEach((el) => io.observe(el));

        /* Submit irratti button cufama (si'a lama hin ergamu) */
        const form = document.getElementById('admissionForm');
        const btn = document.getElementById('submitBtn');
        form.addEventListener('submit', () => {
            if (form.checkValidity()) {
                btn.disabled = true;
                btn.textContent = '⏳ ...';
            }
        });
    })();
</script>
</body>
</html>
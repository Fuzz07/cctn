@php
    // Reopen the step that holds the first validation error instead of always starting at 1
    $stepFields = [
        1 => ['firstname', 'middlename', 'lastname', 'birthdate', 'age', 'gender', 'place_of_birth', 'civil_status'],
        2 => ['contact_no', 'email', 'address_province', 'address_municipality', 'address_barangay'],
        3 => ['username', 'password', 'password_confirmation', 'profile_photo', 'proof_of_billing'],
    ];
    $initialStep = 1;
    foreach ($stepFields as $stepNumber => $fields) {
        foreach ($fields as $field) {
            if ($errors->has($field)) {
                $initialStep = $stepNumber;
                break 2;
            }
        }
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - BCTVI Bantayan</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
    <style>
        body {
            background: linear-gradient(rgba(15, 23, 42, 0.55), rgba(15, 23, 42, 0.7)), url('{{ asset('assets/images/login-bg.jpg') }}') center / cover no-repeat fixed;
            min-height: 100vh; overflow-x: hidden; margin: 0;
        }
        .auth-layout {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            min-height: 100vh;
            width: 100%;
            padding: 5rem 1rem 3rem;
            box-sizing: border-box;
        }
        /* Side panel retired: the office photo is now the full-page background */
        .auth-left { display: none; }
        .auth-left-content { padding: 4rem 4rem 6rem 4rem; position: relative; z-index: 2; }
        .auth-logo { display: flex; align-items: center; justify-content: center; gap: 0.6rem; text-decoration: none; margin-bottom: 1.5rem; }
        .auth-logo-img { width: 44px; height: 44px; object-fit: contain; }
        .auth-logo-name { font-family: var(--font-heading); font-size: 1.5rem; font-weight: 800; color: #ffffff; display: block; line-height: 1; text-shadow: 0 2px 8px rgba(0,0,0,0.4); }
        .auth-logo-sub { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.15em; color: #e2e8f0; text-transform: uppercase; }
        .auth-title { font-family: var(--font-heading); font-size: 3rem; font-weight: 800; line-height: 1.1; color: #ffffff; margin-bottom: 1.5rem; text-shadow: 0 2px 14px rgba(0,0,0,0.45); }
        .auth-subtitle { color: #e2e8f0; font-size: 1.05rem; line-height: 1.6; max-width: 420px; margin-bottom: 2.5rem; text-shadow: 0 1px 8px rgba(0,0,0,0.4); }
        .auth-image { display: none; }

        .auth-right { width: 100%; max-width: 720px; margin: 0; background: transparent; display: flex; flex-direction: column; align-items: center; position: static; padding: 0; }
        .auth-back-link { position: fixed; top: 1.5rem; right: 1.5rem; display: inline-flex; align-items: center; gap: 0.5rem; color: #e2e8f0; text-decoration: none; font-weight: 700; font-size: 0.88rem; transition: color 0.2s; z-index: 10; text-shadow: 0 1px 6px rgba(0,0,0,0.4); }
        .auth-back-link:hover { color: #ffffff; }
        .auth-form-card { background: #fff; width: 100%; max-width: 720px; border-radius: 20px; padding: 3rem 2.5rem; box-shadow: 0 25px 60px rgba(0,0,0,0.35); border: 1px solid #e5e7eb; position: relative; z-index: 2; margin-top: 0; }
        .auth-card-head { margin-bottom: 2rem; }
        .auth-form-title { text-align: center; font-family: system-ui, sans-serif; font-size: 1.6rem; font-weight: 700; color: #0f172a; margin-bottom: 0.5rem; }
        .auth-form-sub { text-align: center; color: var(--text-muted); font-size: 0.9rem; margin-bottom: 2rem; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem 1.25rem; margin-bottom: 1.5rem; }
        .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem 1.25rem; margin-bottom: 1.5rem; }
        .form-full { grid-column: 1 / -1; }

        .field-hint { font-size: 0.78rem; color: #64748b; margin-top: 0.4rem; line-height: 1.5; }

        /* ── Multi-step wizard ──
           Steps are stacked by default; the .js-wizard class (added by script)
           switches to one-step-at-a-time so the form still works without JS. */
        .wizard-steps { display: none; align-items: flex-start; margin-bottom: 2.25rem; }
        .js-wizard .wizard-steps { display: flex; }
        .wizard-step { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 0.45rem; position: relative; }
        .wizard-step:not(:first-child)::before {
            content: ''; position: absolute; top: 17px; right: 50%; width: 100%; height: 3px;
            background: #e2e8f0; z-index: 0;
        }
        .wizard-step.done::before, .wizard-step.active::before { background: var(--primary); }
        .wizard-step-num {
            width: 36px; height: 36px; border-radius: 50%;
            background: #e2e8f0; color: #64748b;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.9rem; position: relative; z-index: 1;
            transition: background 0.2s, color 0.2s;
        }
        .wizard-step.active .wizard-step-num { background: var(--primary); color: #fff; box-shadow: 0 0 0 4px rgba(220,38,38,0.15); }
        .wizard-step.done .wizard-step-num { background: var(--primary); color: #fff; }
        .wizard-step-label { font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-align: center; }
        .wizard-step.active .wizard-step-label, .wizard-step.done .wizard-step-label { color: var(--primary); }

        .js-wizard .form-step { display: none; }
        .js-wizard .form-step.active { display: block; animation: stepIn 0.25s ease; }
        .js-wizard .form-step .form-section-title { margin-top: 0; }
        @keyframes stepIn { from { opacity: 0; transform: translateX(12px); } to { opacity: 1; transform: none; } }

        .wizard-nav { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 2rem; }
        .btn-wizard {
            display: none; align-items: center; gap: 0.5rem;
            padding: 0.85rem 1.75rem; border-radius: 8px;
            font-weight: 700; font-size: 1rem; cursor: pointer;
            border: 1px solid #cbd5e1; background: #fff; color: var(--text-dark);
        }
        .js-wizard .btn-wizard { display: inline-flex; }
        .btn-wizard:hover { border-color: var(--primary); color: var(--primary); }
        .btn-wizard-next {
            background: var(--primary); color: #fff; border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(220,38,38,0.25); margin-left: auto;
        }
        .btn-wizard-next:hover { background: var(--primary-dark); color: #fff; }
        .btn-auth-primary { margin-top: 0; margin-left: auto; width: auto; }
        
        .auth-input-group label { display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.5rem; }
        .auth-input { width: 100%; padding: 0.8rem 1rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: var(--text-dark); transition: border-color 0.2s; box-sizing: border-box;}
        .auth-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
        select.auth-input { background: #fff url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2364748b' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E") no-repeat right 1rem center; -webkit-appearance: none; -moz-appearance: none; appearance: none; padding-right: 2.5rem; }

        .form-section-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 2rem 0 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #f1f5f9; display: flex; align-items: center; gap: 0.5rem; }
        
        .btn-auth-primary { width: 100%; background: var(--primary); color: #fff; padding: 1rem; border-radius: 8px; font-weight: 700; font-size: 1.05rem; border: none; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 0.5rem; box-shadow: 0 4px 12px rgba(220,38,38,0.25); transition: background 0.2s, transform 0.2s; margin-top: 2rem; }
        .btn-auth-primary:hover { background: var(--primary-dark); transform: translateY(-1px); }
        .auth-login-link { text-align: center; margin-top: 1.5rem; font-size: 0.9rem; color: var(--text-muted); }
        .auth-login-link a { color: var(--primary); font-weight: 700; text-decoration: none; }
        .auth-login-link a:hover { text-decoration: underline; }

        @media (max-width: 640px) {
            .auth-layout { padding: 4.5rem 1rem 2.5rem; }
            .auth-form-card { padding: 2rem 1.5rem; }
            .form-grid, .form-grid-3 { grid-template-columns: 1fr; }
            .wizard-step-label { font-size: 0.7rem; }
            .btn-wizard { padding: 0.8rem 1.25rem; font-size: 0.92rem; }
        }
    </style>
</head>
<body>
<div class="auth-layout">
    <div class="auth-left">
        <div class="auth-left-content">
            <a href="{{ route('home') }}" class="auth-logo">
                <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI" class="auth-logo-img">
                <div>
                    <span class="auth-logo-name">BCTVI</span>
                    <span class="auth-logo-sub">Bantayan</span>
                </div>
            </a>
            
            <h1 class="auth-title">
                Create Your<br>
                <span class="text-primary" style="color: #dc2626;">Account</span>
            </h1>
            <p class="auth-subtitle">
                Join BCTVI Bantayan today. Book your Fiber WiFi installation and manage your subscription easily from our client portal.
            </p>
        </div>
        
        <div class="auth-image">
            <img src="{{ asset('assets/images/bg-office.jpg') }}" alt="Office">
        </div>
    </div>
    
    <div class="auth-right">
        <a href="{{ route('home') }}" class="auth-back-link">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Back to Home
        </a>

        <a href="{{ route('home') }}" class="auth-logo">
            <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI" class="auth-logo-img">
            <div>
                <span class="auth-logo-name">BCTVI</span>
                <span class="auth-logo-sub">Bantayan</span>
            </div>
        </a>

        <div class="auth-form-card">
            <div class="auth-card-head">
            <h2 class="auth-form-title">Client Registration</h2>
            <p class="auth-form-sub" id="stepSubtitle">Step 1 of 3 &mdash; Tell us a little about yourself.</p>
            </div>

            <div class="wizard-steps">
                <div class="wizard-step" data-step-indicator="1">
                    <div class="wizard-step-num">1</div>
                    <div class="wizard-step-label">Personal</div>
                </div>
                <div class="wizard-step" data-step-indicator="2">
                    <div class="wizard-step-num">2</div>
                    <div class="wizard-step-label">Contact &amp; Address</div>
                </div>
                <div class="wizard-step" data-step-indicator="3">
                    <div class="wizard-step-num">3</div>
                    <div class="wizard-step-label">Account</div>
                </div>
            </div>

            <div id="googleBlock">
            <a href="{{ route('google.redirect') }}" style="display: flex; justify-content: center; align-items: center; gap: 0.5rem; width: 100%; background: #fff; color: var(--text-dark); padding: 0.85rem; border-radius: 8px; font-weight: 700; font-size: 1rem; border: 1px solid #cbd5e1; text-decoration: none; box-sizing: border-box; margin-bottom: 1.25rem;">
                <svg width="18" height="18" viewBox="0 0 48 48"><path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303C33.654 32.657 29.223 36 24 36c-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z"/><path fill="#FF3D00" d="m6.306 14.691 6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z"/><path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238A11.91 11.91 0 0 1 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z"/><path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303a12.04 12.04 0 0 1-4.087 5.571l.003-.002 6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z"/></svg>
                Sign up with Google
            </a>
            <div style="display: flex; align-items: center; text-align: center; color: #94a3b8; font-size: 0.85rem; margin-bottom: 1.5rem;">
                <span style="flex: 1; border-bottom: 1px solid #e2e8f0;"></span>
                <span style="padding: 0 0.75rem;">or fill in the form below</span>
                <span style="flex: 1; border-bottom: 1px solid #e2e8f0;"></span>
            </div>
            </div>

            @if ($errors->any())
                <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 1rem; border-radius: 8px; font-size: 0.85rem; margin-bottom: 1.5rem;">
                    <ul style="margin: 0; padding-left: 1.2rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST" enctype="multipart/form-data" id="registerForm">
                @csrf

                <!-- Step 1: Personal Information -->
                <div class="form-step" data-step="1">
                    <div class="form-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        Personal Information
                    </div>

                    <div class="form-grid-3">
                        <div class="auth-input-group">
                            <label>First Name *</label>
                            <input type="text" name="firstname" class="auth-input" value="{{ old('firstname') }}" required>
                        </div>
                        <div class="auth-input-group">
                            <label>Middle Name</label>
                            <input type="text" name="middlename" class="auth-input" value="{{ old('middlename') }}">
                        </div>
                        <div class="auth-input-group">
                            <label>Last Name *</label>
                            <input type="text" name="lastname" class="auth-input" value="{{ old('lastname') }}" required>
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="auth-input-group">
                            <label>Birth Date *</label>
                            <input type="date" name="birthdate" id="birthdate" class="auth-input" value="{{ old('birthdate') }}" required>
                        </div>
                        <div class="auth-input-group">
                            <label>Age *</label>
                            <input type="number" name="age" id="age" class="auth-input" value="{{ old('age') }}" readonly style="background:#f1f5f9; cursor:not-allowed;">
                        </div>
                        <div class="auth-input-group">
                            <label>Gender *</label>
                            <select name="gender" class="auth-input" required>
                                <option value="">Select Gender</option>
                                <option value="Male" {{ old('gender') == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender') == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="auth-input-group">
                            <label>Place of Birth</label>
                            <input type="text" name="place_of_birth" class="auth-input" value="{{ old('place_of_birth') }}">
                        </div>
                        <div class="auth-input-group">
                            <label>Civil Status *</label>
                            <select name="civil_status" class="auth-input" required>
                                <option value="">Select Status</option>
                                <option value="Single" {{ old('civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
                                <option value="Married" {{ old('civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
                                <option value="Widowed" {{ old('civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                <option value="Legally Separated" {{ old('civil_status') == 'Legally Separated' ? 'selected' : '' }}>Legally Separated</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Contact & Address -->
                <div class="form-step" data-step="2">
                    <div class="form-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        Contact &amp; Address
                    </div>

                    <div class="form-grid">
                        <div class="auth-input-group">
                            <label>Mobile Number *</label>
                            <input type="text" name="contact_no" class="auth-input" placeholder="e.g. 09123456789" value="{{ old('contact_no') }}" required>
                        </div>
                        <div class="auth-input-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" class="auth-input" value="{{ old('email') }}" required>
                        </div>
                    </div>

                    <div class="form-grid-3">
                        <div class="auth-input-group">
                            <label>Province *</label>
                            <input type="text" name="address_province" class="auth-input" value="Cebu" readonly style="background:#f1f5f9;">
                        </div>
                        <div class="auth-input-group">
                            <label>Municipality *</label>
                            <select name="address_municipality" id="municipality" class="auth-input" required>
                                <option value="">Select Municipality</option>
                                <option value="Bantayan" {{ old('address_municipality', 'Bantayan') == 'Bantayan' ? 'selected' : '' }}>Bantayan</option>
                                <option value="Santa Fe" {{ old('address_municipality') == 'Santa Fe' ? 'selected' : '' }}>Santa Fe</option>
                                <option value="Madridejos" {{ old('address_municipality') == 'Madridejos' ? 'selected' : '' }}>Madridejos</option>
                            </select>
                        </div>
                        <div class="auth-input-group">
                            <label>Barangay *</label>
                            <select name="address_barangay" id="barangay" class="auth-input" required data-old="{{ old('address_barangay') }}">
                                <option value="">Select Brgy</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Account & Verification -->
                <div class="form-step" data-step="3">
                    <div class="form-section-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Account &amp; Verification
                    </div>

                    <div class="form-grid">
                        <div class="auth-input-group">
                            <label>Username *</label>
                            <input type="text" name="username" class="auth-input" value="{{ old('username') }}" required>
                        </div>
                        <div class="auth-input-group">
                            <label>Profile Photo (Optional)</label>
                            <input type="file" name="profile_photo" class="auth-input" accept="image/png, image/jpeg, image/gif, image/webp" style="padding: 0.6rem;">
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="auth-input-group">
                            <label>Password *</label>
                            <input type="password" name="password" class="auth-input" minlength="8" required>
                        </div>
                        <div class="auth-input-group">
                            <label>Confirm Password *</label>
                            <input type="password" name="password_confirmation" class="auth-input" minlength="8" required>
                        </div>
                    </div>

                    <div class="auth-input-group">
                        <label>Proof of Billing (Photo) *</label>
                        <input type="file" name="proof_of_billing" class="auth-input" accept="image/png, image/jpeg, image/webp" style="padding: 0.6rem;" required>
                        <div class="field-hint">
                            Attach a clear photo of a recent billing statement (electric or water bill) showing your
                            name and address. This is required to verify that your account details are valid.
                        </div>
                    </div>
                </div>

                <div class="wizard-nav">
                    <button type="button" class="btn-wizard" id="btnBack">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                        Back
                    </button>
                    <button type="button" class="btn-wizard btn-wizard-next" id="btnNext">
                        Next
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </button>
                    <button type="submit" class="btn-auth-primary" id="btnSubmit">
                        Create Account
                    </button>
                </div>

                <div class="auth-login-link">
                    Already have an account? <a href="{{ route('login') }}">Sign In Here</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Barangays per municipality on Bantayan Island
    var BARANGAYS = {
        'Bantayan': ['Atop-atop','Baigad','Bantigue','Baod','Binaobao','Botigues','Doong','Guiwanon','Hilotongan','Kabac','Kabangbang','Kampingganon','Kangkaibe','Lipayran','Luyongbaybay','Mojon','Obo-ob','Patao','Puting Bato','Sillion','Suba','Sulangan','Sungko','Ticad'],
        'Santa Fe': ['Balidbid','Hagdan','Hilantagaan','Kinatarkan','Langub','Maricaban','Okoy','Poblacion','Pooc','Talisay'],
        'Madridejos': ['Bunakan','Kangwayan','Kaongkod','Kodia','Maalat','Malbago','Mancilang','Pili','Poblacion','San Agustin','Tabagak','Talangnan','Tarong','Tugas']
    };

    var municipalitySelect = document.getElementById('municipality');
    var barangaySelect = document.getElementById('barangay');

    function populateBarangays() {
        var list = BARANGAYS[municipalitySelect.value] || [];
        var oldValue = barangaySelect.getAttribute('data-old') || '';
        barangaySelect.innerHTML = '<option value="">Select Brgy</option>';
        list.forEach(function(brgy) {
            var opt = document.createElement('option');
            opt.value = brgy;
            opt.textContent = brgy;
            if (brgy === oldValue) opt.selected = true;
            barangaySelect.appendChild(opt);
        });
    }

    municipalitySelect.addEventListener('change', function() {
        barangaySelect.setAttribute('data-old', '');
        populateBarangays();
    });
    populateBarangays();

    document.getElementById('birthdate').addEventListener('change', function() {
        var dob = new Date(this.value);
        if(!isNaN(dob)) {
            var today = new Date();
            var age = today.getFullYear() - dob.getFullYear();
            var m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age--;
            }
            document.getElementById('age').value = age;
        }
    });

    // ── Step-by-step registration ──
    (function () {
        var card = document.querySelector('.auth-form-card');
        var form = document.getElementById('registerForm');
        var steps = [].slice.call(document.querySelectorAll('.form-step'));
        var markers = [].slice.call(document.querySelectorAll('.wizard-step'));
        var btnBack = document.getElementById('btnBack');
        var btnNext = document.getElementById('btnNext');
        var btnSubmit = document.getElementById('btnSubmit');
        var googleBlock = document.getElementById('googleBlock');
        var subtitle = document.getElementById('stepSubtitle');
        var total = steps.length;
        var current = 1;

        var SUBTITLES = {
            1: 'Step 1 of 3 — Tell us a little about yourself.',
            2: 'Step 2 of 3 — Where should we install your connection?',
            3: 'Step 3 of 3 — Set up your login and verify your account.'
        };

        // Take over validation so hidden steps never block submit with an unfocusable field
        card.classList.add('js-wizard');
        form.setAttribute('novalidate', 'novalidate');

        function showStep(n) {
            current = n;
            steps.forEach(function (panel) {
                panel.classList.toggle('active', Number(panel.getAttribute('data-step')) === n);
            });
            markers.forEach(function (marker) {
                var i = Number(marker.getAttribute('data-step-indicator'));
                marker.classList.toggle('active', i === n);
                marker.classList.toggle('done', i < n);
            });
            btnBack.style.display = (n === 1) ? 'none' : '';
            btnNext.style.display = (n === total) ? 'none' : '';
            btnSubmit.style.display = (n === total) ? '' : 'none';
            googleBlock.style.display = (n === 1) ? '' : 'none';
            if (subtitle) subtitle.textContent = SUBTITLES[n];
        }

        function firstInvalidIn(n) {
            var controls = [].slice.call(steps[n - 1].querySelectorAll('input, select, textarea'));
            for (var i = 0; i < controls.length; i++) {
                if (!controls[i].checkValidity()) return controls[i];
            }
            return null;
        }

        function validateStep(n) {
            var invalid = firstInvalidIn(n);
            if (!invalid) return true;
            if (current !== n) showStep(n);
            invalid.reportValidity();
            return false;
        }

        btnNext.addEventListener('click', function () {
            if (validateStep(current)) showStep(Math.min(current + 1, total));
        });

        btnBack.addEventListener('click', function () {
            showStep(Math.max(current - 1, 1));
        });

        form.addEventListener('submit', function (e) {
            for (var n = 1; n <= total; n++) {
                if (!validateStep(n)) {
                    e.preventDefault();
                    return;
                }
            }
        });

        // Let the markers jump back freely, but forward only through completed steps
        markers.forEach(function (marker) {
            marker.style.cursor = 'pointer';
            marker.addEventListener('click', function () {
                var target = Number(marker.getAttribute('data-step-indicator'));
                if (target === current) return;
                if (target < current) { showStep(target); return; }
                for (var n = current; n < target; n++) {
                    if (!validateStep(n)) return;
                }
                showStep(target);
            });
        });

        showStep({{ $initialStep }});
    })();
</script>
</body>
</html>

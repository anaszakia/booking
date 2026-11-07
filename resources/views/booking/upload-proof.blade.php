<!DOCTYPE html>
<html lang="id" style="--venue-primary: {{ $primaryVenue?->primary_color ?? '#245c43' }}; --venue-accent: {{ $primaryVenue?->accent_color ?? '#d7f36a' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b110e">
    @if ($primaryVenue?->logo_path)
        <link rel="icon" href="{{ minio_url($primaryVenue->logo_path) }}">
        <link rel="apple-touch-icon" href="{{ minio_url($primaryVenue->logo_path) }}">
    @else
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon/favicon-32x32.png') }}">
    @endif
    <title>Upload Bukti Bayar — {{ $primaryVenue?->name ?? 'Rally Padel' }}</title>
    <script>
        (() => {
            const savedTheme = localStorage.getItem('rally-theme');
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.dataset.theme = ['light', 'dark'].includes(savedTheme) ? savedTheme : systemTheme;
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { color-scheme:dark; --page:#0b110e; --surface:#121b16; --raised:#17231b; --text:#f0f3ed; --muted:#91a096; --line:rgba(235,243,234,.11); --brand:var(--venue-primary); --accent:var(--venue-accent); --accent-ink:#172019; --shadow:0 20px 60px rgba(0,0,0,.24); }
        [data-theme="light"] { color-scheme:light; --page:#f5f7f2; --surface:#fff; --raised:#f0f4eb; --text:#17231d; --muted:#748078; --line:rgba(25,49,32,.12); --brand:var(--venue-primary); --accent:var(--venue-accent); --accent-ink:#172019; --shadow:0 16px 48px rgba(27,52,34,.08); }
        * { box-sizing:border-box; }
        body { min-width:320px; min-height:100vh; margin:0; background:radial-gradient(ellipse at 75% 0,color-mix(in srgb,var(--venue-primary) 16%,transparent),transparent 30%),var(--page); color:var(--text); font:14px "DM Sans",sans-serif; -webkit-font-smoothing:antialiased; }
        a { color:inherit; text-decoration:none; }
        button,input { font:inherit; }
        .shell { width:min(1040px,calc(100% - 48px)); margin:0 auto; }
        .site-header { position:sticky; top:10px; z-index:5; display:flex; align-items:center; justify-content:space-between; height:68px; margin:10px auto 0; padding:0 20px; border:1px solid var(--line); border-radius:15px; background:color-mix(in srgb,var(--page) 82%,transparent); backdrop-filter:blur(18px); }
        .brand { display:inline-flex; align-items:center; gap:9px; font:800 22px Manrope,sans-serif; letter-spacing:-1.3px; }
        .brand-logo,.brand-mark { width:30px; height:30px; flex:none; border-radius:9px; object-fit:cover; }
        .brand-mark { display:grid; place-items:center; background:var(--brand); color:#fff; }
        .brand-mark svg { width:18px; }
        .header-actions { display:flex; align-items:center; gap:16px; }
        .back-link { color:var(--muted); font-size:11px; font-weight:700; }
        .back-link:hover { color:var(--brand); }
        .theme-toggle { width:36px; height:36px; display:grid; place-items:center; border:1px solid var(--line); border-radius:50%; background:var(--surface); color:var(--text); cursor:pointer; }
        .theme-toggle svg { width:17px; height:17px; }
        .theme-toggle .sun-icon,[data-theme="light"] .theme-toggle .moon-icon { display:none; }
        [data-theme="light"] .theme-toggle .sun-icon { display:block; }
        .main { width:min(720px,calc(100% - 48px)); padding:58px 0 76px; }
        .eyebrow { margin:0 0 12px; color:var(--accent); font-size:10px; font-weight:700; letter-spacing:1.7px; text-transform:uppercase; }
        h1,h2,p { margin-top:0; }
        h1 { margin-bottom:10px; font:800 clamp(34px,5vw,50px)/1.08 Manrope,sans-serif; letter-spacing:-2.5px; }
        .intro-copy { max-width:580px; margin:0; color:var(--muted); font-size:13px; line-height:1.75; }
        .panel { margin-top:25px; padding:clamp(21px,5vw,34px); border:1px solid var(--line); border-radius:17px; background:var(--surface); box-shadow:var(--shadow); }
        .panel-heading { display:flex; align-items:center; gap:12px; margin-bottom:24px; }
        .upload-icon { width:42px; height:42px; display:grid; place-items:center; flex:none; border-radius:12px; background:color-mix(in srgb,var(--accent) 15%,transparent); color:var(--accent); }
        .upload-icon svg { width:21px; }
        .panel-heading h2 { margin:0 0 4px; font:700 15px Manrope,sans-serif; }
        .panel-heading p { margin:0; color:var(--muted); font-size:11px; }
        .alert { display:flex; align-items:flex-start; gap:10px; margin-bottom:18px; padding:13px 14px; border:1px solid rgba(226,184,92,.2); border-radius:10px; background:rgba(226,184,92,.08); color:var(--text); font-size:12px; line-height:1.6; }
        .alert.success { border-color:rgba(128,199,123,.22); background:rgba(128,199,123,.09); }
        .alert.error { border-color:rgba(223,132,110,.22); background:rgba(223,132,110,.08); }
        .field { margin-top:19px; }
        .field label { display:flex; align-items:center; justify-content:space-between; gap:10px; margin-bottom:8px; font-size:11px; font-weight:700; }
        .field-hint { color:var(--muted); font-size:10px; font-weight:500; }
        .text-input { width:100%; min-height:47px; padding:12px 14px; border:1px solid var(--line); border-radius:10px; outline:0; background:var(--raised); color:var(--text); font-size:13px; transition:border-color .2s,box-shadow .2s; }
        .text-input::placeholder { color:var(--muted); opacity:.75; }
        .text-input:focus { border-color:var(--accent); box-shadow:0 0 0 3px color-mix(in srgb,var(--accent) 15%,transparent); }
        .file-input { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; clip-path:inset(50%); }
        .file-picker { min-height:104px; display:flex; align-items:center; justify-content:center; gap:13px; padding:17px; border:1px dashed color-mix(in srgb,var(--muted) 45%,transparent); border-radius:11px; background:var(--raised); cursor:pointer; text-align:left; transition:border-color .2s,background .2s; }
        .file-picker:hover,.file-picker:focus-within { border-color:var(--accent); background:color-mix(in srgb,var(--raised) 86%,var(--accent)); }
        .file-icon { width:38px; height:38px; display:grid; place-items:center; flex:none; border:1px solid var(--line); border-radius:10px; background:var(--surface); color:var(--accent); }
        .file-icon svg { width:18px; }
        .file-copy { min-width:0; }
        .file-copy strong { display:block; margin-bottom:5px; font-size:11px; }
        .file-copy span { display:block; overflow:hidden; color:var(--muted); font-size:10px; text-overflow:ellipsis; white-space:nowrap; }
        .field-error { margin-top:7px; color:#e58d7b; font-size:11px; }
        .submit-button { width:100%; min-height:48px; display:inline-flex; align-items:center; justify-content:center; gap:9px; margin-top:24px; padding:13px 17px; border:0; border-radius:9px; background:var(--brand); color:#fff; font-size:12px; font-weight:800; cursor:pointer; transition:transform .2s,filter .2s; }
        .submit-button:hover { transform:translateY(-2px); filter:brightness(1.05); }
        .submit-button:disabled { opacity:.65; cursor:wait; transform:none; }
        .submit-button svg { width:16px; }
        .privacy-note { display:flex; align-items:center; justify-content:center; gap:7px; margin:15px 0 0; color:var(--muted); font-size:10px; }
        .privacy-note svg { width:14px; color:var(--accent); }
        .footer { margin-top:22px; color:var(--muted); font-size:11px; text-align:center; }
        .footer a { color:var(--text); font-weight:700; }
        @media (max-width:520px) { .shell { width:calc(100% - 24px); } .site-header { top:6px; height:60px; margin-top:6px; padding:0 13px; } .brand { font-size:18px; } .header-actions { gap:11px; } .main { width:calc(100% - 32px); padding:42px 0 50px; } h1 { letter-spacing:-1.8px; } .panel { margin-top:21px; } .field label { align-items:flex-start; flex-direction:column; gap:4px; } .file-picker { justify-content:flex-start; } }
        @media (prefers-reduced-motion:reduce) { *,*::before,*::after { scroll-behavior:auto!important; transition-duration:.01ms!important; } }
    </style>
</head>
<body>
    <header class="site-header shell">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ $primaryVenue?->name ?? 'Rally Padel' }}, beranda">
            @if ($primaryVenue?->logo_path)
                <img class="brand-logo" src="{{ minio_url($primaryVenue->logo_path) }}" alt="">
            @else
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M5 19 18.5 5M8 5l11 11M5 8l11 11" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg></span>
            @endif
            {{ $primaryVenue?->name ?? 'Rally' }}<span style="color:var(--accent)">.</span>
        </a>
        <div class="header-actions">
            <a class="back-link" href="{{ route('home') }}">← Beranda</a>
            <button class="theme-toggle" id="theme-toggle" type="button" aria-label="Ganti tema" title="Ganti tema">
                <svg class="sun-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.7"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <svg class="moon-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.5 8.5 0 1 0 20.2 15Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </div>
    </header>

    <main class="shell main">
        <section>
            <p class="eyebrow">Langkah terakhir</p>
            <h1>Kirim bukti pembayaran.</h1>
            <p class="intro-copy">Masukkan nomor booking, lalu unggah bukti transfer atau pembayaran QRIS. Admin akan memeriksa pembayaran sebelum booking dikonfirmasi.</p>
        </section>

        <section class="panel">
            <div class="panel-heading">
                <span class="upload-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M12 16V4m0 0L7 9m5-5 5 5M5 14v5h14v-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <div><h2>Detail pembayaran</h2><p>Format gambar JPG, PNG, atau WebP · Maksimal 8 MB</p></div>
            </div>

            @if (session('success'))
                <div class="alert success" role="status"><span>✓</span><span>{{ session('success') }}</span></div>
            @endif
            @if ($errors->any())
                <div class="alert error" role="alert"><span>!</span><span>{{ $errors->first() }}</span></div>
            @endif

            <form action="{{ route('booking.upload-proof') }}" method="POST" enctype="multipart/form-data" id="proof-form">
                @csrf
                <div class="field">
                    <label for="booking_number">Nomor booking <span class="field-hint">Contoh: BOOK-041126-0001</span></label>
                    <input class="text-input" id="booking_number" name="booking_number" type="text"
                        value="{{ old('booking_number', request('booking_number')) }}"
                        placeholder="BOOK-DDMMYY-NNNN" minlength="16" maxlength="16" inputmode="text"
                        autocapitalize="characters" autocomplete="off" spellcheck="false" required>
                    @error('booking_number') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <label for="payment_proof">Bukti pembayaran <span class="field-hint">Pastikan nominal dan status transaksi terlihat jelas</span></label>
                    <label class="file-picker" for="payment_proof">
                        <span class="file-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><rect x="3.5" y="4.5" width="17" height="15" rx="2.5" stroke="currentColor" stroke-width="1.6"/><circle cx="9" cy="10" r="1.5" stroke="currentColor" stroke-width="1.5"/><path d="m5 17 5-4 3 2 3-3 3 3" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                        <span class="file-copy"><strong>Pilih gambar bukti pembayaran</strong><span id="file-name">JPG, PNG, atau WebP · Maksimal 8 MB</span></span>
                    </label>
                    <input class="file-input" id="payment_proof" name="payment_proof" type="file" accept="image/jpeg,image/png,image/webp" required>
                    @error('payment_proof') <div class="field-error">{{ $message }}</div> @enderror
                </div>
                <button class="submit-button" id="submit-button" type="submit">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 16V4m0 0L7 9m5-5 5 5M5 14v5h14v-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Kirim bukti pembayaran
                </button>
                <p class="privacy-note"><svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M8 10V7a4 4 0 0 1 8 0v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>Bukti pembayaran hanya dapat dilihat oleh admin.</p>
            </form>
        </section>
        <p class="footer">Belum menyelesaikan pembayaran? <a href="{{ route('home') }}">Kembali ke beranda</a></p>
    </main>
    <script>
        const themeToggle = document.getElementById('theme-toggle');
        themeToggle.addEventListener('click', () => {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = nextTheme;
            localStorage.setItem('rally-theme', nextTheme);
        });

        const proofInput = document.getElementById('payment_proof');
        proofInput.addEventListener('change', () => {
            document.getElementById('file-name').textContent = proofInput.files[0]?.name || 'JPG, PNG, atau WebP · Maksimal 8 MB';
        });
        document.getElementById('proof-form').addEventListener('submit', () => {
            const submitButton = document.getElementById('submit-button');
            submitButton.disabled = true;
            submitButton.textContent = 'Mengirim bukti pembayaran…';
        });
    </script>
</body>
</html>

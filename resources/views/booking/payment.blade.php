<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b110e">
    <title>Pembayaran {{ $booking->booking_number }} — {{ $booking->venue->name }}</title>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        :root { color-scheme: dark; --page:#0b110e; --surface:#121b16; --raised:#17231b; --text:#f0f3ed; --muted:#91a096; --line:rgba(235,243,234,.11); --accent:#d7f878; --accent-ink:#172019; --shadow:0 20px 60px rgba(0,0,0,.24); }
        [data-theme="light"] { color-scheme:light; --page:#f5f7f2; --surface:#fff; --raised:#f0f4eb; --text:#17231d; --muted:#748078; --line:rgba(25,49,32,.12); --accent:#245c43; --accent-ink:#fff; --shadow:0 16px 48px rgba(27,52,34,.08); }
        * { box-sizing:border-box; }
        body { min-width:320px; min-height:100vh; margin:0; background:radial-gradient(ellipse at 75% 0,rgba(86,119,63,.12),transparent 30%),var(--page); color:var(--text); font:14px "DM Sans",sans-serif; -webkit-font-smoothing:antialiased; }
        a { color:inherit; text-decoration:none; }
        button { font:inherit; }
        .shell { width:min(1040px,calc(100% - 48px)); margin:0 auto; }
        .site-header { position:sticky; top:10px; z-index:5; display:flex; align-items:center; justify-content:space-between; height:68px; margin:10px auto 0; padding:0 20px; border:1px solid var(--line); border-radius:15px; background:color-mix(in srgb,var(--page) 82%,transparent); backdrop-filter:blur(18px); }
        .brand { display:inline-flex; align-items:center; gap:9px; font:800 22px Manrope,sans-serif; letter-spacing:-1.3px; }
        .brand-logo,.brand-mark { width:30px; height:30px; flex:none; border-radius:9px; object-fit:cover; }
        .brand-mark { display:grid; place-items:center; background:var(--accent); color:var(--accent-ink); }
        .brand-mark svg { width:18px; }
        .header-actions { display:flex; align-items:center; gap:16px; }
        .back-link { color:var(--muted); font-size:11px; font-weight:700; }
        .back-link:hover { color:var(--accent); }
        .theme-toggle { width:36px; height:36px; display:grid; place-items:center; border:1px solid var(--line); border-radius:50%; background:var(--surface); color:var(--text); cursor:pointer; }
        .theme-toggle svg { width:17px; height:17px; }
        .theme-toggle .sun-icon,[data-theme="light"] .theme-toggle .moon-icon { display:none; }
        [data-theme="light"] .theme-toggle .sun-icon { display:block; }
        .main { padding:56px 0 72px; }
        .countdown-banner { display:flex; align-items:center; justify-content:space-between; gap:18px; margin-bottom:30px; padding:16px 20px; border:1px solid rgba(226,184,92,.24); border-radius:14px; background:linear-gradient(110deg,rgba(226,184,92,.12),rgba(226,184,92,.04)); }
        .countdown-copy { display:flex; align-items:center; gap:13px; min-width:0; }
        .countdown-icon { width:40px; height:40px; display:grid; place-items:center; flex:none; border-radius:11px; background:rgba(226,184,92,.13); color:#e4b85c; }
        .countdown-icon svg { width:21px; height:21px; }
        .countdown-label { display:block; margin-bottom:4px; color:var(--muted); font-size:10px; font-weight:700; letter-spacing:.8px; text-transform:uppercase; }
        .countdown-hint { margin:0; color:var(--text); font-size:12px; }
        .countdown-time { flex:none; color:#e4b85c; font:800 clamp(24px,4vw,32px)/1 Manrope,monospace; letter-spacing:1px; font-variant-numeric:tabular-nums; }
        .countdown-banner.is-urgent { border-color:rgba(223,132,110,.3); background:linear-gradient(110deg,rgba(223,132,110,.13),rgba(223,132,110,.04)); }
        .countdown-banner.is-urgent .countdown-icon { background:rgba(223,132,110,.13); color:#df846e; }
        .countdown-banner.is-urgent .countdown-time { color:#df846e; }
        .intro { max-width:700px; margin-bottom:25px; }
        .eyebrow { margin:0 0 12px; color:var(--accent); font-size:10px; font-weight:700; letter-spacing:1.7px; text-transform:uppercase; }
        h1,h2,p { margin-top:0; }
        h1 { margin-bottom:10px; font:800 clamp(33px,5vw,50px)/1.08 Manrope,sans-serif; letter-spacing:-2.5px; }
        .intro-copy { max-width:600px; margin:0; color:var(--muted); font-size:13px; line-height:1.75; }
        .layout { display:grid; grid-template-columns:minmax(0,1.35fr) minmax(290px,.85fr); gap:18px; align-items:start; }
        .panel { overflow:hidden; border:1px solid var(--line); border-radius:17px; background:var(--surface); box-shadow:var(--shadow); }
        .booking-panel { padding:clamp(20px,4vw,30px); }
        .booking-top { display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:20px; }
        .booking-top h2,.panel-title { margin:0; font:700 16px Manrope,sans-serif; letter-spacing:-.3px; }
        .status-pill { display:inline-flex; align-items:center; gap:7px; padding:8px 11px; border:1px solid var(--line); border-radius:99px; color:var(--muted); font-size:10px; font-weight:700; white-space:nowrap; }
        .status-pill::before { width:7px; height:7px; border-radius:50%; background:#e4b85c; content:""; }
        .status-pill.good::before { background:#80c77b; }
        .status-pill.bad::before { background:#df846e; }
        .number-box { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px; border:1px solid var(--line); border-radius:13px; background:var(--raised); }
        .number-label,.detail-label { display:block; margin-bottom:6px; color:var(--muted); font-size:10px; font-weight:700; letter-spacing:.6px; text-transform:uppercase; }
        .booking-number { font:800 clamp(16px,3vw,20px) Manrope,monospace; letter-spacing:.2px; }
        .copy-button { flex:none; padding:9px 11px; border:1px solid var(--line); border-radius:8px; background:var(--surface); color:var(--text); font-size:10px; font-weight:700; cursor:pointer; transition:border-color .2s,transform .2s; }
        .copy-button:hover { border-color:var(--accent); transform:translateY(-1px); }
        .details { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:10px; margin-top:12px; }
        .detail { min-width:0; padding:14px; border:1px solid var(--line); border-radius:11px; background:color-mix(in srgb,var(--surface) 72%,var(--raised)); }
        .detail-value { overflow-wrap:anywhere; font-size:12px; font-weight:700; line-height:1.5; }
        .amount { color:var(--accent); font:800 19px Manrope,sans-serif; }
        .pay-panel { padding:24px; }
        .pay-panel h2 { margin:0 0 8px; font:700 17px Manrope,sans-serif; }
        .muted { color:var(--muted); line-height:1.65; }
        .deadline { display:flex; gap:11px; margin:17px 0 22px; padding:13px; border:1px solid rgba(226,184,92,.2); border-radius:11px; background:rgba(226,184,92,.08); color:var(--text); font-size:11px; line-height:1.6; }
        .deadline svg { width:17px; height:17px; flex:none; color:#e4b85c; }
        .methods { display:grid; gap:10px; margin-top:16px; }
        .method { min-width:0; padding:14px; border:1px solid var(--line); border-radius:11px; background:var(--raised); }
        .method-heading { display:flex; align-items:center; gap:9px; margin-bottom:8px; font-size:12px; font-weight:700; }
        .method-icon { width:28px; height:28px; display:grid; place-items:center; border-radius:8px; background:color-mix(in srgb,var(--accent) 15%,transparent); color:var(--accent); font-size:11px; font-weight:800; }
        .method img { display:block; width:min(100%,210px); max-height:230px; margin:14px auto 2px; border-radius:8px; background:#fff; object-fit:contain; }
        .account-number-row { display:flex; align-items:center; justify-content:space-between; gap:10px; margin:7px 0 3px; }
        .account-number { min-width:0; overflow-wrap:anywhere; font:700 17px Manrope,sans-serif; letter-spacing:.3px; }
        .copy-account-button { width:34px; height:34px; display:grid; place-items:center; flex:none; border:1px solid var(--line); border-radius:8px; background:var(--surface); color:var(--muted); cursor:pointer; transition:border-color .2s,color .2s,background .2s; }
        .copy-account-button:hover,.copy-account-button:focus-visible { border-color:var(--accent); color:var(--accent); outline:0; }
        .copy-account-button svg { width:16px; height:16px; }
        .copy-account-button.is-copied { border-color:rgba(128,199,123,.35); background:rgba(128,199,123,.1); color:#80c77b; }
        .account-name { margin:0; color:var(--muted); font-size:11px; }
        .primary-link { width:100%; min-height:47px; display:inline-flex; align-items:center; justify-content:center; gap:9px; margin-top:18px; padding:12px 16px; border-radius:9px; background:var(--accent); color:var(--accent-ink); font-size:12px; font-weight:800; transition:transform .2s,filter .2s; }
        .primary-link:hover { transform:translateY(-2px); filter:brightness(1.05); }
        .primary-link svg { width:16px; }
        .state-message { display:flex; gap:12px; align-items:flex-start; margin-top:18px; padding:15px; border:1px solid var(--line); border-radius:12px; background:var(--raised); font-size:12px; line-height:1.7; }
        .state-icon { width:28px; height:28px; display:grid; place-items:center; flex:none; border-radius:50%; background:rgba(128,199,123,.14); color:#80c77b; font-weight:800; }
        .state-message.warning .state-icon { background:rgba(226,184,92,.13); color:#e4b85c; }
        .state-message.error .state-icon { background:rgba(223,132,110,.14); color:#df846e; }
        .state-message p { margin:2px 0 0; color:var(--muted); }
        .success-alert { margin-bottom:15px; padding:12px 14px; border:1px solid rgba(128,199,123,.22); border-radius:10px; background:rgba(128,199,123,.09); color:var(--text); font-size:12px; }
        .footer { margin-top:23px; color:var(--muted); font-size:11px; text-align:center; }
        .footer a { color:var(--text); font-weight:700; }
        @media (max-width:760px) { .layout { grid-template-columns:1fr; } .pay-panel { order:2; } }
        @media (max-width:520px) { .shell { width:calc(100% - 24px); } .site-header { top:6px; height:60px; margin-top:6px; padding:0 13px; } .brand { font-size:18px; } .header-actions { gap:11px; } .main { padding:28px 0 48px; } .countdown-banner { gap:10px; margin-bottom:23px; padding:13px; } .countdown-copy { gap:9px; } .countdown-icon { width:34px; height:34px; } .countdown-label { font-size:9px; } .countdown-hint { font-size:10px; } .countdown-time { font-size:24px; } h1 { letter-spacing:-1.8px; } .details { grid-template-columns:1fr 1fr; gap:8px; } .detail { padding:11px; } .booking-top { align-items:flex-start; flex-direction:column; gap:10px; } .number-box { align-items:flex-start; flex-direction:column; } .copy-button { width:100%; } .pay-panel { padding:20px; } }
        @media (prefers-reduced-motion:reduce) { *,*::before,*::after { scroll-behavior:auto!important; transition-duration:.01ms!important; } }
    </style>
</head>
<body>
    <header class="site-header shell">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ $booking->venue->name }}, beranda">
            @if ($booking->venue->logo_path)
                <img class="brand-logo" src="{{ minio_url($booking->venue->logo_path) }}" alt="">
            @else
                <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M5 19 18.5 5M8 5l11 11M5 8l11 11" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg></span>
            @endif
            {{ $booking->venue->name }}<span style="color:var(--accent)">.</span>
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
        @if ($booking->status === 'pending_payment')
            <section class="countdown-banner" id="payment-countdown" data-expires-at="{{ $booking->payment_expires_at->timestamp * 1000 }}" aria-live="polite">
                <div class="countdown-copy">
                    <span class="countdown-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                    <div>
                        <span class="countdown-label">Sisa waktu pembayaran</span>
                        <p class="countdown-hint">Selesaikan pembayaran sebelum slot dilepas</p>
                    </div>
                </div>
                <output class="countdown-time" id="countdown-time" aria-label="Sisa waktu">--:--</output>
            </section>
        @endif

        <section class="intro">
            <p class="eyebrow">Pembayaran booking</p>
            <h1>Selangkah lagi ke lapangan.</h1>
            <p class="intro-copy">Simpan nomor booking dan selesaikan pembayaran dengan metode pilihanmu. Setelah itu, unggah bukti pembayaran untuk konfirmasi.</p>
        </section>

        @if (session('success')) <div class="success-alert" role="status">{{ session('success') }}</div> @endif

        <div class="layout">
            <section class="panel booking-panel" aria-label="Ringkasan booking">
                <div class="booking-top">
                    <h2>Ringkasan booking</h2>
                    @if ($booking->status === 'pending_payment')
                        <span class="status-pill">Menunggu pembayaran</span>
                    @elseif ($booking->status === 'pending_verification')
                        <span class="status-pill">Menunggu verifikasi</span>
                    @elseif ($booking->status === 'confirmed')
                        <span class="status-pill good">Terkonfirmasi</span>
                    @elseif ($booking->status === 'rejected')
                        <span class="status-pill bad">Pembayaran ditolak</span>
                    @else
                        <span class="status-pill bad">Kedaluwarsa</span>
                    @endif
                </div>
                <div class="number-box">
                    <div>
                        <span class="number-label">Nomor booking</span>
                        <span class="booking-number" id="booking-number">{{ $booking->booking_number }}</span>
                    </div>
                    <button type="button" class="copy-button" id="copy-booking-number">Salin nomor</button>
                </div>
                <div class="details">
                    <div class="detail"><span class="detail-label">Lapangan</span><div class="detail-value">{{ $booking->court->name }}</div></div>
                    <div class="detail"><span class="detail-label">Jadwal</span><div class="detail-value">{{ $booking->booking_date->locale('id')->translatedFormat('D, d M Y') }} · {{ substr($booking->booking_time, 0, 5) }}</div></div>
                    <div class="detail"><span class="detail-label">Nama pemesan</span><div class="detail-value">{{ $booking->customer_name }}</div></div>
                    <div class="detail"><span class="detail-label">Total pembayaran</span><div class="amount">Rp{{ number_format($booking->amount, 0, ',', '.') }}</div></div>
                </div>
            </section>

            <aside class="panel pay-panel" aria-label="Instruksi pembayaran">
                @if ($booking->status === 'pending_payment')
                    <h2>Pilih metode pembayaran</h2>
                    <p class="muted" style="margin-bottom:0;font-size:12px">Bayar sesuai total tagihan berikut.</p>
                    <div class="deadline">
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                        <span>Selesaikan sebelum <strong>{{ $booking->payment_expires_at->translatedFormat('H:i, d F Y') }}</strong>. Slot akan dilepas jika waktu habis.</span>
                    </div>
                    <div class="methods">
                        @if ($booking->venue->qris_path)
                            <div class="method">
                                <div class="method-heading"><span class="method-icon">QR</span>QRIS</div>
                                <img src="{{ minio_url($booking->venue->qris_path) }}" alt="QRIS {{ $booking->venue->name }}">
                            </div>
                        @endif
                        @foreach ($booking->venue->bankAccounts as $account)
                            <div class="method">
                                <div class="method-heading"><span class="method-icon">↗</span>Transfer {{ $account->bank_name }}</div>
                                <span class="detail-label">Nomor rekening</span>
                                <span class="account-number-row">
                                    <strong class="account-number">{{ $account->account_number }}</strong>
                                    <button class="copy-account-button" type="button" data-copy-account="{{ $account->account_number }}"
                                        aria-label="Salin nomor rekening {{ $account->bank_name }}" title="Salin nomor rekening">
                                        <svg class="copy-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2" stroke="currentColor" stroke-width="1.7"/></svg>
                                        <svg class="copied-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true" hidden><path d="m5 12 4 4L19 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </span>
                                <p class="account-name">a.n. {{ $account->account_name }}</p>
                            </div>
                        @endforeach
                    </div>
                    <a class="primary-link" href="{{ route('booking.upload-form', ['booking_number' => $booking->booking_number]) }}">
                        Upload bukti pembayaran
                        <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 16V4m0 0L7 9m5-5 5 5M5 14v5h14v-5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </a>
                @elseif ($booking->status === 'pending_verification')
                    <div class="state-message"><span class="state-icon">✓</span><div><strong>Bukti berhasil dikirim</strong><p>Bukti pembayaran sedang diperiksa admin. Slot tetap ditahan selama proses verifikasi.</p></div></div>
                @elseif ($booking->status === 'confirmed')
                    <div class="state-message"><span class="state-icon">✓</span><div><strong>Pembayaran terverifikasi</strong><p>Booking kamu sudah dikonfirmasi. Sampai jumpa di lapangan!</p></div></div>
                @elseif ($booking->status === 'expired')
                    <div class="state-message warning"><span class="state-icon">!</span><div><strong>Waktu pembayaran habis</strong><p>Slot sudah dilepas. Silakan buat booking baru untuk memilih jadwal lain.</p></div></div>
                @else
                    <div class="state-message error"><span class="state-icon">!</span><div><strong>Pembayaran ditolak</strong><p>Bukti pembayaran ditolak dan slot telah tersedia kembali. Silakan buat booking baru atau hubungi venue.</p></div></div>
                @endif
            </aside>
        </div>
        <p class="footer">Butuh bantuan? <a href="{{ route('home') }}">Kembali ke beranda</a></p>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
    <script>
        const themeToggle = document.getElementById('theme-toggle');
        themeToggle.addEventListener('click', () => {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            document.documentElement.dataset.theme = nextTheme;
            localStorage.setItem('rally-theme', nextTheme);
        });

        const countdownBanner = document.getElementById('payment-countdown');
        if (countdownBanner) {
            const countdownOutput = document.getElementById('countdown-time');
            const expiresAt = Number(countdownBanner.dataset.expiresAt);
            let expiryReloadStarted = false;

            const updateCountdown = () => {
                const remainingSeconds = Math.max(0, Math.ceil((expiresAt - Date.now()) / 1000));
                const minutes = Math.floor(remainingSeconds / 60);
                const seconds = remainingSeconds % 60;
                countdownOutput.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                countdownBanner.classList.toggle('is-urgent', remainingSeconds <= 60);

                if (remainingSeconds === 0 && !expiryReloadStarted) {
                    expiryReloadStarted = true;
                    countdownOutput.textContent = '00:00';
                    countdownBanner.querySelector('.countdown-hint').textContent = 'Waktu habis. Memperbarui status booking…';
                    window.setTimeout(() => window.location.reload(), 1200);
                }
            };

            updateCountdown();
            window.setInterval(updateCountdown, 1000);
        }

        async function copyToClipboard(value) {
            if (navigator.clipboard?.writeText) {
                try {
                    await navigator.clipboard.writeText(value);
                    return;
                } catch (error) {
                    console.warn('Clipboard API tidak tersedia, mencoba metode alternatif.', error);
                }
            }

            const input = document.createElement('textarea');
            input.value = value;
            input.setAttribute('readonly', '');
            input.style.position = 'fixed';
            input.style.opacity = '0';
            document.body.appendChild(input);
            input.select();
            const copied = document.execCommand('copy');
            input.remove();

            if (!copied) {
                throw new Error('Browser menolak akses ke clipboard.');
            }
        }

        function showCopyAlert(label) {
            if (window.Swal) {
                return Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: `${label} berhasil disalin`,
                    showConfirmButton: false,
                    timer: 2400,
                    timerProgressBar: true,
                    background: getComputedStyle(document.documentElement).getPropertyValue('--surface').trim(),
                    color: getComputedStyle(document.documentElement).getPropertyValue('--text').trim(),
                });
            }

            window.alert(`${label} berhasil disalin`);
        }

        function showCopyError() {
            if (window.Swal) {
                return Swal.fire({
                    icon: 'error',
                    title: 'Gagal menyalin',
                    text: 'Browser tidak mengizinkan akses clipboard. Silakan salin nomor secara manual.',
                    confirmButtonColor: getComputedStyle(document.documentElement).getPropertyValue('--accent').trim(),
                });
            }

            window.alert('Gagal menyalin. Silakan salin nomor secara manual.');
        }

        document.getElementById('copy-booking-number').addEventListener('click', async (event) => {
            try {
                await copyToClipboard(document.getElementById('booking-number').textContent.trim());
                showCopyAlert('Nomor booking');
            } catch (error) {
                console.error('Nomor booking tidak dapat disalin.', error);
                showCopyError();
            }
        });

        document.querySelectorAll('[data-copy-account]').forEach((button) => {
            button.addEventListener('click', async () => {
                try {
                    await copyToClipboard(button.dataset.copyAccount);
                    button.classList.add('is-copied');
                    button.querySelector('.copy-icon').hidden = true;
                    button.querySelector('.copied-icon').hidden = false;
                    button.title = 'Nomor rekening tersalin';
                    button.setAttribute('aria-label', 'Nomor rekening tersalin');
                    showCopyAlert('Nomor rekening');
                } catch (error) {
                    console.error('Nomor rekening tidak dapat disalin.', error);
                    button.title = 'Gagal menyalin. Silakan salin nomor secara manual.';
                    button.setAttribute('aria-label', 'Gagal menyalin nomor rekening. Salin secara manual.');
                    showCopyError();
                }
            });
        });
    </script>
</body>
</html>

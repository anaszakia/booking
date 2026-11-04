<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b110e">
    <title>Lihat Jadwal — Rally Padel</title>
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
        :root {
            color-scheme: dark;
            --page: #0b110e;
            --surface: #121b16;
            --surface-raised: #17231b;
            --text: #f0f3ed;
            --muted: #91a096;
            --line: rgba(235,243,234,.11);
            --accent: #d7f878;
            --accent-ink: #172019;
            --available: #c9ef63;
            --booked: #e29278;
            --shadow: 0 20px 60px rgba(0,0,0,.24);
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body {
            min-width: 320px;
            margin: 0;
            background:
                radial-gradient(ellipse at 75% 0, rgba(86,119,63,.12), transparent 28%),
                var(--page);
            color: var(--text);
            font-family: "DM Sans", sans-serif;
            -webkit-font-smoothing: antialiased;
            transition: background .25s, color .25s;
        }
        a { color: inherit; text-decoration: none; }
        button, select { font: inherit; }
        .shell { width: min(1180px, calc(100% - 48px)); margin: 0 auto; }
        .site-header {
            position: sticky;
            top: 10px;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: min(1180px, calc(100% - 48px));
            height: 68px;
            margin: 10px auto 0;
            padding: 0 20px;
            border: 1px solid var(--line);
            border-radius: 15px;
            background: color-mix(in srgb, var(--page) 82%, transparent);
            backdrop-filter: blur(18px);
        }
        .brand { display: inline-flex; align-items: center; gap: 9px; font: 800 23px "Manrope", sans-serif; letter-spacing: -1.6px; }
        .brand-mark {
            width: 30px;
            height: 30px;
            display: grid;
            place-items: center;
            border-radius: 10px;
            background: var(--accent);
            color: var(--accent-ink);
        }
        .brand-mark svg { width: 18px; }
        .header-actions { display: flex; align-items: center; gap: 17px; }
        .home-link, .admin-link { color: var(--muted); font-size: 11px; font-weight: 700; transition: color .2s; }
        .home-link:hover, .admin-link:hover { color: var(--accent); }
        .admin-link { display: inline-flex; align-items: center; gap: 7px; color: var(--text); }
        .admin-link svg { width: 15px; color: var(--accent); }
        .theme-toggle {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border: 1px solid var(--line);
            border-radius: 50%;
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
            transition: transform .2s, background .2s;
        }
        .theme-toggle:hover { transform: rotate(-12deg); background: var(--surface-raised); }
        .theme-toggle svg { width: 17px; height: 17px; }
        .theme-toggle .sun-icon { display: none; }
        [data-theme="light"] {
            color-scheme: light;
            --page: #f5f7f2;
            --surface: #fff;
            --surface-raised: #f0f4eb;
            --text: #17231d;
            --muted: #748078;
            --line: rgba(25,49,32,.12);
            --accent: #245c43;
            --accent-ink: #fff;
            --available: #386e38;
            --booked: #bf684f;
            --shadow: 0 16px 48px rgba(27,52,34,.08);
        }
        [data-theme="light"] .theme-toggle .sun-icon { display: block; }
        [data-theme="light"] .theme-toggle .moon-icon { display: none; }
        .page-heading { display: flex; justify-content: space-between; align-items: end; gap: 24px; padding: 62px 0 26px; }
        .eyebrow { margin-bottom: 12px; color: var(--accent); font-size: 10px; font-weight: 700; letter-spacing: 1.65px; text-transform: uppercase; }
        h1, h2, h3, p { margin-top: 0; }
        h1 { margin-bottom: 10px; font: 800 clamp(34px, 5vw, 52px)/1.08 "Manrope", sans-serif; letter-spacing: -2.7px; }
        .page-heading p:last-child { margin-bottom: 0; color: var(--muted); font-size: 13px; line-height: 1.65; }
        .demo-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 9px 12px;
            border: 1px solid var(--line);
            border-radius: 99px;
            background: var(--surface);
            color: var(--muted);
            font-size: 10px;
            white-space: nowrap;
        }
        .demo-label::before { width: 7px; height: 7px; border-radius: 50%; background: #e7af52; content: ""; }
        .calendar-layout { display: grid; grid-template-columns: minmax(0, 1.65fr) minmax(280px, .75fr); gap: 18px; align-items: start; padding-bottom: 62px; }
        .panel {
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 17px;
            background: var(--surface);
            box-shadow: var(--shadow);
        }
        .calendar-panel { padding: 22px; }
        .calendar-toolbar { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-bottom: 20px; }
        .month-title { margin: 0; font: 700 20px "Manrope", sans-serif; letter-spacing: -.6px; }
        .calendar-actions { display: flex; align-items: center; gap: 8px; }
        .calendar-button, .today-button {
            min-width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            border: 1px solid var(--line);
            border-radius: 9px;
            background: var(--surface);
            color: var(--text);
            cursor: pointer;
            transition: border-color .2s, background .2s, transform .2s;
        }
        .calendar-button:hover, .today-button:hover { border-color: var(--accent); background: var(--surface-raised); }
        .calendar-button:active { transform: scale(.95); }
        .calendar-button svg { width: 15px; }
        .today-button { padding: 0 12px; color: var(--muted); font-size: 10px; font-weight: 700; }
        .filters-row { display: flex; justify-content: space-between; align-items: center; gap: 14px; margin-bottom: 17px; }
        .venue-select {
            min-width: 185px;
            padding: 10px 34px 10px 12px;
            border: 1px solid var(--line);
            border-radius: 9px;
            outline: 0;
            background: var(--surface);
            color: var(--text);
            font-size: 11px;
            font-weight: 600;
        }
        .venue-select option { background: var(--surface); color: var(--text); }
        .legend { display: flex; flex-wrap: wrap; gap: 13px; color: var(--muted); font-size: 9px; }
        .legend-item { display: inline-flex; align-items: center; gap: 6px; }
        .legend-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--available); }
        .legend-dot.booked { background: var(--booked); }
        .weekday-grid, .days-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); }
        .weekday-grid { padding-bottom: 8px; border-bottom: 1px solid var(--line); }
        .weekday { padding: 9px 2px; color: var(--muted); font-size: 9px; font-weight: 700; text-align: center; text-transform: uppercase; }
        .day-cell {
            position: relative;
            min-width: 0;
            min-height: 91px;
            padding: 8px 7px;
            border: 0;
            border-right: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            background: transparent;
            color: var(--text);
            text-align: left;
            cursor: pointer;
            transition: background .18s, box-shadow .18s;
        }
        .day-cell:nth-child(7n) { border-right: 0; }
        .day-cell:hover:not(:disabled) { background: color-mix(in srgb, var(--accent) 8%, transparent); }
        .day-cell.selected { z-index: 1; background: color-mix(in srgb, var(--accent) 10%, transparent); box-shadow: inset 0 0 0 1px var(--accent); }
        .day-cell.outside { color: var(--muted); opacity: .43; }
        .day-cell:disabled { cursor: default; }
        .day-number { display: inline-grid; width: 25px; height: 25px; place-items: center; border-radius: 50%; font-size: 11px; font-weight: 600; }
        .day-cell.today .day-number { background: var(--accent); color: var(--accent-ink); }
        .day-cell.selected .day-number { color: var(--accent); font-weight: 800; }
        .day-cell.today.selected .day-number { background: var(--accent); color: var(--accent-ink); }
        .day-count { display: block; margin-top: 7px; color: var(--available); font-size: 8px; font-weight: 700; line-height: 1.3; }
        .day-dots { display: flex; gap: 3px; margin-top: 6px; }
        .day-dots span { width: 5px; height: 5px; border-radius: 50%; background: var(--available); }
        .day-dots span.booked { background: var(--booked); }
        .mobile-count { display: none; }
        .calendar-footnote { margin: 14px 0 0; color: var(--muted); font-size: 9px; line-height: 1.6; }
        .schedule-panel { position: sticky; top: 94px; }
        .schedule-header { padding: 20px 20px 16px; border-bottom: 1px solid var(--line); }
        .schedule-header-top { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin-bottom: 6px; }
        .schedule-header h2 { margin: 0; font: 700 17px "Manrope", sans-serif; letter-spacing: -.4px; }
        .slot-total { color: var(--accent); font-size: 10px; font-weight: 700; }
        .selected-date-label { margin: 0; color: var(--muted); font-size: 10px; }
        .slot-list { display: flex; flex-direction: column; gap: 9px; max-height: 540px; overflow: auto; padding: 13px; }
        .slot-card {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 12px;
            border: 1px solid var(--line);
            border-radius: 11px;
            background: var(--surface);
            animation: slot-in .32s ease both;
        }
        .slot-info { min-width: 0; }
        .slot-time { display: block; margin-bottom: 5px; font: 700 13px "Manrope", sans-serif; }
        .slot-venue { display: block; overflow: hidden; color: var(--muted); font-size: 9px; text-overflow: ellipsis; white-space: nowrap; }
        .slot-price { display: block; margin-top: 5px; color: var(--text); font-size: 9px; font-weight: 700; }
        .slot-right { display: flex; flex: 0 0 auto; flex-direction: column; align-items: flex-end; gap: 8px; }
        .slot-status { display: inline-flex; align-items: center; gap: 5px; color: var(--available); font-size: 8px; font-weight: 700; }
        .slot-status::before { width: 6px; height: 6px; border-radius: 50%; background: currentColor; content: ""; }
        .slot-status.booked { color: var(--booked); }
        .book-slot {
            padding: 7px 9px;
            border: 0;
            border-radius: 7px;
            background: var(--accent);
            color: var(--accent-ink);
            font-size: 9px;
            font-weight: 700;
            cursor: pointer;
            transition: transform .18s, filter .18s;
        }
        .book-slot:hover { transform: translateY(-1px); filter: brightness(1.08); }
        .book-slot:disabled { background: var(--surface-raised); color: var(--muted); cursor: not-allowed; }
        .book-slot.disabled { background: var(--surface-raised); color: var(--muted); cursor: not-allowed; pointer-events: none; }
        .empty-slots { padding: 34px 20px; color: var(--muted); font-size: 11px; line-height: 1.6; text-align: center; }
        .summary-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 13px; padding: 0 0 62px; }
        .summary-card { padding: 17px; border: 1px solid var(--line); border-radius: 13px; background: var(--surface); }
        .summary-value { display: block; margin-bottom: 6px; color: var(--accent); font: 800 21px "Manrope", sans-serif; }
        .summary-label { color: var(--muted); font-size: 9px; }
        footer { padding: 18px 0 24px; border-top: 1px solid var(--line); color: var(--muted); font-size: 10px; }
        .footer-row { display: flex; justify-content: space-between; gap: 14px; }
        .footer-row a { color: var(--text); }
        @keyframes slot-in { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 900px) {
            .calendar-layout { grid-template-columns: minmax(0, 1.35fr) minmax(260px, .8fr); gap: 12px; }
            .calendar-panel { padding: 16px; }
            .day-cell { min-height: 82px; padding: 6px 5px; }
            .day-count { font-size: 7px; }
        }
        @media (max-width: 700px) {
            .shell, .site-header { width: calc(100% - 28px); }
            .site-header { top: 6px; height: 58px; margin-top: 6px; padding: 0 13px; }
            .header-actions { gap: 10px; }
            .page-heading { align-items: flex-start; flex-direction: column; padding: 42px 0 20px; }
            .page-heading h1 { font-size: 38px; }
            .calendar-layout { grid-template-columns: 1fr; }
            .calendar-panel { padding: 13px; }
            .schedule-panel { position: static; }
            .day-cell { min-height: 68px; padding: 5px 3px; text-align: center; }
            .day-number { width: 23px; height: 23px; font-size: 10px; }
            .day-count { display: none; }
            .day-dots { justify-content: center; margin-top: 5px; }
            .mobile-count { display: inline; }
            .slot-list { max-height: none; }
            .summary-row { padding-bottom: 42px; }
        }
        @media (max-width: 430px) {
            .header-actions { gap: 8px; }
            .home-link { display: none; }
            .calendar-toolbar { gap: 7px; }
            .month-title { font-size: 17px; }
            .calendar-actions { gap: 5px; }
            .calendar-button { min-width: 31px; height: 32px; }
            .today-button { height: 32px; padding: 0 8px; font-size: 9px; }
            .filters-row { align-items: flex-start; flex-direction: column; }
            .venue-select { width: 100%; }
            .weekday { font-size: 8px; }
            .day-cell { min-height: 59px; }
            .day-dots span { width: 4px; height: 4px; }
            .summary-row { grid-template-columns: 1fr; gap: 8px; }
            .summary-card { display: flex; justify-content: space-between; align-items: center; padding: 12px 14px; }
            .summary-value { margin: 0; font-size: 17px; }
            .footer-row { flex-direction: column; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after { scroll-behavior: auto !important; animation-duration: .01ms !important; transition-duration: .01ms !important; }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <a class="brand" href="{{ route('home') }}" aria-label="Rally, beranda">
            <span class="brand-mark" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none"><path d="M5 19 18.5 5M8 5l11 11M5 8l11 11" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg></span>
            rally<span style="color:var(--accent)">.</span>
        </a>
        <div class="header-actions">
            <a class="home-link" href="{{ route('home') }}">← Beranda</a>
            <button class="theme-toggle" id="theme-toggle" type="button" aria-label="Aktifkan mode terang" title="Aktifkan mode terang">
                <svg class="sun-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.7"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <svg class="moon-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.5 8.5 0 1 0 20.2 15Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <a class="admin-link" href="{{ route('login') }}" aria-label="Masuk sebagai admin">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 19 6v5c0 4.7-3 8-7 10-4-2-7-5.3-7-10V6l7-3Z" stroke="currentColor" stroke-width="1.7"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Masuk Admin
            </a>
        </div>
    </header>

    <main class="shell">
        <section class="page-heading">
            <div>
                <p class="eyebrow">Jadwal lapangan</p>
                <h1>Rencanakan game<br>berikutnya.</h1>
                <p>Lihat ketersediaan lapangan dari kalender, lalu pilih slot yang cocok untuk timmu.</p>
            </div>
            <span class="demo-label">Jadwal tersedia · contoh ketersediaan</span>
        </section>

        <section class="calendar-layout" aria-label="Kalender jadwal padel">
            <div class="panel calendar-panel">
                <div class="calendar-toolbar">
                    <h2 class="month-title" id="month-title">November 2026</h2>
                    <div class="calendar-actions">
                        <button class="today-button" id="today-button" type="button">Hari ini</button>
                        <button class="calendar-button" id="previous-month" type="button" aria-label="Bulan sebelumnya"><svg viewBox="0 0 24 24" fill="none"><path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                        <button class="calendar-button" id="next-month" type="button" aria-label="Bulan berikutnya"><svg viewBox="0 0 24 24" fill="none"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
                    </div>
                </div>
                <div class="filters-row">
                    <label>
                        <span class="sr-only" style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0">Pilih lapangan</span>
                        <select class="venue-select" id="venue-filter" aria-label="Filter jadwal berdasarkan lapangan">
                            <option value="all">Semua lapangan</option>
                        </select>
                    </label>
                    <div class="legend" aria-label="Keterangan jadwal">
                        <span class="legend-item"><span class="legend-dot"></span>Tersedia</span>
                        <span class="legend-item"><span class="legend-dot booked"></span>Penuh</span>
                    </div>
                </div>
                <div class="weekday-grid" aria-hidden="true">
                    <span class="weekday">Sen</span><span class="weekday">Sel</span><span class="weekday">Rab</span><span class="weekday">Kam</span><span class="weekday">Jum</span><span class="weekday">Sab</span><span class="weekday">Min</span>
                </div>
                <div class="days-grid" id="days-grid" role="grid" aria-label="Hari dalam kalender"></div>
                <p class="calendar-footnote">Data lapangan, lokasi, dan harga berasal dari master. Status slot masih contoh dan belum terhubung ke booking server.</p>
            </div>

            <aside class="panel schedule-panel" aria-labelledby="selected-day-heading">
                <div class="schedule-header">
                    <div class="schedule-header-top">
                        <h2 id="selected-day-heading">Jadwal tersedia</h2>
                        <span class="slot-total" id="slot-total">0 slot</span>
                    </div>
                    <p class="selected-date-label" id="selected-date-label"></p>
                </div>
                <div class="slot-list" id="slot-list" aria-live="polite"></div>
            </aside>
        </section>

        <section class="summary-row" aria-label="Ringkasan lapangan">
            <div class="summary-card"><span class="summary-value">{{ $courts->count() }}</span><span class="summary-label">Lapangan aktif</span></div>
            <div class="summary-card"><span class="summary-value">06.00–22.00</span><span class="summary-label">Jam operasional contoh</span></div>
            <div class="summary-card"><span class="summary-value">7 hari</span><span class="summary-label">Kalender dapat dijelajahi</span></div>
        </section>
    </main>

    <footer>
        <div class="shell footer-row">
            <span>© {{ date('Y') }} Rally Padel · Jadwal contoh untuk pratinjau.</span>
            <a href="{{ route('home') }}">Kembali ke beranda ↗</a>
        </div>
    </footer>

    <script>
        const themeToggle = document.getElementById('theme-toggle');
        const venues = @json($scheduleCourts);
        const hourSlots = ['07:00', '09:00', '11:00', '13:00', '15:00', '17:00', '19:00', '21:00'];
        const bookingStorageKey = 'rally-booked-slots';
        const now = new Date();
        const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
        let visibleMonth = new Date(today.getFullYear(), today.getMonth(), 1);
        let selectedDate = new Date(today);
        let selectedVenue = 'all';
        const monthTitle = document.getElementById('month-title');
        const daysGrid = document.getElementById('days-grid');
        const slotList = document.getElementById('slot-list');
        const venueFilter = document.getElementById('venue-filter');
        const selectedDateLabel = document.getElementById('selected-date-label');
        const slotTotal = document.getElementById('slot-total');
        let savedBookings = readSavedBookings();

        function readSavedBookings() {
            const stored = localStorage.getItem(bookingStorageKey);
            if (!stored) return [];

            try {
                const parsed = JSON.parse(stored);
                const valid = Array.isArray(parsed) && parsed.every((booking) => (
                    booking
                    && (typeof booking.venueId === 'string' || typeof booking.venueId === 'number')
                    && /^\d{4}-\d{2}-\d{2}$/.test(booking.date)
                    && hourSlots.includes(booking.time)
                ));
                if (!valid) throw new Error('Data jadwal tersimpan memiliki format yang tidak valid.');
                return parsed;
            } catch (error) {
                console.error('Jadwal booking tersimpan tidak dapat dibaca.', error);
                return null;
            }
        }

        venues.forEach((venue) => {
            const option = document.createElement('option');
            option.value = venue.ulid;
            option.textContent = venue.name;
            venueFilter.append(option);
        });

        function dateKey(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }

        function formatPrice(price) {
            return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(price);
        }

        function makeSlots(date) {
            if (date < today) return [];
            const dayNumber = date.getDate();
            const dateString = dateKey(date);
            return venues
                .filter((venue) => selectedVenue === 'all' || selectedVenue === venue.ulid)
                .flatMap((venue) => hourSlots.map((time, slotIndex) => ({
                    venue,
                    time,
                    available: Boolean(savedBookings)
                        && (dayNumber + venues.indexOf(venue) * 3 + slotIndex * 2) % 7 !== 0
                        && (dayNumber + venues.indexOf(venue) + slotIndex) % 11 !== 0
                        && !savedBookings.some((booking) => (
                            String(booking.venueId) === venue.ulid
                            && booking.date === dateString
                            && booking.time === time
                        )),
                })))
                .sort((a, b) => a.time.localeCompare(b.time) || a.venue.name.localeCompare(b.venue.name));
        }

        function renderSlots() {
            const date = new Date(selectedDate);
            const slots = makeSlots(date);
            const availableCount = slots.filter((slot) => slot.available).length;
            selectedDateLabel.textContent = new Intl.DateTimeFormat('id-ID', { dateStyle: 'full' }).format(date);
            slotTotal.textContent = `${availableCount} slot tersedia`;

            if (!savedBookings) {
                slotList.innerHTML = '<div class="empty-slots">Data jadwal tidak dapat dibaca. Muat ulang halaman sebelum memilih slot.</div>';
                return;
            }

            if (!venues.length) {
                slotList.innerHTML = '<div class="empty-slots">Belum ada lapangan aktif di master lapangan.</div>';
                return;
            }

            if (!slots.length) {
                slotList.innerHTML = '<div class="empty-slots">Jadwal untuk tanggal yang sudah lewat tidak ditampilkan. Pilih tanggal hari ini atau berikutnya.</div>';
                return;
            }

            slotList.innerHTML = slots.map((slot, index) => {
                const status = slot.available ? 'Tersedia' : 'Penuh';
                const bookUrl = new URLSearchParams({
                    court: slot.venue.ulid,
                    venue: slot.venue.name,
                    date: dateKey(date),
                    slot: slot.time,
                    location: slot.venue.location,
                });
                return `
                    <article class="slot-card" style="animation-delay:${Math.min(index, 8) * 25}ms">
                        <div class="slot-info">
                            <span class="slot-time">${slot.time}</span>
                            <span class="slot-venue">${slot.venue.name} · ${slot.venue.area}</span>
                            <span class="slot-price">${formatPrice(slot.venue.price)} <span style="font-weight:400;color:var(--muted)">/ jam</span></span>
                        </div>
                        <div class="slot-right">
                            <span class="slot-status${slot.available ? '' : ' booked'}">${status}</span>
                            <a class="book-slot" ${slot.available ? `href="/?${bookUrl.toString()}#lapangan"` : 'aria-disabled="true" tabindex="-1"'}>Pilih</a>
                        </div>
                    </article>`;
            }).join('');

            slotList.querySelectorAll('.book-slot[aria-disabled="true"]').forEach((link) => {
                link.classList.add('disabled');
                link.removeAttribute('href');
            });
        }

        function renderCalendar() {
            const year = visibleMonth.getFullYear();
            const month = visibleMonth.getMonth();
            monthTitle.textContent = new Intl.DateTimeFormat('id-ID', { month: 'long', year: 'numeric' }).format(visibleMonth);
            const firstDay = new Date(year, month, 1);
            const gridStart = new Date(year, month, 1 - ((firstDay.getDay() + 6) % 7));
            const selectedKey = dateKey(selectedDate);
            const todayKey = dateKey(today);
            const cells = [];

            for (let index = 0; index < 42; index += 1) {
                const date = new Date(gridStart);
                date.setDate(gridStart.getDate() + index);
                const key = dateKey(date);
                const inMonth = date.getMonth() === month;
                const isPast = date < today;
                const isSelected = key === selectedKey;
                const isToday = key === todayKey;
                const venueCount = selectedVenue === 'all' ? venues.length : 1;
                const totalSlots = isPast ? 0 : venueCount * hourSlots.length;
                const availability = isPast ? 0 : makeSlots(date).filter((slot) => slot.available).length;
                const booked = totalSlots - availability;
                const classes = ['day-cell'];
                if (!inMonth) classes.push('outside');
                if (isPast) classes.push('past');
                if (isSelected) classes.push('selected');
                if (isToday) classes.push('today');
                const dots = isPast ? '' : `<span></span><span></span>${booked ? '<span class="booked"></span>' : ''}`;
                const dateLabel = new Intl.DateTimeFormat('id-ID', { dateStyle: 'full' }).format(date);

                cells.push(`
                    <button class="${classes.join(' ')}" type="button" role="gridcell" data-date="${key}" aria-label="${dateLabel}${isPast ? ', sudah lewat' : `, ${availability} slot tersedia`}" ${isPast ? 'disabled' : ''} ${isSelected ? 'aria-selected="true"' : ''}>
                        <span class="day-number">${date.getDate()}</span>
                        ${totalSlots ? `<span class="day-count">${availability}<span class="mobile-count"> slot</span> tersedia</span><span class="day-dots" aria-hidden="true">${dots}</span>` : ''}
                    </button>`);
            }

            daysGrid.innerHTML = cells.join('');
            daysGrid.querySelectorAll('.day-cell:not(:disabled)').forEach((cell) => {
                cell.addEventListener('click', () => {
                    const [cellYear, cellMonth, cellDay] = cell.dataset.date.split('-').map(Number);
                    selectedDate = new Date(cellYear, cellMonth - 1, cellDay);
                    renderCalendar();
                    renderSlots();
                });
            });
        }

        function changeMonth(amount) {
            visibleMonth = new Date(visibleMonth.getFullYear(), visibleMonth.getMonth() + amount, 1);
            renderCalendar();
        }

        document.getElementById('previous-month').addEventListener('click', () => changeMonth(-1));
        document.getElementById('next-month').addEventListener('click', () => changeMonth(1));
        document.getElementById('today-button').addEventListener('click', () => {
            visibleMonth = new Date(today.getFullYear(), today.getMonth(), 1);
            selectedDate = new Date(today);
            renderCalendar();
            renderSlots();
        });
        venueFilter.addEventListener('change', () => {
            selectedVenue = venueFilter.value;
            renderCalendar();
            renderSlots();
        });
        window.addEventListener('storage', (event) => {
            if (event.key === bookingStorageKey) {
                savedBookings = readSavedBookings();
                renderCalendar();
                renderSlots();
            }
        });

        function setTheme(theme, save = false) {
            document.documentElement.dataset.theme = theme;
            const isDark = theme === 'dark';
            themeToggle.setAttribute('aria-pressed', String(isDark));
            themeToggle.setAttribute('aria-label', `Aktifkan mode ${isDark ? 'terang' : 'gelap'}`);
            themeToggle.title = `Aktifkan mode ${isDark ? 'terang' : 'gelap'}`;
            document.querySelector('meta[name="theme-color"]').content = isDark ? '#0b110e' : '#f5f7f2';
            if (save) localStorage.setItem('rally-theme', theme);
        }

        setTheme(document.documentElement.dataset.theme);
        themeToggle.addEventListener('click', () => {
            setTheme(document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark', true);
        });
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
            if (!localStorage.getItem('rally-theme')) setTheme(event.matches ? 'dark' : 'light');
        });

        renderCalendar();
        renderSlots();
    </script>
</body>
</html>

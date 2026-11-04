<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0b110e">
    <title>{{ $primaryVenue?->name ?? 'Rally Padel' }} — Booking Lapangan Padel</title>
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        :root {
            color-scheme: light;
            --ink: #17231d;
            --muted: #78827b;
            --green: #245c43;
            --lime: #d7f36a;
            --paper: #f7f8f4;
            --line: #e7eae3;
        }
        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }
        body { margin: 0; background: var(--paper); color: var(--ink); font-family: "DM Sans", sans-serif; -webkit-font-smoothing: antialiased; }
        button, input, select { font: inherit; }
        a { color: inherit; text-decoration: none; }
        .shell { width: min(1180px, calc(100% - 48px)); margin: 0 auto; }
        .site-header { height: 82px; display: flex; align-items: center; justify-content: space-between; }
        .brand { display: inline-flex; gap: 10px; align-items: center; font: 800 24px "Manrope", sans-serif; letter-spacing: -1.5px; }
        .brand-logo { width: 31px; height: 31px; flex: 0 0 auto; border-radius: 9px; object-fit: cover; }
        .brand-mark { width: 31px; height: 31px; display: grid; place-items: center; border-radius: 10px; background: var(--green); color: var(--lime); }
        .brand-mark svg { width: 19px; }
        .nav-links { display: flex; align-items: center; gap: 36px; color: #68746c; font-size: 13px; font-weight: 600; }
        .nav-links a:hover { color: var(--green); }
        .header-actions { display: flex; align-items: center; gap: 22px; font-size: 13px; font-weight: 700; }
        .login-link { color: #38463d; }
        .button { display: inline-flex; justify-content: center; align-items: center; gap: 9px; border: 0; border-radius: 9px; padding: 13px 19px; background: var(--green); color: #fff; font-weight: 700; cursor: pointer; transition: background .2s, transform .2s; }
        .button:hover { background: #194a34; transform: translateY(-1px); }
        .button:disabled { opacity: .58; cursor: not-allowed; transform: none; }
        .button svg { width: 16px; }
        .hero { position: relative; min-height: 490px; overflow: hidden; display: flex; align-items: center; border-radius: 18px; background: #1c382c; color: white; }
        .hero::before { content: ""; position: absolute; inset: 0; background: linear-gradient(90deg, rgba(13, 30, 22, .9) 0%, rgba(13, 30, 22, .69) 43%, rgba(13, 30, 22, .12) 100%), url("https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=2000&q=85") center 52% / cover; }
        .hero-content { position: relative; z-index: 1; width: 100%; padding: 68px 7.3% 96px; }
        .eyebrow { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; color: var(--lime); font-size: 11px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; }
        .eyebrow-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--lime); box-shadow: 0 0 0 4px rgba(215, 243, 106, .18); }
        h1, h2, h3, p { margin-top: 0; }
        h1 { max-width: 580px; margin-bottom: 17px; font: 800 clamp(42px, 5vw, 64px)/1.07 "Manrope", sans-serif; letter-spacing: -3.4px; }
        .hero-copy { max-width: 420px; margin-bottom: 0; color: rgba(255,255,255,.77); font-size: 15px; line-height: 1.75; }
        .hero-note { position: absolute; right: 34px; bottom: 30px; z-index: 1; display: flex; align-items: center; gap: 10px; color: #fff; font-size: 11px; }
        .avatar-stack { display: flex; }
        .avatar-stack img { width: 28px; height: 28px; border: 2px solid #e8eee8; border-radius: 50%; object-fit: cover; margin-left: -8px; }
        .avatar-stack img:first-child { margin-left: 0; }
        .search-panel { position: relative; z-index: 2; display: grid; grid-template-columns: 1.25fr 1fr 1fr auto; gap: 0; align-items: center; width: calc(100% - 88px); margin: -38px auto 0; padding: 12px; border: 1px solid rgba(25, 45, 33, .06); border-radius: 13px; background: white; box-shadow: 0 14px 40px rgba(24, 42, 30, .1); }
        .search-field { display: flex; align-items: center; gap: 12px; min-width: 0; padding: 8px 19px; border-right: 1px solid var(--line); }
        .search-field svg { width: 19px; flex: 0 0 auto; color: #688271; }
        .field-copy { display: flex; flex-direction: column; gap: 5px; min-width: 0; }
        .field-copy label { color: #89918b; font-size: 10px; font-weight: 700; letter-spacing: .5px; text-transform: uppercase; }
        .field-copy input, .field-copy select { width: 100%; padding: 0; border: 0; outline: 0; background: transparent; color: var(--ink); font-size: 13px; font-weight: 700; }
        .field-copy select { cursor: pointer; }
        .search-button { min-height: 52px; margin-left: 10px; white-space: nowrap; }
        .section { padding: 86px 0 28px; }
        .section-heading { display: flex; justify-content: space-between; align-items: end; gap: 24px; margin-bottom: 27px; }
        .section-kicker { margin-bottom: 9px; color: #6d8b72; font-size: 10px; font-weight: 700; letter-spacing: 1.4px; text-transform: uppercase; }
        h2 { margin-bottom: 0; font: 800 clamp(27px, 3vw, 36px)/1.2 "Manrope", sans-serif; letter-spacing: -1.4px; }
        .section-subtitle { margin: 9px 0 0; color: var(--muted); font-size: 13px; }
        .text-link { display: inline-flex; align-items: center; gap: 7px; color: var(--green); font-size: 12px; font-weight: 700; white-space: nowrap; }
        .text-link svg { width: 15px; }
        .filters { display: flex; gap: 8px; margin-bottom: 21px; }
        .filter-chip { padding: 9px 15px; border: 1px solid #e4e9e1; border-radius: 30px; background: transparent; color: #68746c; font-size: 11px; font-weight: 700; cursor: pointer; }
        .filter-chip.active, .filter-chip:hover { border-color: var(--green); background: var(--green); color: white; }
        .venue-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 19px; }
        .venue-card { overflow: hidden; border: 1px solid #eaede7; border-radius: 13px; background: white; transition: transform .2s, box-shadow .2s; }
        .venue-card:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(24, 42, 30, .08); }
        .venue-card[hidden] { display: none; }
        .venue-image { position: relative; height: 190px; overflow: hidden; background: #dce5dc; }
        .venue-image img { width: 100%; height: 100%; object-fit: cover; transition: transform .4s; }
        .venue-card:hover .venue-image img { transform: scale(1.035); }
        .badge { position: absolute; top: 13px; left: 13px; padding: 7px 10px; border-radius: 30px; background: rgba(255,255,255,.94); color: #314238; font-size: 10px; font-weight: 700; }
        .favorite { position: absolute; top: 11px; right: 12px; width: 32px; height: 32px; display: grid; place-items: center; border: 0; border-radius: 50%; background: rgba(255,255,255,.94); color: #39483e; cursor: pointer; }
        .favorite svg { width: 16px; }
        .venue-details { padding: 17px 17px 15px; }
        .venue-title-row { display: flex; justify-content: space-between; align-items: start; gap: 8px; }
        h3 { margin-bottom: 6px; font: 700 15px "Manrope", sans-serif; letter-spacing: -.3px; }
        .rating { display: flex; align-items: center; gap: 4px; color: #25382c; font-size: 11px; font-weight: 700; }
        .rating svg { width: 13px; color: #e8aa45; }
        .location { display: flex; align-items: center; gap: 5px; color: #808981; font-size: 11px; }
        .location svg { width: 13px; }
        .venue-divider { height: 1px; margin: 15px 0 12px; background: #edf0eb; }
        .venue-bottom { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
        .price { color: #7b857d; font-size: 10px; }
        .price strong { color: var(--ink); font: 800 15px "Manrope", sans-serif; }
        .small-button { padding: 9px 12px; border-radius: 7px; font-size: 10px; }
        .empty-results { display: none; padding: 35px; border: 1px dashed #dce3da; border-radius: 12px; color: var(--muted); text-align: center; font-size: 13px; }
        .benefits { display: grid; grid-template-columns: repeat(3, 1fr); gap: 25px; padding: 54px 0 79px; }
        .benefit { display: flex; align-items: start; gap: 13px; }
        .benefit-icon { width: 39px; height: 39px; flex: 0 0 auto; display: grid; place-items: center; border-radius: 11px; background: #eaf0e7; color: var(--green); }
        .benefit-icon svg { width: 19px; }
        .benefit h3 { margin: 1px 0 5px; font-size: 12px; }
        .benefit p { margin: 0; color: #7b857d; font-size: 11px; line-height: 1.6; }
        .cta { display: flex; align-items: center; justify-content: space-between; gap: 24px; margin-bottom: 65px; padding: 32px 38px; border-radius: 14px; background: #eaf0e7; }
        .cta h2 { font-size: 24px; }
        .cta p { margin: 8px 0 0; color: #6e7c70; font-size: 12px; }
        footer { padding: 23px 0; border-top: 1px solid var(--line); }
        .footer-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; color: #89928b; font-size: 10px; }
        .footer-brand { font: 800 17px "Manrope", sans-serif; color: var(--ink); letter-spacing: -1px; }
        .toast { position: fixed; right: 24px; bottom: 24px; z-index: 5; max-width: calc(100% - 48px); padding: 14px 18px; border-radius: 9px; background: #172e22; color: white; box-shadow: 0 10px 30px rgba(0,0,0,.16); font-size: 12px; opacity: 0; transform: translateY(10px); pointer-events: none; transition: opacity .2s, transform .2s; }
        .toast.show { opacity: 1; transform: translateY(0); }
        @media (max-width: 820px) {
            .nav-links { gap: 18px; }
            .search-panel { grid-template-columns: 1fr 1fr; gap: 8px; width: calc(100% - 40px); margin-top: -28px; }
            .search-field { border-right: 0; }
            .search-button { margin: 0 8px; }
            .venue-grid { grid-template-columns: repeat(2, 1fr); }
            .benefits { gap: 18px; }
        }
        @media (max-width: 600px) {
            .shell { width: calc(100% - 32px); }
            .site-header { height: 70px; }
            .nav-links { display: none; }
            .brand { font-size: 22px; }
            .header-actions { gap: 13px; font-size: 11px; }
            .header-actions .button { padding: 10px 12px; }
            .hero { min-height: 440px; border-radius: 13px; }
            .hero::before { background-image: linear-gradient(90deg, rgba(13, 30, 22, .88), rgba(13, 30, 22, .34)), url("https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=1000&q=80"); }
            .hero-content { padding: 54px 25px 85px; }
            h1 { max-width: 380px; font-size: 46px; letter-spacing: -2.5px; }
            .hero-copy { max-width: 320px; font-size: 13px; }
            .hero-note { right: 20px; bottom: 18px; font-size: 9px; }
            .search-panel { grid-template-columns: 1fr 1fr; width: calc(100% - 24px); padding: 9px; }
            .search-field { gap: 9px; padding: 9px 7px; }
            .search-field svg { width: 17px; }
            .field-copy label { font-size: 9px; }
            .field-copy input, .field-copy select { font-size: 11px; }
            .search-button { grid-column: 1 / -1; min-height: 44px; margin: 3px 0 0; }
            .section { padding-top: 61px; }
            .section-heading { align-items: start; }
            .section-heading .text-link { display: none; }
            h2 { font-size: 27px; }
            .section-subtitle { max-width: 270px; font-size: 11px; line-height: 1.5; }
            .filters { overflow-x: auto; }
            .filter-chip { white-space: nowrap; }
            .venue-grid { grid-template-columns: 1fr; gap: 13px; }
            .venue-image { height: 205px; }
            .benefits { grid-template-columns: 1fr; gap: 22px; padding: 42px 0 54px; }
            .cta { align-items: start; flex-direction: column; margin-bottom: 45px; padding: 25px; }
            .cta h2 { font-size: 22px; }
            .footer-inner { flex-wrap: wrap; }
        }

        :root {
            --green: #164b36;
            --lime: #d9f76b;
            --paper: #f5f7f2;
        }
        body {
            background:
                radial-gradient(ellipse at 4% 30%, rgba(216, 236, 204, .28), transparent 28%),
                radial-gradient(ellipse at 96% 64%, rgba(221, 231, 211, .3), transparent 26%),
                var(--paper);
        }
        .site-header {
            position: sticky;
            top: 10px;
            z-index: 10;
            width: min(1180px, calc(100% - 48px));
            height: 68px;
            margin-top: 10px;
            padding: 0 20px;
            border: 1px solid rgba(255,255,255,.82);
            border-radius: 16px;
            background: rgba(255,255,255,.78);
            backdrop-filter: blur(18px);
            transition: box-shadow .25s, background .25s, border-color .25s;
        }
        .site-header.is-scrolled {
            border-color: rgba(225,232,223,.88);
            background: rgba(255,255,255,.94);
            box-shadow: 0 10px 32px rgba(31,53,38,.09);
        }
        .brand { letter-spacing: -1.7px; }
        .brand-mark { border-radius: 11px; box-shadow: 0 5px 12px rgba(22,75,54,.2); }
        .nav-links { gap: 30px; font-size: 12px; }
        .nav-links a { position: relative; padding: 8px 0; }
        .nav-links a::after {
            position: absolute;
            right: 0;
            bottom: 1px;
            left: 0;
            height: 2px;
            border-radius: 2px;
            background: var(--green);
            content: "";
            transform: scaleX(0);
            transform-origin: left;
            transition: transform .22s ease;
        }
        .nav-links a:hover::after { transform: scaleX(1); }
        .button {
            border-radius: 10px;
            box-shadow: 0 5px 14px rgba(22,75,54,.14);
            transition: background .2s, transform .2s, box-shadow .2s;
        }
        .button:hover { box-shadow: 0 9px 22px rgba(22,75,54,.22); }
        .hero {
            min-height: 535px;
            margin-top: 19px;
            border: 1px solid rgba(255,255,255,.34);
            border-radius: 25px;
            box-shadow: 0 28px 65px rgba(24,49,33,.17);
            isolation: isolate;
        }
        .hero::before {
            background-image:
                linear-gradient(90deg, rgba(10,30,20,.91) 0%, rgba(13,37,26,.76) 42%, rgba(13,37,26,.12) 100%),
                url("https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=2000&q=85");
            background-position: calc(50% + var(--hero-shift, 0px)) 52%;
            transition: background-position .25s ease-out;
        }
        .hero::after {
            position: absolute;
            top: 11%;
            right: 5%;
            width: 36%;
            height: 76%;
            border: 1px solid rgba(255,255,255,.28);
            border-radius: 22px;
            background:
                linear-gradient(90deg, transparent calc(50% - .5px), rgba(255,255,255,.25) calc(50% - .5px), rgba(255,255,255,.25) calc(50% + .5px), transparent calc(50% + .5px)),
                linear-gradient(0deg, transparent calc(50% - .5px), rgba(255,255,255,.22) calc(50% - .5px), rgba(255,255,255,.22) calc(50% + .5px), transparent calc(50% + .5px));
            box-shadow: inset 0 0 0 24px rgba(255,255,255,.04);
            content: "";
            opacity: .8;
            pointer-events: none;
            transform: perspective(700px) rotateY(-12deg);
            mask-image: linear-gradient(90deg, transparent, #000 30%);
        }
        .hero-content { padding-left: 8%; }
        .eyebrow {
            padding: 9px 13px;
            border: 1px solid rgba(217,247,107,.25);
            border-radius: 99px;
            background: rgba(15,46,30,.43);
            letter-spacing: 1.15px;
            backdrop-filter: blur(10px);
        }
        .eyebrow-dot { animation: pulse-dot 2s infinite; }
        h1 { font-size: clamp(48px, 6vw, 76px); letter-spacing: -4.5px; }
        .hero-copy { max-width: 400px; font-size: 15px; }
        .hero-note {
            right: 28px;
            bottom: 25px;
            padding: 11px 15px;
            border: 1px solid rgba(255,255,255,.19);
            border-radius: 99px;
            background: rgba(13,37,26,.43);
            backdrop-filter: blur(12px);
        }
        .search-panel {
            width: calc(100% - 100px);
            margin-top: -36px;
            padding: 14px;
            border-color: rgba(255,255,255,.9);
            border-radius: 17px;
            box-shadow: 0 20px 55px rgba(28,51,36,.12);
        }
        .search-field {
            min-height: 58px;
            border-radius: 10px;
            transition: background .2s;
        }
        .search-field:focus-within { background: #f5f8f2; }
        .search-field svg { color: #527a5d; transition: transform .2s; }
        .search-field:focus-within svg { transform: translateY(-2px); }
        .search-button { min-height: 54px; }
        .section { padding-top: 94px; }
        .section-kicker { letter-spacing: 1.7px; }
        h2 { letter-spacing: -1.7px; }
        .filters { gap: 9px; }
        .filter-chip {
            padding: 10px 17px;
            border-color: #e2e8de;
            background: rgba(255,255,255,.55);
            transition: color .2s, background .2s, border-color .2s, transform .2s;
        }
        .filter-chip:hover { transform: translateY(-2px); }
        .venue-grid { gap: 22px; }
        .venue-card {
            border-color: rgba(224,231,220,.9);
            border-radius: 17px;
            box-shadow: 0 8px 24px rgba(30,53,36,.035);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s;
            transform: perspective(1000px) rotateX(var(--tilt-x, 0deg)) rotateY(var(--tilt-y, 0deg)) translateY(var(--lift, 0px));
        }
        .venue-card:hover {
            border-color: #d5e0d1;
            box-shadow: 0 18px 38px rgba(30,53,36,.12);
            --lift: -5px;
        }
        .venue-image { height: 215px; }
        .venue-image img { transition: transform .65s cubic-bezier(.2,.7,.2,1); }
        .venue-card:hover .venue-image img { transform: scale(1.07); }
        .badge {
            border: 1px solid rgba(255,255,255,.75);
            box-shadow: 0 3px 12px rgba(0,0,0,.08);
            backdrop-filter: blur(8px);
        }
        .favorite {
            transition: color .2s, transform .2s, background .2s;
        }
        .favorite:hover { background: white; transform: scale(1.1); }
        .favorite.is-saved svg { fill: currentColor; animation: heart-pop .35s ease; }
        .venue-details { padding: 19px 19px 17px; }
        .small-button { box-shadow: none; }
        .small-button:hover { box-shadow: 0 6px 15px rgba(22,75,54,.18); }
        .benefit-icon { transition: transform .25s, background .25s; }
        .benefit:hover .benefit-icon { background: var(--lime); transform: translateY(-3px) rotate(-5deg); }
        .cta {
            position: relative;
            overflow: hidden;
            border: 1px solid #e1e9db;
            border-radius: 19px;
            background: linear-gradient(110deg, #eaf1e4 0%, #f1f5e9 57%, #e0edce 100%);
        }
        .cta::after {
            position: absolute;
            top: -80px;
            right: 16%;
            width: 230px;
            height: 230px;
            border: 1px solid rgba(65,105,66,.12);
            border-radius: 50%;
            box-shadow: 0 0 0 22px rgba(65,105,66,.035), 0 0 0 44px rgba(65,105,66,.025);
            content: "";
            pointer-events: none;
        }
        .cta > * { position: relative; z-index: 1; }
        .toast { border: 1px solid rgba(255,255,255,.12); border-radius: 12px; }
        .toast.show { animation: toast-in .35s cubic-bezier(.2,.8,.2,1) both; }
        .js-motion .reveal {
            opacity: 0;
            translate: 0 24px;
            transition: opacity .65s ease, translate .65s cubic-bezier(.2,.7,.2,1);
            transition-delay: var(--reveal-delay, 0ms);
        }
        .js-motion .reveal.is-visible { opacity: 1; translate: 0 0; }
        .venue-card.is-filtering-in { animation: card-in .42s cubic-bezier(.2,.7,.2,1) both; }
        @keyframes pulse-dot {
            0%, 100% { box-shadow: 0 0 0 4px rgba(217,247,107,.18); }
            50% { box-shadow: 0 0 0 7px rgba(217,247,107,.06); }
        }
        @keyframes heart-pop {
            0% { transform: scale(.65); }
            65% { transform: scale(1.3); }
            100% { transform: scale(1); }
        }
        @keyframes card-in {
            from { opacity: .35; transform: translateY(13px) scale(.985); }
            to { opacity: 1; transform: perspective(1000px) rotateX(0) rotateY(0) translateY(0) scale(1); }
        }
        @keyframes toast-in {
            from { opacity: 0; transform: translateY(12px) scale(.97); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        @media (max-width: 820px) {
            .hero { min-height: 480px; }
            .hero::after { right: 3%; width: 39%; }
            .search-panel { width: calc(100% - 40px); }
        }
        @media (max-width: 600px) {
            .site-header { top: 6px; width: calc(100% - 24px); height: 60px; margin-top: 6px; padding: 0 13px; border-radius: 13px; }
            .header-actions { gap: 11px; }
            .header-actions .button { padding: 10px 12px; }
            .hero { min-height: 440px; margin-top: 12px; border-radius: 19px; }
            .hero::before { background-image: linear-gradient(90deg, rgba(10,30,20,.9), rgba(13,37,26,.48)), url("https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=1000&q=80"); }
            .hero::after { top: 22%; right: -17%; width: 70%; height: 64%; opacity: .48; }
            .hero-content { padding: 48px 25px 85px; }
            h1 { font-size: 48px; letter-spacing: -3px; }
            .hero-note { right: 12px; bottom: 12px; padding: 8px 11px; }
            .search-panel { width: calc(100% - 24px); }
            .section { padding-top: 68px; }
            .venue-card { transform: none; }
            .venue-card:hover { --lift: 0; }
            .cta::after { right: -20%; }
        }
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                scroll-behavior: auto !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: .01ms !important;
            }
        }

        :root {
            color-scheme: dark;
            --ink: #f0f3ed;
            --muted: #9aa79d;
            --green: #cbf36a;
            --lime: #d7f878;
            --paper: #0b110e;
            --line: rgba(226,237,222,.12);
            --surface: #121b16;
        }
        body {
            color: var(--ink);
            background:
                radial-gradient(ellipse at 75% 4%, rgba(86,119,63,.12), transparent 30%),
                radial-gradient(ellipse at 0% 57%, rgba(31,83,56,.11), transparent 30%),
                #0b110e;
        }
        body::before {
            position: fixed;
            z-index: -1;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 180 180' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='.16'/%3E%3C/svg%3E");
            content: "";
            opacity: .14;
            pointer-events: none;
        }
        .site-header {
            border-color: rgba(255,255,255,.09);
            background: rgba(13,20,16,.72);
            color: #eef2ec;
        }
        .site-header.is-scrolled {
            border-color: rgba(255,255,255,.1);
            background: rgba(13,20,16,.94);
            box-shadow: 0 12px 35px rgba(0,0,0,.32);
        }
        .brand { color: #f4f6f1; }
        .brand span:last-child { color: var(--lime) !important; }
        .brand-mark {
            background: var(--lime);
            color: #122018;
            box-shadow: 0 0 22px rgba(215,248,120,.2);
        }
        .nav-links { color: #b1bdb3; }
        .nav-links a:hover, .login-link:hover { color: var(--lime); }
        .login-link { color: #d7ded6; }
        .button {
            background: var(--lime);
            color: #142018;
            box-shadow: 0 0 0 rgba(215,248,120,0);
        }
        .button:hover {
            background: #e4ff91;
            color: #142018;
            box-shadow: 0 8px 26px rgba(215,248,120,.16);
        }
        .hero {
            min-height: 570px;
            margin-top: 20px;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 22px;
            background: #142119;
            box-shadow: 0 28px 90px rgba(0,0,0,.36);
        }
        .hero::before {
            background-image:
                linear-gradient(90deg, rgba(7,15,10,.94) 0%, rgba(8,18,12,.85) 35%, rgba(8,18,12,.35) 70%, rgba(8,18,12,.18) 100%),
                linear-gradient(0deg, rgba(8,15,10,.45), transparent 42%),
                url("https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=2000&q=85");
            background-position: calc(50% + var(--hero-shift, 0px)) center;
            filter: saturate(.72);
        }
        .hero::after {
            top: 9%;
            right: 7%;
            width: 35%;
            height: 82%;
            border: 1px solid rgba(215,248,120,.35);
            border-radius: 16px;
            background:
                linear-gradient(90deg, transparent calc(50% - .5px), rgba(215,248,120,.28) calc(50% - .5px), rgba(215,248,120,.28) calc(50% + .5px), transparent calc(50% + .5px)),
                linear-gradient(0deg, transparent calc(50% - .5px), rgba(215,248,120,.25) calc(50% - .5px), rgba(215,248,120,.25) calc(50% + .5px), transparent calc(50% + .5px));
            box-shadow: inset 0 0 0 25px rgba(215,248,120,.045), 0 0 70px rgba(215,248,120,.07);
            opacity: .72;
            transform: perspective(800px) rotateY(-16deg);
            mask-image: linear-gradient(90deg, transparent, #000 32%);
        }
        .hero-content { padding: 66px 7.3% 96px; }
        .eyebrow {
            border-color: rgba(215,248,120,.24);
            background: rgba(10,21,14,.62);
            color: var(--lime);
        }
        h1 {
            max-width: 650px;
            margin-bottom: 20px;
            font-size: clamp(58px, 7vw, 88px);
            line-height: .99;
            letter-spacing: -6px;
        }
        h1 span { color: var(--lime); }
        .hero-copy { color: rgba(240,243,237,.72); }
        .hero-actions { display: flex; align-items: center; gap: 18px; margin-top: 29px; }
        .hero-actions .button { padding: 15px 20px; }
        .hero-secondary {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: rgba(240,243,237,.82);
            font-size: 12px;
            font-weight: 700;
        }
        .hero-secondary svg { width: 17px; color: var(--lime); transition: transform .2s; }
        .hero-secondary:hover svg { transform: translateX(4px); }
        .hero-side-card {
            position: absolute;
            z-index: 1;
            right: 7%;
            bottom: 86px;
            width: 230px;
            padding: 18px;
            border: 1px solid rgba(255,255,255,.24);
            border-radius: 14px;
            background: rgba(12,21,15,.62);
            box-shadow: 0 20px 50px rgba(0,0,0,.3);
            backdrop-filter: blur(18px);
            animation: float-card 5s ease-in-out infinite;
        }
        .hero-side-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; }
        .live-pill {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 6px 9px;
            border: 1px solid rgba(215,248,120,.2);
            border-radius: 99px;
            background: rgba(215,248,120,.08);
            color: var(--lime);
            font-size: 9px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
        }
        .live-pill::before { width: 6px; height: 6px; border-radius: 50%; background: var(--lime); content: ""; box-shadow: 0 0 9px var(--lime); }
        .side-number { color: rgba(255,255,255,.54); font: 700 10px "Manrope", sans-serif; letter-spacing: 1px; }
        .hero-side-card strong { display: block; font: 700 18px "Manrope", sans-serif; }
        .hero-side-card p { margin: 6px 0 15px; color: #aab5aa; font-size: 10px; }
        .side-slot { display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid rgba(255,255,255,.12); color: #cad2c9; font-size: 10px; }
        .side-slot span:last-child { color: var(--lime); font-weight: 700; }
        .hero-note { left: 7.3%; right: auto; bottom: 76px; padding: 0; border: 0; background: transparent; }
        .hero-note span { color: rgba(255,255,255,.75); }
        .avatar-stack img { border-color: #17241a; }
        .search-panel {
            width: calc(100% - 100px);
            margin-top: -36px;
            padding: 13px;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 16px;
            background: rgba(21,30,24,.97);
            box-shadow: 0 24px 70px rgba(0,0,0,.38);
        }
        .search-field { border-color: rgba(255,255,255,.1); }
        .search-field svg { color: var(--lime); }
        .search-field:focus-within { background: rgba(215,248,120,.06); }
        .field-copy label { color: #89978c; }
        .field-copy input, .field-copy select { color: #eff3ed; }
        .field-copy select option { background: #18231b; color: #f2f5ef; }
        .section { padding-top: 100px; }
        .section-kicker { color: var(--lime); }
        .section-subtitle { color: #8c998e; }
        .text-link { color: var(--lime); }
        .filters { margin-bottom: 24px; }
        .filter-chip { border-color: rgba(255,255,255,.13); background: rgba(255,255,255,.025); color: #aab5ab; }
        .filter-chip.active, .filter-chip:hover { border-color: var(--lime); background: var(--lime); color: #172019; }
        .venue-card { border-color: rgba(255,255,255,.1); background: #121b16; box-shadow: 0 12px 35px rgba(0,0,0,.18); }
        .venue-card:hover { border-color: rgba(215,248,120,.42); box-shadow: 0 22px 48px rgba(0,0,0,.35); }
        .venue-image { background: #24352a; }
        .venue-image::after {
            position: absolute;
            inset: 50% 0 0;
            background: linear-gradient(0deg, rgba(8,14,10,.5), transparent);
            content: "";
            pointer-events: none;
        }
        .badge { z-index: 1; border-color: rgba(255,255,255,.16); background: rgba(12,20,15,.76); color: #ecf1e9; }
        .favorite { z-index: 1; border: 1px solid rgba(255,255,255,.12); background: rgba(12,20,15,.76); color: #e9eee7; }
        h3 { color: #f0f3ed; }
        .rating { color: #e8eee6; }
        .location { color: #95a196; }
        .venue-divider { background: rgba(255,255,255,.1); }
        .price { color: #8e9b91; }
        .price strong { color: var(--lime); }
        .small-button { background: rgba(215,248,120,.1); color: var(--lime); }
        .small-button:hover { background: var(--lime); color: #172019; }
        .empty-results { border-color: rgba(255,255,255,.18); color: #a8b2a9; }
        .benefits {
            position: relative;
            margin-top: 50px;
            margin-bottom: 76px;
            padding: 28px;
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 17px;
            background: linear-gradient(120deg, rgba(23,34,26,.94), rgba(17,26,20,.94));
        }
        .benefit-icon { background: rgba(215,248,120,.1); color: var(--lime); }
        .benefit:hover .benefit-icon { background: var(--lime); color: #172019; }
        .benefit h3 { font-size: 12px; }
        .benefit p { color: #99a59b; }
        .cta {
            border-color: rgba(215,248,120,.22);
            background: linear-gradient(112deg, #c8ef62, #daf781 58%, #bfe754);
            color: #172019;
        }
        .cta h2 { color: #172019; }
        .cta p { color: rgba(23,32,25,.72); }
        .cta .button { border: 1px solid rgba(20,32,23,.18); background: #172019; color: #f3f6ef; }
        .cta .button:hover { background: #263a2a; color: #fff; }
        .cta::after { border-color: rgba(23,32,25,.16); box-shadow: 0 0 0 22px rgba(23,32,25,.05), 0 0 0 44px rgba(23,32,25,.035); }
        footer { padding: 58px 0 0; border-color: rgba(255,255,255,.1); }
        .footer-grid {
            display: grid;
            grid-template-columns: 1.15fr .8fr .9fr 1.25fr;
            gap: 40px;
            padding-bottom: 48px;
        }
        .footer-about { max-width: 240px; }
        .footer-about .footer-brand { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 15px; font-size: 24px; }
        .footer-brand-logo { width: 30px; height: 30px; border-radius: 8px; object-fit: cover; }
        .footer-about p { color: #8d9a8f; font-size: 12px; line-height: 1.8; }
        .footer-heading {
            margin: 4px 0 17px;
            color: #e8eee7;
            font: 700 11px "Manrope", sans-serif;
            letter-spacing: .2px;
        }
        .footer-address { display: flex; align-items: flex-start; gap: 10px; color: #a4b0a5; font-size: 11px; line-height: 1.7; }
        .footer-address svg { width: 17px; flex: 0 0 auto; margin-top: 1px; color: var(--lime); }
        .footer-venues { display: flex; flex-direction: column; gap: 13px; }
        .footer-venues .footer-address { align-items: center; }
        .footer-venues .footer-address img { flex: 0 0 auto; }
        .footer-socials { display: flex; flex-direction: column; align-items: flex-start; gap: 12px; }
        .footer-social {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            color: #a4b0a5;
            font-size: 11px;
            transition: color .2s, transform .2s;
        }
        .footer-social:hover { color: var(--lime); transform: translateX(3px); }
        .social-icon {
            width: 28px;
            height: 28px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 9px;
            color: var(--lime);
        }
        .social-icon svg { width: 14px; height: 14px; }
        .footer-map {
            position: relative;
            display: block;
            height: 150px;
            min-height: 150px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 13px;
            background: #dfe7dc;
        }
        #venue-map {
            position: absolute;
            z-index: 0;
            inset: 0;
        }
        .map-caption {
            position: absolute;
            z-index: 500;
            right: 10px;
            bottom: 10px;
            left: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
            color: #eaf0e8;
            font-size: 10px;
            padding: 8px 10px;
            border: 1px solid rgba(255,255,255,.12);
            border-radius: 8px;
            background: rgba(12,20,15,.86);
            backdrop-filter: blur(8px);
        }
        .map-caption a { color: var(--lime); font-weight: 700; white-space: nowrap; }
        .footer-bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 18px 0;
            border-top: 1px solid rgba(255,255,255,.1);
            color: #77847a;
            font-size: 10px;
        }
        .footer-inner { color: #77847a; }
        .footer-brand { color: #f0f3ed; }
        .footer-brand span { color: var(--lime) !important; }
        .toast { border-color: rgba(215,248,120,.2); background: #1b2b1f; color: #f4f7f1; }
        @keyframes float-card {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-7px); }
        }
        @media (max-width: 820px) {
            .hero { min-height: 535px; }
            .hero::after { right: 3%; }
            .hero-side-card { right: 5%; bottom: 74px; }
            .search-panel { width: calc(100% - 40px); }
        }
        @media (max-width: 600px) {
            body { background: #0b110e; }
            .site-header { background: rgba(13,20,16,.83); }
            .hero { min-height: 560px; }
            .hero::before { background-image: linear-gradient(90deg, rgba(7,15,10,.92), rgba(8,18,12,.5)), linear-gradient(0deg, rgba(8,15,10,.48), transparent 65%), url("https://images.unsplash.com/photo-1622279457486-62dcc4a431d6?auto=format&fit=crop&w=1000&q=80"); }
            .hero::after { top: 25%; right: -17%; width: 70%; height: 55%; }
            .hero-content { padding: 48px 25px 145px; }
            h1 { font-size: 55px; letter-spacing: -3.8px; }
            .hero-actions { align-items: flex-start; flex-direction: column; gap: 14px; }
            .hero-side-card { right: 17px; bottom: 79px; width: 188px; padding: 13px; }
            .hero-side-card strong { font-size: 15px; }
            .hero-side-card p { margin-bottom: 10px; }
            .hero-note { left: 17px; bottom: 76px; }
            .search-panel { border-radius: 13px; }
            .search-field { border-right: 0; }
            .section { padding-top: 76px; }
            .benefits { margin-top: 30px; margin-bottom: 54px; padding: 22px; }
            .cta { border-radius: 16px; }
            footer { padding-top: 42px; }
            .footer-grid { grid-template-columns: 1fr 1fr; gap: 30px 18px; padding-bottom: 32px; }
            .footer-about { grid-column: 1 / -1; max-width: 310px; }
            .footer-grid > div:last-child { grid-column: 1 / -1; }
            .footer-map { min-height: 135px; }
            .footer-bottom { align-items: flex-start; flex-direction: column; gap: 8px; }
        }
        @media (prefers-reduced-motion: reduce) {
            .hero-side-card { animation: none; }
        }
        [data-theme="light"] {
            color-scheme: light;
            --ink: #17231d;
            --muted: #78827b;
            --green: #245c43;
            --lime: #d7f36a;
            --paper: #f7f8f4;
            --line: #e7eae3;
        }
        [data-theme="light"] body {
            color: var(--ink);
            background:
                radial-gradient(ellipse at 4% 30%, rgba(216,236,204,.28), transparent 28%),
                radial-gradient(ellipse at 96% 64%, rgba(221,231,211,.3), transparent 26%),
                var(--paper);
        }
        [data-theme="light"] body::before { opacity: .035; }
        [data-theme="light"] .site-header {
            border-color: rgba(255,255,255,.82);
            background: rgba(255,255,255,.78);
            color: var(--ink);
        }
        [data-theme="light"] .site-header.is-scrolled {
            border-color: rgba(225,232,223,.88);
            background: rgba(255,255,255,.94);
            box-shadow: 0 10px 32px rgba(31,53,38,.09);
        }
        [data-theme="light"] .brand { color: var(--ink); }
        [data-theme="light"] .brand span:last-child { color: #6f8b70 !important; }
        [data-theme="light"] .brand-mark { background: var(--green); color: var(--lime); }
        [data-theme="light"] .nav-links { color: #68746c; }
        [data-theme="light"] .nav-links a:hover,
        [data-theme="light"] .login-link:hover { color: var(--green); }
        [data-theme="light"] .login-link { color: #38463d; }
        [data-theme="light"] .button { background: var(--green); color: #fff; }
        [data-theme="light"] .button:hover { background: #194a34; color: #fff; }
        [data-theme="light"] .hero .button { background: var(--lime); color: #142018; }
        [data-theme="light"] .hero .button:hover { background: #e4ff91; color: #142018; }
        [data-theme="light"] .hero-side-card {
            border-color: rgba(255,255,255,.24);
            background: rgba(12,21,15,.62);
            color: #f0f3ed;
        }
        [data-theme="light"] .search-panel {
            border-color: rgba(25,45,33,.06);
            background: #fff;
            box-shadow: 0 14px 40px rgba(24,42,30,.1);
        }
        [data-theme="light"] .search-field { border-color: var(--line); }
        [data-theme="light"] .search-field svg { color: #688271; }
        [data-theme="light"] .search-field:focus-within { background: #f5f8f2; }
        [data-theme="light"] .field-copy label { color: #89918b; }
        [data-theme="light"] .field-copy input,
        [data-theme="light"] .field-copy select { color: var(--ink); }
        [data-theme="light"] .field-copy select option { background: #fff; color: var(--ink); }
        [data-theme="light"] .section-kicker { color: #6d8b72; }
        [data-theme="light"] .section-subtitle { color: var(--muted); }
        [data-theme="light"] .text-link { color: var(--green); }
        [data-theme="light"] .filter-chip {
            border-color: #e2e8de;
            background: rgba(255,255,255,.55);
            color: #68746c;
        }
        [data-theme="light"] .filter-chip.active,
        [data-theme="light"] .filter-chip:hover { border-color: var(--green); background: var(--green); color: #fff; }
        [data-theme="light"] .venue-card {
            border-color: rgba(224,231,220,.9);
            background: #fff;
            box-shadow: 0 8px 24px rgba(30,53,36,.035);
        }
        [data-theme="light"] .venue-card:hover {
            border-color: #d5e0d1;
            box-shadow: 0 18px 38px rgba(30,53,36,.12);
        }
        [data-theme="light"] .venue-image { background: #dce5dc; }
        [data-theme="light"] .badge {
            border-color: rgba(255,255,255,.75);
            background: rgba(255,255,255,.94);
            color: #314238;
        }
        [data-theme="light"] .favorite { border: 0; background: rgba(255,255,255,.94); color: #39483e; }
        [data-theme="light"] h3 { color: var(--ink); }
        [data-theme="light"] .rating { color: #25382c; }
        [data-theme="light"] .location { color: #808981; }
        [data-theme="light"] .venue-divider { background: #edf0eb; }
        [data-theme="light"] .price { color: #7b857d; }
        [data-theme="light"] .price strong { color: var(--ink); }
        [data-theme="light"] .small-button { background: var(--green); color: #fff; }
        [data-theme="light"] .small-button:hover { background: #194a34; color: #fff; }
        [data-theme="light"] .empty-results { border-color: #dce3da; color: var(--muted); }
        [data-theme="light"] .benefits {
            border-color: #e1e9db;
            background: rgba(255,255,255,.72);
        }
        [data-theme="light"] .benefit-icon { background: #eaf0e7; color: var(--green); }
        [data-theme="light"] .benefit:hover .benefit-icon { background: var(--lime); color: var(--green); }
        [data-theme="light"] .benefit p { color: #7b857d; }
        [data-theme="light"] .cta {
            border-color: #e1e9db;
            background: linear-gradient(110deg, #eaf1e4 0%, #f1f5e9 57%, #e0edce 100%);
            color: var(--ink);
        }
        [data-theme="light"] .cta h2 { color: var(--ink); }
        [data-theme="light"] .cta p { color: #6e7c70; }
        [data-theme="light"] .cta .button { border: 0; background: var(--green); color: #fff; }
        [data-theme="light"] .cta .button:hover { background: #194a34; color: #fff; }
        [data-theme="light"] .cta::after {
            border-color: rgba(65,105,66,.12);
            box-shadow: 0 0 0 22px rgba(65,105,66,.035), 0 0 0 44px rgba(65,105,66,.025);
        }
        [data-theme="light"] footer { border-color: rgba(255,255,255,.12); }
        [data-theme="light"] .footer-map { border-color: rgba(255,255,255,.13); }
        [data-theme="light"] .map-caption { color: #fff; background: rgba(12,20,15,.86); }
        [data-theme="light"] .toast { border-color: rgba(255,255,255,.12); background: #172e22; color: #fff; }
        .theme-toggle {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 50%;
            background: rgba(255,255,255,.06);
            color: #e9eee7;
            cursor: pointer;
            transition: transform .2s, background .2s, color .2s, border-color .2s;
        }
        .theme-toggle:hover { transform: rotate(-12deg); background: rgba(255,255,255,.12); }
        .theme-toggle svg { width: 17px; height: 17px; }
        .theme-toggle .sun-icon { display: none; }
        [data-theme="light"] .theme-toggle {
            border-color: #e1e7de;
            background: #f4f7f0;
            color: #385843;
        }
        [data-theme="light"] .theme-toggle:hover { background: #eaf0e5; }
        [data-theme="light"] .theme-toggle .sun-icon { display: block; }
        [data-theme="light"] .theme-toggle .moon-icon { display: none; }
        .admin-link {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            color: #d7ded6;
            font-size: 11px;
            font-weight: 700;
        }
        .admin-link svg { width: 15px; color: var(--lime); }
        [data-theme="light"] .admin-link { color: #38463d; }
        [data-theme="light"] .admin-link svg { color: var(--green); }
        .booking-dialog {
            width: min(520px, calc(100% - 32px));
            max-height: min(90vh, 760px);
            padding: 0;
            overflow: auto;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 20px;
            background: #121b16;
            color: #f0f3ed;
            box-shadow: 0 30px 100px rgba(0,0,0,.5);
        }
        .booking-dialog::backdrop { background: rgba(3,8,5,.72); backdrop-filter: blur(7px); }
        .booking-dialog[open] { animation: dialog-in .24s cubic-bezier(.2,.8,.2,1) both; }
        .booking-dialog-content { padding: 27px; }
        .booking-dialog-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 20px; margin-bottom: 22px; }
        .booking-dialog-header .section-kicker { margin-bottom: 8px; }
        .booking-dialog-header h2 { font-size: 25px; }
        .booking-dialog-header p { margin: 8px 0 0; color: #97a399; font-size: 12px; }
        .dialog-close {
            width: 34px;
            height: 34px;
            flex: 0 0 auto;
            display: grid;
            place-items: center;
            border: 1px solid rgba(255,255,255,.13);
            border-radius: 50%;
            background: transparent;
            color: currentColor;
            cursor: pointer;
        }
        .dialog-close svg { width: 16px; }
        .booking-summary {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 20px;
            padding: 13px 15px;
            border: 1px solid rgba(215,248,120,.2);
            border-radius: 11px;
            background: rgba(215,248,120,.06);
            font-size: 11px;
        }
        .booking-summary strong { color: var(--lime); }
        .booking-date-field { margin-bottom: 15px; }
        .booking-date-field input { width: 100%; }
        .booking-time-heading { display: flex; justify-content: space-between; align-items: center; gap: 10px; margin: 0 0 9px; }
        .booking-time-heading label { color: #aab5ab; font-size: 10px; font-weight: 700; }
        .booking-time-heading span { color: #829085; font-size: 9px; }
        .booking-time-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 7px; }
        .booking-time-slot {
            min-height: 36px;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 8px;
            background: rgba(255,255,255,.04);
            color: #e7eee6;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: border-color .18s, background .18s, color .18s, transform .18s;
        }
        .booking-time-slot:hover:not(:disabled) { transform: translateY(-1px); border-color: var(--lime); }
        .booking-time-slot.selected { border-color: var(--lime); background: var(--lime); color: #172019; }
        .booking-time-slot:disabled { border-color: rgba(255,255,255,.07); background: rgba(255,255,255,.02); color: #68756b; cursor: not-allowed; text-decoration: line-through; }
        .booking-availability { min-height: 17px; margin: 7px 0 0; color: #829085; font-size: 9px; }
        .booking-availability.error { color: #ef9c88; }
        .booking-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .booking-field { display: flex; flex-direction: column; gap: 7px; }
        .booking-field.full { grid-column: 1 / -1; }
        .booking-field label { color: #aab5ab; font-size: 10px; font-weight: 700; }
        .booking-field input, .booking-field textarea {
            width: 100%;
            padding: 12px 13px;
            border: 1px solid rgba(255,255,255,.14);
            border-radius: 9px;
            outline: 0;
            background: rgba(255,255,255,.045);
            color: #f0f3ed;
            font-size: 12px;
            transition: border-color .2s, box-shadow .2s;
        }
        .booking-field input:focus, .booking-field textarea:focus { border-color: var(--lime); box-shadow: 0 0 0 3px rgba(215,248,120,.1); }
        .booking-field textarea { min-height: 78px; resize: vertical; }
        .booking-field input::placeholder, .booking-field textarea::placeholder { color: #718075; }
        .booking-note { margin: 14px 0 0; color: #829085; font-size: 10px; line-height: 1.6; }
        .booking-dialog-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 21px; }
        .dialog-cancel { padding: 12px 16px; border: 1px solid rgba(255,255,255,.14); border-radius: 9px; background: transparent; color: #d6ddd5; font-size: 11px; font-weight: 700; cursor: pointer; }
        .booking-submit { padding: 12px 17px; font-size: 11px; }
        [data-theme="light"] .booking-dialog { border-color: #e2e8de; background: #fff; color: #17231d; }
        [data-theme="light"] .booking-dialog-header p,
        [data-theme="light"] .booking-field label,
        [data-theme="light"] .booking-note { color: #68746c; }
        [data-theme="light"] .dialog-close,
        [data-theme="light"] .dialog-cancel { border-color: #e1e7de; color: #344239; }
        [data-theme="light"] .booking-field input,
        [data-theme="light"] .booking-field textarea { border-color: #dfe6dc; background: #f8faf6; color: #17231d; }
        [data-theme="light"] .booking-field input::placeholder,
        [data-theme="light"] .booking-field textarea::placeholder { color: #89958b; }
        [data-theme="light"] .booking-summary { background: #f6f9ee; }
        [data-theme="light"] .booking-summary strong { color: #385b32; }
        [data-theme="light"] .booking-time-heading label { color: #344239; }
        [data-theme="light"] .booking-time-heading span,
        [data-theme="light"] .booking-availability { color: #68746c; }
        [data-theme="light"] .booking-time-slot { border-color: #dfe6dc; background: #f8faf6; color: #344239; }
        [data-theme="light"] .booking-time-slot.selected { border-color: #245c43; background: #245c43; color: #fff; }
        [data-theme="light"] .booking-time-slot:disabled { border-color: #e5e9e3; background: #f1f3ef; color: #9aa39c; }
        [data-theme="light"] .booking-availability.error { color: #ae4c37; }
        @keyframes dialog-in {
            from { opacity: 0; transform: translateY(12px) scale(.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
        html[data-theme="dark"] footer {
            border-color: #e7eae3;
            background: #fff;
            color: #111;
            transition: background .25s, color .25s, border-color .25s;
        }
        html[data-theme="dark"] footer .footer-about p,
        html[data-theme="dark"] footer .footer-heading,
        html[data-theme="dark"] footer .footer-address,
        html[data-theme="dark"] footer .footer-social,
        html[data-theme="dark"] footer .footer-bottom { color: #111; }
        html[data-theme="dark"] footer .footer-brand,
        html[data-theme="dark"] footer .footer-brand span { color: #111 !important; }
        html[data-theme="dark"] footer .footer-address svg,
        html[data-theme="dark"] footer .map-caption a { color: #245c43; }
        [data-theme="dark"] footer .map-caption { background: rgba(255,255,255,.9); }
        html[data-theme="dark"] footer .footer-social:hover { color: #245c43; }
        html[data-theme="dark"] footer .social-icon { border-color: #e1e8dd; color: #245c43; }
        html[data-theme="dark"] footer .footer-bottom { border-color: #e7eae3; }
        html[data-theme="light"] footer {
            border-color: rgba(255,255,255,.12);
            background: #000;
            color: #fff;
            transition: background .25s, color .25s, border-color .25s;
        }
        html[data-theme="light"] footer .footer-about p,
        html[data-theme="light"] footer .footer-heading,
        html[data-theme="light"] footer .footer-address,
        html[data-theme="light"] footer .footer-social,
        html[data-theme="light"] footer .footer-bottom { color: #fff; }
        html[data-theme="light"] footer .map-caption { color: #fff; background: rgba(12,20,15,.86); }
        html[data-theme="light"] footer .footer-brand,
        html[data-theme="light"] footer .footer-brand span { color: #fff !important; }
        html[data-theme="light"] footer .footer-social:hover { color: var(--lime); }
        html        [data-theme="light"] footer .footer-bottom { border-color: rgba(255,255,255,.14); }
        .leaflet-container { font-family: "DM Sans", sans-serif; }
        .leaflet-control-attribution { font-size: 8px !important; }
        @media (max-width: 600px) {
            .theme-toggle { width: 34px; height: 34px; }
            .admin-link { font-size: 10px; }
            .booking-dialog-content { padding: 21px; }
            .booking-fields { grid-template-columns: 1fr; }
            .booking-field.full { grid-column: auto; }
            .booking-time-grid { grid-template-columns: repeat(4, minmax(0, 1fr)); }
            .hero {
                display: block;
                min-height: 0;
                padding-bottom: 1px;
            }
            .hero-content { padding: 46px 24px 0; }
            .hero-actions { align-items: flex-start; }
            .hero-actions > .button { max-width: 100%; }
            .hero-side-card {
                position: relative;
                right: auto;
                bottom: auto;
                width: min(210px, calc(100% - 34px));
                margin: 22px 17px 112px auto;
                padding: 14px;
            }
            .hero-side-top { margin-bottom: 15px; }
            .hero-note { left: 17px; bottom: 60px; }
        }
    </style>
</head>
<body>
    <header class="shell site-header">
        <a class="brand" href="{{ route('home') }}" aria-label="{{ $primaryVenue?->name ?? 'Rally Padel' }}, beranda">
            @if ($primaryVenue?->logo_path)
                <img class="brand-logo" src="{{ minio_url($primaryVenue->logo_path) }}" alt="">
            @else
                <span class="brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none"><path d="M5 19 18.5 5M8 5l11 11M5 8l11 11" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg>
                </span>
            @endif
            {{ $primaryVenue?->name ?? 'Rally' }}<span style="color:#6f8b70">.</span>
        </a>
        <nav class="nav-links" aria-label="Navigasi utama">
            <a href="#lapangan">Cari lapangan</a>
            <a href="{{ route('schedule') }}">Lihat jadwal</a>
            <a href="{{ route('booking.upload-form') }}">Upload bukti bayar</a>
            <a href="#keunggulan">Cara kerja</a>
            <a href="#tentang">Tentang kami</a>
        </nav>
        <div class="header-actions">
            <button class="theme-toggle" id="theme-toggle" type="button" aria-label="Aktifkan mode terang" title="Aktifkan mode terang" aria-pressed="true">
                <svg class="sun-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="4" stroke="currentColor" stroke-width="1.7"/><path d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <svg class="moon-icon" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20.2 15.1A8.5 8.5 0 0 1 8.9 3.8 8.5 8.5 0 1 0 20.2 15Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
            <a class="admin-link" href="{{ route('login') }}" aria-label="Masuk sebagai admin">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 3 19 6v5c0 4.7-3 8-7 10-4-2-7-5.3-7-10V6l7-3Z" stroke="currentColor" stroke-width="1.7"/><path d="M9 12h6m-3-3v6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                Masuk Admin
            </a>
        </div>
    </header>

    @if ($errors->any())
        <div class="shell" role="alert" style="margin-top:16px;padding:13px 16px;border:1px solid #efc5bb;border-radius:10px;background:#fff2ee;color:#8f3828;font-size:13px">
            {{ $errors->first() }}
        </div>
    @endif

    <main>
        <section class="shell hero" aria-labelledby="hero-title">
            <div class="hero-content">
                <div class="eyebrow"><span class="eyebrow-dot"></span> Your next game starts here</div>
                <h1 id="hero-title">Main padel,<br><span>tanpa ribet.</span></h1>
                <p class="hero-copy">Temukan lapangan favoritmu, pilih waktu main, dan ajak teman. Semua beres dalam beberapa klik.</p>
                <div class="hero-actions">
                    <a class="button" href="#lapangan">Temukan lapangan <span aria-hidden="true">↗</span></a>
                    <a class="hero-secondary" href="{{ route('schedule') }}">Lihat semua jadwal <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
                    <a class="hero-secondary" href="{{ route('booking.upload-form') }}">Upload bukti bayar <span aria-hidden="true">↗</span></a>
                </div>
            </div>
            <aside class="hero-side-card" aria-label="Contoh ketersediaan lapangan">
                <div class="hero-side-top"><span class="live-pill">Lapangan aktif</span><span class="side-number">{{ str_pad((string) $courts->count(), 2, '0', STR_PAD_LEFT) }}</span></div>
                <strong>Game on tonight.</strong>
                <p>Temukan slot dan ajak timmu main.</p>
                <div class="side-slot"><span>Jadwal hari ini</span><span>↗</span></div>
            </aside>
            <div class="hero-note">
                <div class="avatar-stack" aria-hidden="true">
                    <img src="https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=80&q=75" alt="">
                    <img src="https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=80&q=75" alt="">
                    <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=80&q=75" alt="">
                </div>
                <span>Dipercaya komunitas padel</span>
            </div>
        </section>

        <form class="shell search-panel" id="search-form">
            <div class="search-field">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.7"/></svg>
                <div class="field-copy">
                    <label for="location">Lokasi</label>
                    <select id="location" name="location">
                        <option value="all">Semua lokasi</option>
                        @foreach ($courts->pluck('location')->unique()->sort() as $courtLocation)
                            <option value="{{ \Illuminate\Support\Str::slug($courtLocation) }}">{{ $courtLocation }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="search-field">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="3.5" y="5" width="17" height="16" rx="2" stroke="currentColor" stroke-width="1.7"/><path d="M16 3v4M8 3v4M4 10h16" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <div class="field-copy">
                    <label for="play-date">Tanggal main</label>
                    <input id="play-date" name="date" type="date" required>
                </div>
            </div>
            <div class="search-field">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg>
                <div class="field-copy">
                    <label for="play-time">Waktu</label>
                    <select id="play-time" name="time">
                        <option value="all">Semua waktu</option>
                        <option value="pagi">Pagi · 06.00–11.00</option>
                        <option value="siang">Siang · 11.00–16.00</option>
                        <option value="malam">Malam · 16.00–22.00</option>
                    </select>
                </div>
            </div>
            <button class="button search-button" type="submit">
                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="2"/><path d="m16 16 4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                Cari lapangan
            </button>
        </form>

        <section class="shell section" id="lapangan" aria-labelledby="venue-heading">
            <div class="section-heading">
                <div>
                    <p class="section-kicker">Court picks for you</p>
                    <h2 id="venue-heading">Lapangan pilihan</h2>
                    <p class="section-subtitle" id="results-caption">Temukan spot seru untuk rally berikutnya.</p>
                </div>
                <a class="text-link" href="#lapangan">Jelajahi semua <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
            </div>
            <div class="filters" role="group" aria-label="Filter jenis lapangan">
                <button class="filter-chip active" type="button" data-filter="all">Semua lapangan</button>
                <button class="filter-chip" type="button" data-filter="indoor">Indoor</button>
                <button class="filter-chip" type="button" data-filter="outdoor">Outdoor</button>
            </div>
            <div class="venue-grid" id="venue-grid">
                @forelse ($courts as $court)
                    <article class="venue-card" data-type="{{ $court->court_type }}" data-location="{{ \Illuminate\Support\Str::slug($court->location) }}" data-time="pagi siang malam">
                        <div class="venue-image">
                            <img src="{{ $court->image_url ?: 'https://images.unsplash.com/photo-1554068865-24cecd4e34b8?auto=format&fit=crop&w=900&q=80' }}" alt="Lapangan padel {{ $court->name }}">
                            <span class="badge">{{ ucfirst($court->court_type) }}</span>
                            <button class="favorite" type="button" aria-label="Simpan {{ $court->name }} ke favorit"><svg viewBox="0 0 24 24" fill="none"><path d="M20.8 8.8c0 4.2-8.8 10-8.8 10s-8.8-5.8-8.8-10a4.7 4.7 0 0 1 8.8-2.3 4.7 4.7 0 0 1 8.8 2.3Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></button>
                        </div>
                        <div class="venue-details">
                            <div class="venue-title-row"><h3>{{ $court->name }}</h3></div>
                            <div class="location"><svg viewBox="0 0 24 24" fill="none"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.7"/></svg> {{ $court->location }}</div>
                            <div class="venue-divider"></div>
                            <div class="venue-bottom">
                                <span class="price"><strong>Rp{{ number_format($court->price_per_hour, 0, ',', '.') }}</strong> <span>/ jam</span></span>
                                <button class="button small-button book-button" type="button" data-venue="{{ $court->name }}" data-venue-ulid="{{ $court->ulid }}" @disabled(! $court->venue?->is_active || (! $court->venue->qris_path && $court->venue->bankAccounts->isEmpty()))>
                                    {{ ! $court->venue?->is_active || (! $court->venue->qris_path && $court->venue->bankAccounts->isEmpty()) ? 'Pembayaran belum tersedia' : 'Pilih jadwal' }}
                                </button>
                            </div>
                        </div>
                    </article>
                @empty
                    <p class="empty-results" style="display:block">Belum ada lapangan aktif. Silakan kembali lagi nanti.</p>
                @endforelse
            </div>
            <p class="empty-results" id="empty-results">Belum ada lapangan untuk pilihan tersebut. Coba ubah lokasi atau waktu mainmu.</p>
        </section>

        <section class="shell benefits" id="keunggulan" aria-label="Keunggulan Rally">
            <div class="benefit">
                <span class="benefit-icon"><svg viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="1.7"/><path d="M12 7v5l3 2" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
                <div><h3>Booking dalam hitungan menit</h3><p>Pilih lapangan dan jam bermain tanpa perlu bolak-balik chat.</p></div>
            </div>
            <div class="benefit">
                <span class="benefit-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M12 3 19 6v5c0 4.7-3 8-7 10-4-2-7-5.3-7-10V6l7-3Z" stroke="currentColor" stroke-width="1.7"/><path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <div><h3>Harga jelas, tanpa kejutan</h3><p>Lihat harga lapangan di awal sebelum kamu mengunci jadwal.</p></div>
            </div>
            <div class="benefit">
                <span class="benefit-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2m16 0v-2a4 4 0 0 0-3-3.9M14 3.1a4 4 0 0 1 0 7.8M10 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0Z" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
                <div><h3>Lebih gampang cari teman main</h3><p>Bangun rutinitas dan ajak komunitasmu kembali ke lapangan.</p></div>
            </div>
        </section>

        <section class="shell cta" id="tentang">
            <div><h2>Siap untuk rally berikutnya?</h2><p>Lapangan favoritmu mungkin tinggal satu klik lagi.</p></div>
            <a class="button" href="#lapangan">Cari lapangan <span aria-hidden="true">↗</span></a>
        </section>
    </main>

    <footer>
        <div class="shell">
            <div class="footer-grid">
                <div class="footer-about">
                    <a class="footer-brand" href="{{ route('home') }}">
                        @if ($primaryVenue?->logo_path)
                            <img class="footer-brand-logo" src="{{ minio_url($primaryVenue->logo_path) }}" alt="">
                        @endif
                        {{ $primaryVenue?->name ?? 'Rally' }}<span style="color:#6f8b70">.</span>
                    </a>
                    <p>Temukan lapangan, kumpulkan teman, dan nikmati permainan. Semua rally dimulai dari sini.</p>
                </div>
                <div>
                    <h2 class="footer-heading">Alamat</h2>
                    <div class="footer-venues">
                        @forelse ($venues as $venue)
                            <div class="footer-address">
                                @if ($venue->logo_path)
                                    <img src="{{ minio_url($venue->logo_path) }}" alt="Logo {{ $venue->name }}" width="30" height="30" class="rounded object-fit-cover">
                                @else
                                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z" stroke="currentColor" stroke-width="1.7"/><circle cx="12" cy="10" r="2.5" stroke="currentColor" stroke-width="1.7"/></svg>
                                @endif
                                <span><strong>{{ $venue->name }}</strong><br>{{ $venue->address }}</span>
                            </div>
                        @empty
                            <span class="footer-address">Informasi venue belum tersedia.</span>
                        @endforelse
                    </div>
                </div>
                <div>
                    <h2 class="footer-heading">Ikuti Rally</h2>
                    <div class="footer-socials">
                        @foreach ($socialLinks as $socialLink)
                            <a class="footer-social" href="{{ $socialLink->url }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $socialLink->name }}">
                                <span class="social-icon" aria-hidden="true"><i class="{{ \App\Models\SocialLink::platformIcon($socialLink->platform) }}"></i></span>
                                {{ $socialLink->name }}
                            </a>
                        @endforeach
                        @if ($socialLinks->isEmpty())
                            <span class="footer-address">Tautan sosial media belum tersedia.</span>
                        @endif
                    </div>
                </div>
                <div>
                    <h2 class="footer-heading">Temukan kami</h2>
                    <div class="footer-map">
                        <div id="venue-map" role="application" aria-label="Peta interaktif semua venue Rally"></div>
                        <div class="map-caption">
                            @if ($venues->isNotEmpty())
                                <span>{{ $venues->count() }} venue aktif</span>
                                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode($venues->first()->latitude . ',' . $venues->first()->longitude) }}" target="_blank" rel="noopener noreferrer">Venue pertama ↗</a>
                            @else
                                <span>Lokasi venue belum tersedia</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-bottom">
                <span>© {{ date('Y') }} {{ $primaryVenue?->name ?? 'Rally Padel' }}. Semua hak dilindungi.</span>
                <span>Temukan lapangan. Mulai permainan.</span>
            </div>
        </div>
    </footer>
    <div class="toast" id="toast" role="status" aria-live="polite"></div>
    <dialog class="booking-dialog" id="booking-dialog" aria-labelledby="booking-title">
        <form class="booking-dialog-content" id="booking-form" action="{{ route('booking.store') }}" method="POST">
            @csrf
            <input id="booking-court-ulid" name="court_ulid" type="hidden" required>
            <div class="booking-dialog-header">
                <div>
                    <p class="section-kicker">Booking lapangan</p>
                    <h2 id="booking-title">Data diri kamu</h2>
                    <p>Isi informasi kontak untuk melanjutkan booking.</p>
                </div>
                <button class="dialog-close" type="button" data-close-dialog aria-label="Tutup formulir">
                    <svg viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                </button>
            </div>
            <div class="booking-summary">
                <strong id="booking-venue">Nama lapangan</strong>
                <span id="booking-schedule">Pilih tanggal dan slot jam</span>
            </div>
            <div class="booking-field booking-date-field">
                <label for="booking-date">Tanggal main *</label>
                <input id="booking-date" name="booking_date" type="date" value="{{ old('booking_date') }}" required>
            </div>
            <div class="booking-time-heading">
                <label for="booking-time-slots">Pilih jam mulai *</label>
                <span>Durasi 1 jam</span>
            </div>
            <div class="booking-time-grid" id="booking-time-slots" role="group" aria-label="Pilihan jam booking"></div>
            <p class="booking-availability" id="booking-availability" aria-live="polite">Pilih tanggal untuk melihat slot yang tersedia.</p>
            <input id="booking-slot-time" name="booking_time" type="hidden" required>
            <div class="booking-fields">
                <div class="booking-field full">
                    <label for="customer-name">Nama lengkap *</label>
                    <input id="customer-name" name="name" autocomplete="name" placeholder="Nama sesuai identitas" required>
                </div>
                <div class="booking-field">
                    <label for="customer-phone">Nomor WhatsApp *</label>
                    <input id="customer-phone" name="phone" type="tel" autocomplete="tel" inputmode="tel" placeholder="08xxxxxxxxxx" required>
                </div>
                <div class="booking-field">
                    <label for="customer-email">Email</label>
                    <input id="customer-email" name="email" type="email" autocomplete="email" placeholder="nama@email.com">
                </div>
                <div class="booking-field full">
                    <label for="booking-notes">Catatan (opsional)</label>
                    <textarea id="booking-notes" name="notes" placeholder="Contoh: butuh sewa raket"></textarea>
                </div>
            </div>
            <p class="booking-note">Slot akan ditahan selama 30 menit. Setelah booking dibuat, lakukan pembayaran dan unggah bukti bayar sebelum batas waktu.</p>
            <div class="booking-dialog-actions">
                <button class="dialog-cancel" type="button" data-close-dialog>Batal</button>
                <button class="button booking-submit" type="submit">Lanjutkan booking <span aria-hidden="true">↗</span></button>
            </div>
        </form>
    </dialog>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const searchForm = document.getElementById('search-form');
        const locationSelect = document.getElementById('location');
        const timeSelect = document.getElementById('play-time');
        const venueCards = [...document.querySelectorAll('.venue-card')];
        const emptyResults = document.getElementById('empty-results');
        const resultsCaption = document.getElementById('results-caption');
        const toast = document.getElementById('toast');
        const themeToggle = document.getElementById('theme-toggle');
        const themeColor = document.querySelector('meta[name="theme-color"]');
        const bookingDialog = document.getElementById('booking-dialog');
        const bookingForm = document.getElementById('booking-form');
        const searchDate = document.getElementById('play-date');
        const bookingTime = document.getElementById('play-time');
        const bookingLocation = document.getElementById('location');
        const bookingDateField = document.getElementById('booking-date');
        const bookingSlotGrid = document.getElementById('booking-time-slots');
        const bookingSlotInput = document.getElementById('booking-slot-time');
        const bookingAvailability = document.getElementById('booking-availability');
        const bookingSubmit = bookingForm.querySelector('[type="submit"]');
        const bookingSlotTimes = ['07:00', '09:00', '11:00', '13:00', '15:00', '17:00', '19:00', '21:00'];
        const bookingCourtInput = document.getElementById('booking-court-ulid');
        let selectedBookingVenue = null;
        let selectedBookingTime = '';
        let availabilityRequest = 0;
        let activeType = 'all';
        let toastTimer;

        function setTheme(theme, savePreference = false) {
            document.documentElement.dataset.theme = theme;
            const isDark = theme === 'dark';
            themeToggle.setAttribute('aria-pressed', String(isDark));
            themeToggle.setAttribute('aria-label', `Aktifkan mode ${isDark ? 'terang' : 'gelap'}`);
            themeToggle.title = `Aktifkan mode ${isDark ? 'terang' : 'gelap'}`;
            themeColor.content = isDark ? '#0b110e' : '#f7f8f4';

            if (savePreference) localStorage.setItem('rally-theme', theme);
        }

        setTheme(document.documentElement.dataset.theme);
        themeToggle.addEventListener('click', () => {
            const nextTheme = document.documentElement.dataset.theme === 'dark' ? 'light' : 'dark';
            setTheme(nextTheme, true);
        });

        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (event) => {
            if (!localStorage.getItem('rally-theme')) setTheme(event.matches ? 'dark' : 'light');
        });

        const today = new Date();
        const localToday = new Date(today.getTime() - today.getTimezoneOffset() * 60000).toISOString().slice(0, 10);
        searchDate.min = localToday;
        searchDate.value = localToday;
        bookingDateField.min = localToday;

        function currentDateKey() {
            return bookingDateField.value;
        }

        function updateBookingSummary() {
            const date = currentDateKey();
            const dateLabel = date
                ? new Intl.DateTimeFormat('id-ID', { dateStyle: 'medium' }).format(new Date(`${date}T00:00:00`))
                : 'Pilih tanggal';
            const timeLabel = selectedBookingTime ? `Pukul ${selectedBookingTime}` : 'Pilih jam';
            document.getElementById('booking-schedule').textContent = `${dateLabel} · ${timeLabel}`;
        }

        async function renderBookingSlots() {
            const requestId = ++availabilityRequest;
            selectedBookingTime = '';
            bookingSlotInput.value = '';
            updateBookingSummary();

            if (!selectedBookingVenue || !currentDateKey()) {
                bookingSlotGrid.replaceChildren();
                bookingAvailability.textContent = 'Pilih tanggal untuk melihat slot yang tersedia.';
                bookingAvailability.classList.remove('error');
                bookingSubmit.disabled = true;
                return;
            }

            bookingSlotGrid.replaceChildren();
            bookingAvailability.classList.remove('error');
            bookingAvailability.textContent = 'Memuat jadwal...';
            bookingSubmit.disabled = true;
            let bookedSlots;
            try {
                const query = new URLSearchParams({ from: currentDateKey(), to: currentDateKey() });
                const response = await fetch(`{{ route('booking.availability') }}?${query}`, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Jadwal tidak dapat dimuat.');
                bookedSlots = (await response.json()).bookings;
            } catch (error) {
                console.error('Jadwal booking tidak dapat dimuat.', error);
                if (requestId !== availabilityRequest) return;
                bookingAvailability.textContent = 'Jadwal gagal dimuat. Periksa koneksi lalu coba lagi.';
                bookingAvailability.classList.add('error');
                return;
            }
            if (requestId !== availabilityRequest) return;

            const matchingSlots = bookingSlotTimes.filter((time) => {
                const hour = Number(time.slice(0, 2));
                return bookingTime.value === 'all'
                    || (bookingTime.value === 'pagi' && hour < 11)
                    || (bookingTime.value === 'siang' && hour >= 11 && hour < 16)
                    || (bookingTime.value === 'malam' && hour >= 16);
            });
            let availableCount = 0;

            matchingSlots.forEach((time) => {
                const alreadyBooked = bookedSlots.some((slot) => (
                    slot.court === selectedBookingVenue && slot.date === currentDateKey() && slot.time === time
                ));
                const isPastToday = currentDateKey() === localToday && new Date(`${currentDateKey()}T${time}:00`) <= new Date();
                const available = !alreadyBooked && !isPastToday;
                if (available) availableCount += 1;
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'booking-time-slot';
                button.textContent = time;
                button.dataset.slot = time;
                button.disabled = !available;
                button.setAttribute('aria-label', `${time}, ${available ? 'tersedia' : 'tidak tersedia'}`);
                if (!available) button.title = alreadyBooked ? 'Slot ini sudah dipesan.' : 'Waktu ini sudah lewat.';
                button.addEventListener('click', () => {
                    selectedBookingTime = time;
                    bookingSlotInput.value = time;
                    bookingSlotGrid.querySelectorAll('.booking-time-slot').forEach((slotButton) => {
                        slotButton.classList.toggle('selected', slotButton === button);
                        slotButton.setAttribute('aria-pressed', String(slotButton === button));
                    });
                    bookingAvailability.textContent = `${availableCount} slot tersedia untuk tanggal ini.`;
                    bookingSubmit.disabled = false;
                    updateBookingSummary();
                });
                bookingSlotGrid.append(button);
            });

            bookingAvailability.textContent = availableCount
                ? `${availableCount} slot tersedia untuk tanggal ini.`
                : 'Tidak ada slot tersedia. Silakan pilih tanggal lain.';
            bookingSubmit.disabled = true;
        }

        bookingDateField.addEventListener('change', renderBookingSlots);
        bookingTime.addEventListener('change', () => {
            if (bookingDialog.open) renderBookingSlots();
        });

        async function openBooking(button, preferredDate = searchDate.value, preferredSlot = '') {
            selectedBookingVenue = button.dataset.venueUlid;
            bookingCourtInput.value = selectedBookingVenue;
            document.getElementById('booking-venue').textContent = button.dataset.venue;
            bookingDateField.value = preferredDate && preferredDate >= localToday ? preferredDate : localToday;
            bookingDialog.showModal();
            await renderBookingSlots();
            document.getElementById('customer-name').focus();

            if (preferredSlot) {
                const slotButton = [...bookingSlotGrid.querySelectorAll('.booking-time-slot')]
                    .find((slot) => slot.dataset.slot === preferredSlot && !slot.disabled);
                if (slotButton) slotButton.click();
                else {
                    bookingAvailability.textContent = 'Slot dari kalender sudah tidak tersedia. Pilih jam lain.';
                    bookingAvailability.classList.add('error');
                }
            }
        }

        function updateVenues() {
            let visible = 0;
            venueCards.forEach((card) => {
                const matchesType = activeType === 'all' || card.dataset.type === activeType;
                const matchesLocation = locationSelect.value === 'all' || card.dataset.location === locationSelect.value;
                const matchesTime = timeSelect.value === 'all' || card.dataset.time.split(' ').includes(timeSelect.value);
                const show = matchesType && matchesLocation && matchesTime;
                const wasHidden = card.hidden;
                card.hidden = !show;
                if (show) {
                    visible += 1;
                    if (wasHidden) {
                        card.classList.remove('is-filtering-in');
                        void card.offsetWidth;
                        card.classList.add('is-filtering-in');
                    }
                } else {
                    card.classList.remove('is-filtering-in');
                }
            });
            emptyResults.style.display = visible ? 'none' : 'block';
            resultsCaption.textContent = `${visible} lapangan cocok untuk pilihanmu.`;
        }

        searchForm.addEventListener('submit', (event) => {
            event.preventDefault();
            updateVenues();
            document.getElementById('lapangan').scrollIntoView({ behavior: 'smooth' });
        });

        document.querySelectorAll('.filter-chip').forEach((button) => {
            button.addEventListener('click', () => {
                document.querySelector('.filter-chip.active').classList.remove('active');
                button.classList.add('active');
                activeType = button.dataset.filter;
                updateVenues();
            });
        });

        locationSelect.addEventListener('change', updateVenues);
        timeSelect.addEventListener('change', updateVenues);

        function showToast(message) {
            toast.textContent = message;
            toast.classList.add('show');
            window.clearTimeout(toastTimer);
            toastTimer = window.setTimeout(() => toast.classList.remove('show'), 3000);
        }

        document.querySelectorAll('.book-button').forEach((button) => {
            button.addEventListener('click', () => {
                if (!button.disabled) openBooking(button, searchDate.value, button.dataset.selectedSlot || '');
            });
        });

        document.querySelectorAll('[data-close-dialog]').forEach((button) => {
            button.addEventListener('click', () => bookingDialog.close());
        });
        bookingDialog.addEventListener('click', (event) => {
            if (event.target === bookingDialog) bookingDialog.close();
        });
        bookingForm.addEventListener('submit', (event) => {
            if (!currentDateKey() || !selectedBookingTime) {
                event.preventDefault();
                bookingAvailability.textContent = 'Pilih tanggal dan jam yang masih tersedia sebelum melanjutkan.';
                bookingAvailability.classList.add('error');
                return;
            }
            bookingSubmit.disabled = true;
            bookingSubmit.textContent = 'Membuat booking...';
        });

        const scheduleParams = new URLSearchParams(window.location.search);
        const scheduledVenue = scheduleParams.get('venue');
        const scheduledCourt = scheduleParams.get('court');
        const scheduledDate = scheduleParams.get('date');
        const scheduledSlot = scheduleParams.get('slot');
        const scheduledLocation = scheduleParams.get('location');
        const datePattern = /^\d{4}-\d{2}-\d{2}$/;
        const targetBookButton = [...document.querySelectorAll('.book-button')]
            .find((button) => scheduledCourt
                ? button.dataset.venueUlid === scheduledCourt
                : button.dataset.venue === scheduledVenue);

        if (
            targetBookButton
            && datePattern.test(scheduledDate || '')
            && scheduledDate >= localToday
            && bookingSlotTimes.includes(scheduledSlot)
            && [...bookingLocation.options].some((option) => option.value === scheduledLocation)
        ) {
            const hour = Number(scheduledSlot.slice(0, 2));
            searchDate.value = scheduledDate;
            bookingLocation.value = scheduledLocation;
            bookingTime.value = hour < 11 ? 'pagi' : hour < 16 ? 'siang' : 'malam';
            targetBookButton.dataset.selectedSlot = scheduledSlot;
            updateVenues();
            window.setTimeout(() => targetBookButton.click(), 250);
        }

        document.querySelectorAll('.favorite').forEach((button) => {
            button.addEventListener('click', () => {
                const isSaved = button.dataset.saved === 'true';
                button.dataset.saved = String(!isSaved);
                button.setAttribute('aria-pressed', String(!isSaved));
                button.style.color = isSaved ? '' : '#b14a4a';
                button.classList.toggle('is-saved', !isSaved);
                showToast(isSaved ? 'Dihapus dari favorit.' : 'Ditambahkan ke favorit.');
            });
        });

        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        const siteHeader = document.querySelector('.site-header');
        const revealElements = [
            document.querySelector('.hero-content'),
            document.querySelector('.search-panel'),
            document.querySelector('.section-heading'),
            document.querySelector('.filters'),
            ...venueCards,
            ...document.querySelectorAll('.benefit'),
            document.querySelector('.cta'),
        ].filter(Boolean);

        if (!reducedMotion) {
            document.documentElement.classList.add('js-motion');

            if ('IntersectionObserver' in window) {
                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('is-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.12, rootMargin: '0px 0px -35px 0px' });

                revealElements.forEach((element, index) => {
                    element.classList.add('reveal');
                    element.style.setProperty('--reveal-delay', `${Math.min(index % 3, 2) * 90}ms`);
                    revealObserver.observe(element);
                });
            }

            if (window.matchMedia('(pointer: fine)').matches) {
                venueCards.forEach((card) => {
                    card.addEventListener('pointermove', (event) => {
                        const bounds = card.getBoundingClientRect();
                        const x = (event.clientX - bounds.left) / bounds.width - 0.5;
                        const y = (event.clientY - bounds.top) / bounds.height - 0.5;
                        card.style.setProperty('--tilt-x', `${-y * 3}deg`);
                        card.style.setProperty('--tilt-y', `${x * 3}deg`);
                    });
                    card.addEventListener('pointerleave', () => {
                        card.style.setProperty('--tilt-x', '0deg');
                        card.style.setProperty('--tilt-y', '0deg');
                    });
                });

                const hero = document.querySelector('.hero');
                hero.addEventListener('pointermove', (event) => {
                    const bounds = hero.getBoundingClientRect();
                    const shift = ((event.clientX - bounds.left) / bounds.width - 0.5) * 12;
                    hero.style.setProperty('--hero-shift', `${shift}px`);
                });
                hero.addEventListener('pointerleave', () => hero.style.setProperty('--hero-shift', '0px'));
            }
        }

        const updateHeader = () => siteHeader.classList.toggle('is-scrolled', window.scrollY > 12);
        updateHeader();
        window.addEventListener('scroll', updateHeader, { passive: true });

        venueCards.forEach((card) => {
            card.addEventListener('animationend', (event) => {
                if (event.animationName === 'card-in') card.classList.remove('is-filtering-in');
            });
        });

        const mapElement = document.getElementById('venue-map');
        if (window.L) {
            const venueLocations = @json($venueMapPoints);
            const defaultCenter = [-6.2, 106.8167];
            const venueMap = L.map(mapElement, { scrollWheelZoom: false }).setView(defaultCenter, 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            }).addTo(venueMap);
            const venueBounds = [];
            venueLocations.forEach((venue) => {
                const coordinates = [venue.latitude, venue.longitude];
                venueBounds.push(coordinates);
                const popup = document.createElement('div');
                popup.textContent = `${venue.name} · ${venue.address}`;
                L.marker(coordinates).addTo(venueMap).bindPopup(popup);
            });
            if (venueBounds.length === 1) venueMap.setView(venueBounds[0], 14);
            if (venueBounds.length > 1) venueMap.fitBounds(venueBounds, { padding: [20, 20], maxZoom: 14 });
            window.setTimeout(() => venueMap.invalidateSize(), 100);
        } else {
            console.error('Leaflet gagal dimuat; peta lokasi tidak tersedia.');
            mapElement.textContent = 'Peta tidak dapat dimuat. Silakan coba lagi.';
        }
    </script>
</body>
</html>

@extends('layouts.app')

@section('title', 'Kalender Booking')

@section('content')
    @php
        $statusLabels = [
            'pending_payment' => 'Menunggu pembayaran',
            'pending_verification' => 'Menunggu verifikasi',
            'confirmed' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            'canceled' => 'Dibatalkan',
            'expired' => 'Kedaluwarsa',
        ];
        $statusClasses = [
            'pending_payment' => 'booking-event--payment',
            'pending_verification' => 'booking-event--verification',
            'confirmed' => 'booking-event--confirmed',
            'rejected' => 'booking-event--rejected',
            'cancelled' => 'booking-event--cancelled',
            'canceled' => 'booking-event--cancelled',
            'expired' => 'booking-event--expired',
        ];
        $calendarDays = [];
        for ($date = $calendarStart->copy(); $date->lte($calendarEnd); $date->addDay()) {
            $calendarDays[] = $date->copy();
        }
        $initialDate = now()->format('Y-m');
        $selectedDate = $month->format('Y-m') === $initialDate ? now()->toDateString() : $month->toDateString();
    @endphp

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-5">
        <div>
            <h4 class="mb-1">Kalender Booking</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Kalender Booking</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('bookings.index') }}" class="btn btn-white"><i class="ti ti-list me-1"></i> Daftar Booking</a>
            <a href="{{ route('bookings.create') }}" class="btn btn-primary"><i class="ti ti-calendar-plus me-1"></i> Buat Booking</a>
        </div>
    </div>

    <div class="card card-lg mb-4">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('bookings.calendar', ['month' => $previousMonth]) }}" class="btn btn-white btn-icon" aria-label="Bulan sebelumnya"><i class="ti ti-chevron-left"></i></a>
                <h5 class="mb-0 mx-2 text-capitalize">{{ $monthLabel }}</h5>
                <a href="{{ route('bookings.calendar', ['month' => $nextMonth]) }}" class="btn btn-white btn-icon" aria-label="Bulan berikutnya"><i class="ti ti-chevron-right"></i></a>
                <a href="{{ route('bookings.calendar') }}" class="btn btn-white ms-1">Hari ini</a>
            </div>
            <form action="{{ route('bookings.calendar') }}" method="GET" class="d-flex align-items-center gap-2">
                <label class="form-label mb-0" for="calendar-month">Pilih bulan</label>
                <input class="form-control" id="calendar-month" name="month" type="month" value="{{ $month->format('Y-m') }}" onchange="this.form.submit()">
            </form>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card card-lg">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div>
                            <h5 class="mb-1">Jadwal bulan {{ $monthLabel }}</h5>
                            <div class="text-muted small">{{ $bookings->count() }} booking bulan ini</div>
                        </div>
                        <select class="form-select w-auto" id="booking-status-filter" aria-label="Filter status booking">
                            <option value="all">Semua status</option>
                            <option value="pending_payment">Menunggu pembayaran</option>
                            <option value="pending_verification">Menunggu verifikasi</option>
                            <option value="confirmed">Disetujui</option>
                            <option value="rejected">Ditolak</option>
                            <option value="cancelled">Dibatalkan</option>
                            <option value="expired">Kedaluwarsa</option>
                        </select>
                    </div>

                    <div class="booking-calendar" role="grid" aria-label="Kalender booking bulan {{ $monthLabel }}">
                        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
                            <div class="booking-calendar__weekday" role="columnheader">{{ $weekday }}</div>
                        @endforeach

                        @foreach ($calendarDays as $date)
                            @php
                                $dateKey = $date->toDateString();
                                $dayBookings = $bookingsByDate->get($dateKey, collect());
                            @endphp
                            <button
                                type="button"
                                class="booking-calendar__day {{ $date->month !== $month->month ? 'booking-calendar__day--outside' : '' }} {{ $dateKey === $selectedDate ? 'booking-calendar__day--selected' : '' }} {{ $dateKey === now()->toDateString() ? 'booking-calendar__day--today' : '' }}"
                                @disabled($date->month !== $month->month)
                                data-date="{{ $dateKey }}"
                                aria-label="{{ $date->locale('id')->translatedFormat('j F Y') }}, {{ $dayBookings->count() }} booking"
                                aria-pressed="{{ $dateKey === $selectedDate ? 'true' : 'false' }}"
                                role="gridcell">
                                <span class="booking-calendar__number">{{ $date->day }}</span>
                                <span class="booking-calendar__events">
                                    @foreach ($dayBookings as $booking)
                                        <span class="booking-event {{ $statusClasses[$booking->status] ?? 'booking-event--other' }}"
                                            data-status="{{ in_array($booking->status, ['canceled', 'cancelled'], true) ? 'cancelled' : $booking->status }}"
                                            title="{{ substr($booking->booking_time, 0, 5) }}–{{ \Illuminate\Support\Carbon::parse($booking->booking_time)->addHours($booking->duration_hours)->format('H:i') }} ({{ $booking->duration_hours }} jam) · {{ $booking->customer_name }} · {{ $statusLabels[$booking->status] ?? $booking->status }}">
                                            <span class="booking-event__time">{{ substr($booking->booking_time, 0, 5) }} ({{ $booking->duration_hours }}j)</span>
                                            <span class="booking-event__name">{{ $booking->customer_name }}</span>
                                        </span>
                                    @endforeach
                                    @if ($dayBookings->count() > 3)
                                        <span class="booking-calendar__more">+{{ $dayBookings->count() - 3 }} booking lainnya</span>
                                    @endif
                                </span>
                            </button>
                        @endforeach
                    </div>

                    <div class="d-flex flex-wrap gap-3 mt-4 small text-muted" aria-label="Keterangan status">
                        <span><i class="booking-legend booking-legend--confirmed"></i> Disetujui</span>
                        <span><i class="booking-legend booking-legend--verification"></i> Menunggu verifikasi</span>
                        <span><i class="booking-legend booking-legend--payment"></i> Menunggu pembayaran</span>
                        <span><i class="booking-legend booking-legend--rejected"></i> Ditolak</span>
                        <span><i class="booking-legend booking-legend--cancelled"></i> Dibatalkan</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card card-lg h-100">
                <div class="card-header">
                    <h5 class="mb-1" id="selected-date-title"></h5>
                    <span class="text-muted small" id="selected-date-count"></span>
                </div>
                <div class="card-body" id="selected-date-bookings" aria-live="polite"></div>
            </div>
        </div>
    </div>

    <template id="booking-detail-template">
        <article class="booking-detail">
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div>
                    <div class="fw-semibold booking-detail__customer"></div>
                    <code class="small booking-detail__number"></code>
                </div>
                <span class="badge booking-detail__status"></span>
            </div>
            <div class="booking-detail__meta text-muted small mt-3"></div>
            <div class="booking-detail__amount fw-semibold mt-2"></div>
        </article>
    </template>
@endsection

@push('styles')
    <style>
        .booking-calendar {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            border-top: 1px solid var(--bs-border-color);
            border-left: 1px solid var(--bs-border-color);
            border-radius: .5rem;
            overflow: hidden;
        }
        .booking-calendar__weekday {
            padding: .65rem .45rem;
            background: var(--bs-tertiary-bg);
            color: var(--bs-secondary-color);
            text-align: center;
            font-size: .75rem;
            font-weight: 600;
        }
        .booking-calendar__day {
            min-width: 0;
            min-height: 112px;
            padding: .55rem;
            border: 0;
            border-right: 1px solid var(--bs-border-color);
            border-bottom: 1px solid var(--bs-border-color);
            background: var(--bs-body-bg);
            color: var(--bs-body-color);
            text-align: left;
            vertical-align: top;
            cursor: pointer;
            transition: background-color .15s, box-shadow .15s;
        }
        .booking-calendar__day:hover { background: var(--bs-tertiary-bg); }
        .booking-calendar__day--outside { background: var(--bs-tertiary-bg); color: var(--bs-secondary-color); opacity: .7; }
        .booking-calendar__day--selected { box-shadow: inset 0 0 0 2px var(--bs-primary); }
        .booking-calendar__day--today .booking-calendar__number {
            display: inline-grid;
            min-width: 1.65rem;
            min-height: 1.65rem;
            place-items: center;
            border-radius: 50%;
            background: var(--bs-primary);
            color: #fff;
        }
        .booking-calendar__number { display: inline-block; margin-bottom: .35rem; font-size: .8rem; font-weight: 600; }
        .booking-calendar__events { display: grid; gap: .25rem; }
        .booking-event {
            display: flex;
            gap: .25rem;
            overflow: hidden;
            padding: .2rem .3rem;
            border-radius: .25rem;
            font-size: .66rem;
            line-height: 1.35;
        }
        .booking-event__time { flex: 0 0 auto; font-weight: 700; }
        .booking-event__name { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .booking-event--confirmed, .booking-legend--confirmed { background: #d7f0df; color: #176b36; }
        .booking-event--verification, .booking-legend--verification { background: #fff0c2; color: #805c00; }
        .booking-event--payment, .booking-legend--payment { background: #dceaff; color: #2054a3; }
        .booking-event--rejected, .booking-legend--rejected { background: #f9d9d9; color: #a32626; }
        .booking-event--cancelled, .booking-legend--cancelled { background: #e7e2ee; color: #604780; }
        .booking-event--expired, .booking-event--other { background: #e8eaed; color: #4d5560; }
        .booking-calendar__more { color: var(--bs-secondary-color); font-size: .65rem; }
        .booking-legend { display: inline-block; width: .65rem; height: .65rem; margin-right: .25rem; border-radius: 50%; vertical-align: -1px; }
        .booking-detail { padding: .9rem 0; border-bottom: 1px solid var(--bs-border-color); }
        .booking-detail:first-child { padding-top: 0; }
        .booking-detail:last-child { padding-bottom: 0; border-bottom: 0; }
        .booking-detail__status { white-space: normal; }
        .booking-empty { padding: 2rem 1rem; color: var(--bs-secondary-color); text-align: center; }
        .booking-calendar__day.is-filtered-empty .booking-calendar__more { display: none; }
        @media (max-width: 767.98px) {
            .booking-calendar__day { min-height: 82px; padding: .3rem; }
            .booking-calendar__weekday { padding: .5rem .15rem; font-size: .65rem; }
            .booking-event { display: block; padding: .15rem .2rem; font-size: .6rem; }
            .booking-event__time { display: none; }
            .booking-calendar__more { font-size: .58rem; }
        }
        @media (max-width: 420px) {
            .booking-calendar__day { min-height: 65px; padding: .2rem; }
            .booking-calendar__events { gap: .15rem; }
            .booking-event__name { display: block; }
            .booking-calendar__more { font-size: .52rem; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (() => {
            const bookings = @json($calendarEvents);
            const statusLabels = @json($statusLabels);
            const statusClasses = @json($statusClasses);
            const filter = document.getElementById('booking-status-filter');
            const dateTitle = document.getElementById('selected-date-title');
            const dateCount = document.getElementById('selected-date-count');
            const detailList = document.getElementById('selected-date-bookings');
            const template = document.getElementById('booking-detail-template');
            let selectedDate = @json($selectedDate);

            const formatDate = (date) => new Intl.DateTimeFormat('id-ID', {
                weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
            }).format(new Date(`${date}T00:00:00`));

            const statusFilterMatches = (status) => {
                const normalizedStatus = ['canceled', 'cancelled'].includes(status) ? 'cancelled' : status;
                return filter.value === 'all' || normalizedStatus === filter.value;
            };

            const renderDate = () => {
                const visibleBookings = bookings.filter((booking) =>
                    booking.date === selectedDate && statusFilterMatches(booking.status)
                );
                dateTitle.textContent = formatDate(selectedDate);
                dateCount.textContent = `${visibleBookings.length} booking`;
                detailList.replaceChildren();

                if (visibleBookings.length === 0) {
                    const empty = document.createElement('div');
                    empty.className = 'booking-empty';
                    empty.textContent = filter.value === 'all'
                        ? 'Tidak ada booking pada tanggal ini.'
                        : 'Tidak ada booking dengan status tersebut pada tanggal ini.';
                    detailList.append(empty);
                    return;
                }

                visibleBookings.forEach((booking) => {
                    const card = template.content.firstElementChild.cloneNode(true);
                    const status = ['canceled', 'cancelled'].includes(booking.status) ? 'cancelled' : booking.status;
                    card.querySelector('.booking-detail__customer').textContent = booking.customer;
                    card.querySelector('.booking-detail__number').textContent = booking.number;
                    card.querySelector('.booking-detail__status').textContent = statusLabels[booking.status] || booking.status;
                    card.querySelector('.booking-detail__status').classList.add(
                        statusClasses[status] === 'booking-event--confirmed' ? 'bg-success-subtle' :
                        statusClasses[status] === 'booking-event--rejected' ? 'bg-danger-subtle' :
                        statusClasses[status] === 'booking-event--cancelled' ? 'bg-secondary-subtle' :
                        statusClasses[status] === 'booking-event--payment' ? 'bg-primary-subtle' :
                        'bg-warning-subtle'
                    );
                    const paymentLabels = { cash: 'Tunai', qris: 'QRIS', bank_transfer: 'Transfer bank' };
                    const details = [
                        `${booking.time}–${booking.endTime} (${booking.durationHours} jam) · ${booking.court} · ${booking.venue}`,
                        booking.source === 'staff' ? 'Booking oleh staf' : 'Booking website',
                    ];
                    if (booking.paymentMethod) details.push(paymentLabels[booking.paymentMethod] || booking.paymentMethod);
                    card.querySelector('.booking-detail__meta').textContent = details.join(' — ');
                    card.querySelector('.booking-detail__amount').textContent = new Intl.NumberFormat('id-ID', {
                        style: 'currency', currency: 'IDR', maximumFractionDigits: 0,
                    }).format(booking.amount);
                    detailList.append(card);
                });
            };

            const filterCalendar = () => {
                document.querySelectorAll('.booking-calendar__day').forEach((day) => {
                    let visibleCount = 0;
                    const events = [...day.querySelectorAll('.booking-event')];
                    events.forEach((event) => {
                        const visible = filter.value === 'all' || event.dataset.status === filter.value;
                        event.hidden = !visible;
                        if (visible) visibleCount += 1;
                        event.style.display = visible && visibleCount <= 3 ? '' : 'none';
                    });
                    let more = day.querySelector('.booking-calendar__more');
                    if (visibleCount > 3 && !more) {
                        more = document.createElement('span');
                        more.className = 'booking-calendar__more';
                        day.querySelector('.booking-calendar__events').append(more);
                    }
                    if (more) {
                        more.hidden = visibleCount <= 3;
                        more.textContent = `+${visibleCount - 3} booking lainnya`;
                    }
                    day.classList.toggle('is-filtered-empty', visibleCount === 0);
                });
                renderDate();
            };

            document.querySelectorAll('.booking-calendar__day').forEach((day) => {
                day.addEventListener('click', () => {
                    selectedDate = day.dataset.date;
                    document.querySelectorAll('.booking-calendar__day').forEach((item) => {
                        const selected = item === day;
                        item.classList.toggle('booking-calendar__day--selected', selected);
                        item.setAttribute('aria-pressed', String(selected));
                    });
                    renderDate();
                });
            });
            filter.addEventListener('change', filterCalendar);
            filterCalendar();
        })();
    </script>
@endpush

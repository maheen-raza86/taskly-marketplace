@extends('layouts.app')

@section('title', 'Provider Dashboard')

@section('content')

@php
    $user = auth()->user();
    // Load availability inline — controller doesn't pass it
    $availability = \App\Models\Availability::where('provider_id', $user->id)
        ->orderByRaw("CASE day_of_week WHEN 'monday' THEN 1 WHEN 'tuesday' THEN 2 WHEN 'wednesday' THEN 3 WHEN 'thursday' THEN 4 WHEN 'friday' THEN 5 WHEN 'saturday' THEN 6 ELSE 7 END")
        ->get()
        ->keyBy('day_of_week');

    $categories = \App\Models\Category::orderBy('name')->get();

    // Stats
    $totalServices   = $services->count();
    $pendingCount    = $pendingBookings->count();
    $confirmedCount  = $confirmedBookings->count();
    $avgRating       = $profile ? number_format($profile->avg_rating, 1) : '—';
    $totalReviews    = $profile ? $profile->total_reviews : 0;

    // Which tab to open (after form errors, stay on same tab)
    $activeTab = session('active_tab', request('tab', 'overview'));

    // Days of week for availability form
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
@endphp

<div class="page-wrap">

    {{-- ── Header ──────────────────────────────────────────── --}}
    <div class="page-header">
        <h1>Provider Dashboard</h1>
        <a href="{{ route('provider.profile', $user) }}" class="btn-ghost" style="font-size:.8rem;padding:.35rem .85rem;">
            View Public Profile →
        </a>
    </div>

    {{-- ── Approval banner ─────────────────────────────────── --}}
    @if(!$profile || !$profile->is_approved)
        <div class="approval-banner">
            <span class="icon">⏳</span>
            <span>Your account is <strong>pending admin approval</strong>. Services you add won't be visible to customers until approved.</span>
        </div>
    @endif

    {{-- ── Flash messages ──────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert-success" style="margin-bottom:1.25rem;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-errors" style="margin-bottom:1.25rem;">
            <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- ── Tab navigation ──────────────────────────────────── --}}
    <div class="tab-nav" id="tabNav">
        <button class="tab-btn {{ $activeTab === 'overview'      ? 'active' : '' }}" data-tab="overview">
            Overview
        </button>
        <button class="tab-btn {{ $activeTab === 'bookings'      ? 'active' : '' }}" data-tab="bookings">
            Bookings
            @if($pendingCount > 0)
                <span style="background:#ef4444;color:#fff;font-size:.65rem;font-weight:700;padding:.1rem .45rem;border-radius:20px;margin-left:.3rem;">
                    {{ $pendingCount }}
                </span>
            @endif
        </button>
        <button class="tab-btn {{ $activeTab === 'services'      ? 'active' : '' }}" data-tab="services">
            My Services
        </button>
        <button class="tab-btn {{ $activeTab === 'availability'  ? 'active' : '' }}" data-tab="availability">
            Availability
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         TAB: OVERVIEW
    ═══════════════════════════════════════════════════════ --}}
    <div class="tab-panel {{ $activeTab === 'overview' ? 'active' : '' }}" id="tab-overview">

        {{-- Stats --}}
        <div class="stats-row">
            <div class="stat-card accent-amber">
                <span class="stat-icon">📋</span>
                <span class="stat-value">{{ $pendingCount }}</span>
                <span class="stat-label">Pending bookings</span>
            </div>
            <div class="stat-card accent-green">
                <span class="stat-icon">✅</span>
                <span class="stat-value">{{ $confirmedCount }}</span>
                <span class="stat-label">Confirmed bookings</span>
            </div>
            <div class="stat-card accent-blue">
                <span class="stat-icon">🛠</span>
                <span class="stat-value">{{ $totalServices }}</span>
                <span class="stat-label">Active services</span>
            </div>
            <div class="stat-card accent-purple">
                <span class="stat-icon">⭐</span>
                <span class="stat-value">{{ $avgRating }}</span>
                <span class="stat-label">Avg rating ({{ $totalReviews }} reviews)</span>
            </div>
        </div>

        {{-- Recent pending bookings --}}
        @if($pendingBookings->count())
            <div class="card">
                <div class="section-hdr">
                    <h2>Pending Requests</h2>
                    <button class="tab-btn" onclick="openTab('bookings')"
                        style="font-size:.8rem;padding:.3rem .75rem;color:#4f46e5;border:1.5px solid #e0e7ff;border-radius:6px;">
                        View all →
                    </button>
                </div>
                <div class="tbl-wrap">
                    <table class="data-tbl">
                        <thead>
                            <tr>
                                <th>Customer</th><th>Service</th>
                                <th>Date</th><th>Time</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingBookings->take(5) as $bk)
                            <tr>
                                <td>
                                    <div style="font-weight:600;">{{ $bk->customer->name }}</div>
                                    @if($bk->notes)
                                        <div style="font-size:.75rem;color:#6b7280;margin-top:.15rem;">
                                            {{ Str::limit($bk->notes, 40) }}
                                        </div>
                                    @endif
                                </td>
                                <td>{{ $bk->service->title }}</td>
                                <td style="white-space:nowrap;">
                                    {{ $bk->booking_date->format('D, d M Y') }}
                                </td>
                                <td style="white-space:nowrap;">
                                    {{ \Carbon\Carbon::parse($bk->time_slot)->format('g:i A') }}
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <form action="{{ route('provider.bookings.updateStatus', $bk) }}"
                                              method="POST" style="display:contents;">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="btn-sm btn-confirm">Confirm</button>
                                        </form>
                                        <form action="{{ route('provider.bookings.updateStatus', $bk) }}"
                                              method="POST" style="display:contents;">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn-sm btn-cancel">Decline</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="card">
                <div style="text-align:center;padding:1.5rem;color:#9ca3af;font-size:.875rem;">
                    🎉 No pending bookings right now.
                </div>
            </div>
        @endif

        {{-- Quick services preview --}}
        @if($services->count())
            <div class="card" style="margin-top:1.5rem;">
                <div class="section-hdr">
                    <h2>My Services ({{ $totalServices }})</h2>
                    <button class="tab-btn" onclick="openTab('services')"
                        style="font-size:.8rem;padding:.3rem .75rem;color:#4f46e5;border:1.5px solid #e0e7ff;border-radius:6px;">
                        Manage →
                    </button>
                </div>
                <div class="svc-list">
                    @foreach($services->take(3) as $svc)
                        <div class="svc-item">
                            <div class="svc-info">
                                <div class="svc-name">{{ $svc->title }}</div>
                                <div class="svc-meta">
                                    <span>{{ $svc->category->name }}</span>
                                    <span>₨{{ number_format($svc->price, 0) }} / {{ $svc->price_type }}</span>
                                    @if(!$svc->is_active)
                                        <span class="inactive-tag">Inactive</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @if($services->count() > 3)
                        <p style="font-size:.8rem;color:#6b7280;text-align:center;padding:.5rem;">
                            +{{ $services->count() - 3 }} more
                        </p>
                    @endif
                </div>
            </div>
        @endif

    </div>{{-- /tab-overview --}}

    {{-- ═══════════════════════════════════════════════════════
         TAB: BOOKINGS
    ═══════════════════════════════════════════════════════ --}}
    <div class="tab-panel {{ $activeTab === 'bookings' ? 'active' : '' }}" id="tab-bookings">

        {{-- Pending bookings --}}
        <div class="card">
            <div class="section-hdr">
                <h2>Pending Requests
                    @if($pendingCount)
                        <span style="font-size:.75rem;font-weight:500;color:#92400e;background:#fef3c7;padding:.15rem .5rem;border-radius:20px;margin-left:.5rem;">
                            {{ $pendingCount }} waiting
                        </span>
                    @endif
                </h2>
            </div>

            @if($pendingBookings->count())
                <div class="tbl-wrap">
                    <table class="data-tbl">
                        <thead>
                            <tr>
                                <th>Customer</th><th>Service</th>
                                <th>Date</th><th>Time</th><th>Notes</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingBookings as $bk)
                            <tr>
                                <td style="font-weight:600;">{{ $bk->customer->name }}</td>
                                <td>{{ $bk->service->title }}</td>
                                <td style="white-space:nowrap;">{{ $bk->booking_date->format('d M Y') }}</td>
                                <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($bk->time_slot)->format('g:i A') }}</td>
                                <td style="color:#6b7280;font-size:.8rem;">
                                    {{ $bk->notes ? Str::limit($bk->notes, 50) : '—' }}
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <form action="{{ route('provider.bookings.updateStatus', $bk) }}"
                                              method="POST" style="display:contents;">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="confirmed">
                                            <button type="submit" class="btn-sm btn-confirm">✓ Confirm</button>
                                        </form>
                                        <form action="{{ route('provider.bookings.updateStatus', $bk) }}"
                                              method="POST" style="display:contents;">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="status" value="cancelled">
                                            <button type="submit" class="btn-sm btn-cancel">✕ Decline</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="padding:.75rem 0;color:#9ca3af;font-size:.875rem;">No pending requests.</p>
            @endif
        </div>

        {{-- Confirmed bookings --}}
        <div class="card" style="margin-top:1.25rem;">
            <div class="section-hdr">
                <h2>Confirmed Bookings</h2>
            </div>
            @if($confirmedBookings->count())
                <div class="tbl-wrap">
                    <table class="data-tbl">
                        <thead>
                            <tr>
                                <th>Customer</th><th>Service</th>
                                <th>Date</th><th>Time</th><th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($confirmedBookings as $bk)
                            <tr>
                                <td style="font-weight:600;">{{ $bk->customer->name }}</td>
                                <td>{{ $bk->service->title }}</td>
                                <td style="white-space:nowrap;">{{ $bk->booking_date->format('d M Y') }}</td>
                                <td style="white-space:nowrap;">{{ \Carbon\Carbon::parse($bk->time_slot)->format('g:i A') }}</td>
                                <td>
                                    <form action="{{ route('provider.bookings.complete', $bk) }}"
                                          method="POST" style="display:contents;">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="btn-sm btn-complete">Mark Complete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p style="padding:.75rem 0;color:#9ca3af;font-size:.875rem;">No confirmed bookings.</p>
            @endif
        </div>

    </div>{{-- /tab-bookings --}}

    {{-- ═══════════════════════════════════════════════════════
         TAB: SERVICES
    ═══════════════════════════════════════════════════════ --}}
    <div class="tab-panel {{ $activeTab === 'services' ? 'active' : '' }}" id="tab-services">

        {{-- ── Add Service form ── --}}
        @php
            $editId = session('edit_service_id');
            $editSvc = $editId ? $services->firstWhere('id', $editId) : null;
        @endphp

        <div class="form-card" id="serviceForm">
            <div class="form-title">
                {{ $editSvc ? 'Edit Service' : 'Add New Service' }}
            </div>

            @if($editSvc)
                {{-- EDIT form --}}
                <form action="{{ route('provider.services.update', $editSvc) }}" method="POST">
                    @csrf @method('PUT')
            @else
                {{-- ADD form --}}
                <form action="{{ route('provider.services.store') }}" method="POST">
                    @csrf
            @endif

                <div class="form-grid">
                    {{-- Title --}}
                    <div class="fld span-2">
                        <label>Service title</label>
                        <input
                            type="text"
                            name="title"
                            value="{{ old('title', $editSvc->title ?? '') }}"
                            placeholder="e.g. Bathroom Pipe Repair"
                            class="{{ $errors->has('title') ? 'err' : '' }}"
                            required
                        >
                        @error('title')<span class="err-msg">{{ $message }}</span>@enderror
                    </div>

                    {{-- Description --}}
                    <div class="fld span-2">
                        <label>Description</label>
                        <textarea
                            name="description"
                            placeholder="Describe what's included in this service…"
                            class="{{ $errors->has('description') ? 'err' : '' }}"
                            required
                        >{{ old('description', $editSvc->description ?? '') }}</textarea>
                        @error('description')<span class="err-msg">{{ $message }}</span>@enderror
                    </div>

                    {{-- Category --}}
                    <div class="fld">
                        <label>Category</label>
                        <select
                            name="category_id"
                            class="{{ $errors->has('category_id') ? 'err' : '' }}"
                            required
                        >
                            <option value="">Select category…</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}"
                                    {{ old('category_id', $editSvc->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')<span class="err-msg">{{ $message }}</span>@enderror
                    </div>

                    {{-- Price type --}}
                    <div class="fld">
                        <label>Price type</label>
                        <select
                            name="price_type"
                            class="{{ $errors->has('price_type') ? 'err' : '' }}"
                            required
                        >
                            <option value="">Select…</option>
                            <option value="fixed"
                                {{ old('price_type', $editSvc->price_type ?? '') === 'fixed' ? 'selected' : '' }}>
                                Fixed price
                            </option>
                            <option value="hourly"
                                {{ old('price_type', $editSvc->price_type ?? '') === 'hourly' ? 'selected' : '' }}>
                                Hourly rate
                            </option>
                        </select>
                        @error('price_type')<span class="err-msg">{{ $message }}</span>@enderror
                    </div>

                    {{-- Price --}}
                    <div class="fld">
                        <label>Price (₨)</label>
                        <input
                            type="number"
                            name="price"
                            value="{{ old('price', $editSvc->price ?? '') }}"
                            placeholder="500"
                            min="0"
                            step="0.01"
                            class="{{ $errors->has('price') ? 'err' : '' }}"
                            required
                        >
                        @error('price')<span class="err-msg">{{ $message }}</span>@enderror
                    </div>

                    {{-- is_active (edit only — store always sets true) --}}
                    @if($editSvc)
                        <div class="fld">
                            <label>Status</label>
                            <label class="check-row">
                                <input
                                    type="checkbox"
                                    name="is_active"
                                    value="1"
                                    {{ old('is_active', $editSvc->is_active ?? true) ? 'checked' : '' }}
                                >
                                Active (visible to customers)
                            </label>
                        </div>
                    @endif
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-save">
                        {{ $editSvc ? 'Save Changes' : 'Add Service' }}
                    </button>
                    @if($editSvc)
                        <a href="{{ route('provider.dashboard') }}?tab=services" class="btn-discard">
                            Cancel
                        </a>
                    @endif
                </div>

            </form>
        </div>

        {{-- ── Services list ── --}}
        @if($services->count())
            <div class="svc-list">
                @foreach($services as $svc)
                    <div class="svc-item">
                        <div class="svc-info">
                            <div class="svc-name">
                                {{ $svc->title }}
                                @if(!$svc->is_active)
                                    <span class="inactive-tag">Inactive</span>
                                @endif
                            </div>
                            <div class="svc-meta">
                                <span>{{ $svc->category->name }}</span>
                                <span>₨{{ number_format($svc->price, 0) }} / {{ $svc->price_type }}</span>
                            </div>
                            @if($svc->description)
                                <p style="font-size:.78rem;color:#6b7280;margin-top:.25rem;line-height:1.4;">
                                    {{ Str::limit($svc->description, 100) }}
                                </p>
                            @endif
                        </div>
                        <div class="action-btns">
                            {{-- Edit: pass service id via GET so dashboard reopens in edit mode --}}
                            <a href="{{ route('provider.dashboard') }}?tab=services&edit={{ $svc->id }}"
                               class="btn-sm btn-edit">Edit</a>
                            <form action="{{ route('provider.services.destroy', $svc) }}"
                                  method="POST" style="display:contents;"
                                  onsubmit="return confirm('Delete {{ addslashes($svc->title) }}?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-sm btn-delete">Delete</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align:center;padding:2rem;color:#9ca3af;font-size:.875rem;">
                No services yet. Add your first one above.
            </div>
        @endif

    </div>{{-- /tab-services --}}

    {{-- ═══════════════════════════════════════════════════════
         TAB: AVAILABILITY
    ═══════════════════════════════════════════════════════ --}}
    <div class="tab-panel {{ $activeTab === 'availability' ? 'active' : '' }}" id="tab-availability">

        <div class="form-card">
            <div class="form-title">Weekly Availability</div>
            <p style="font-size:.83rem;color:#6b7280;margin-bottom:1.25rem;">
                Set the days and hours you're available for bookings.
                Uncheck a day to mark it as unavailable.
            </p>

            <form action="{{ route('provider.availability.set') }}" method="POST" id="availForm">
                @csrf
                <div class="avail-form-grid">
                    @foreach($days as $day)
                        @php
                            $slot     = $availability->get($day);
                            $checked  = $slot !== null;
                            $startVal = $slot ? \Carbon\Carbon::parse($slot->start_time)->format('H:i') : '09:00';
                            $endVal   = $slot ? \Carbon\Carbon::parse($slot->end_time)->format('H:i')   : '18:00';
                        @endphp
                        <div class="avail-row {{ $checked ? 'enabled' : '' }}" id="row-{{ $day }}">
                            <span class="day-name">{{ ucfirst(substr($day,0,3)) }}</span>

                            <div class="fld" style="gap:.2rem;">
                                <label style="font-size:.68rem;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;">
                                    Start
                                </label>
                                <input
                                    type="time"
                                    name="availability[{{ $loop->index }}][start_time]"
                                    value="{{ $startVal }}"
                                    id="start-{{ $day }}"
                                    {{ !$checked ? 'disabled' : '' }}
                                >
                            </div>

                            <div class="fld" style="gap:.2rem;">
                                <label style="font-size:.68rem;color:#6b7280;text-transform:uppercase;letter-spacing:.4px;">
                                    End
                                </label>
                                <input
                                    type="time"
                                    name="availability[{{ $loop->index }}][end_time]"
                                    value="{{ $endVal }}"
                                    id="end-{{ $day }}"
                                    {{ !$checked ? 'disabled' : '' }}
                                >
                            </div>

                            <label class="unavail-check">
                                <input
                                    type="checkbox"
                                    id="chk-{{ $day }}"
                                    {{ $checked ? 'checked' : '' }}
                                    onchange="toggleDay('{{ $day }}', this.checked)"
                                >
                                Available
                            </label>

                            {{-- Hidden field carries day_of_week when row is enabled --}}
                            <input
                                type="hidden"
                                name="availability[{{ $loop->index }}][day_of_week]"
                                value="{{ $day }}"
                                id="hidden-{{ $day }}"
                                {{ !$checked ? 'disabled' : '' }}
                            >
                        </div>
                    @endforeach
                </div>

                <div class="form-actions" style="margin-top:1.5rem;">
                    <button type="submit" class="btn-save">Save Availability</button>
                </div>
            </form>

            {{-- Current availability summary --}}
            @if($availability->count())
                <div style="margin-top:1.5rem;padding-top:1rem;border-top:1px solid #f3f4f6;">
                    <p style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:#6b7280;margin-bottom:.65rem;">
                        Currently set
                    </p>
                    <div class="avail-pills">
                        @foreach($availability as $slot)
                            <span class="avail-pill">
                                {{ ucfirst($slot->day_of_week) }}
                                <span class="avail-time">
                                    {{ \Carbon\Carbon::parse($slot->start_time)->format('g A') }}–{{ \Carbon\Carbon::parse($slot->end_time)->format('g A') }}
                                </span>
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

    </div>{{-- /tab-availability --}}

</div>{{-- /page-wrap --}}

@endsection

@push('scripts')
<script>
/* ── Tab switching ─────────────────────────────────── */
function openTab(name) {
    document.querySelectorAll('.tab-panel').forEach(function(p) {
        p.classList.remove('active');
    });
    document.querySelectorAll('.tab-btn').forEach(function(b) {
        b.classList.toggle('active', b.dataset.tab === name);
    });
    var panel = document.getElementById('tab-' + name);
    if (panel) panel.classList.add('active');

    // Update URL without reload so refresh preserves tab
    var url = new URL(window.location);
    url.searchParams.set('tab', name);
    history.replaceState(null, '', url.toString());
}

document.querySelectorAll('.tab-btn[data-tab]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        openTab(this.dataset.tab);
    });
});

/* ── Edit service: scroll to form ──────────────────── */
var editParam = new URLSearchParams(window.location.search).get('edit');
if (editParam) {
    var form = document.getElementById('serviceForm');
    if (form) { setTimeout(function(){ form.scrollIntoView({behavior:'smooth', block:'start'}); }, 150); }
}

/* ── Availability: toggle day ──────────────────────── */
function toggleDay(day, enabled) {
    var row     = document.getElementById('row-' + day);
    var startEl = document.getElementById('start-' + day);
    var endEl   = document.getElementById('end-' + day);
    var hidEl   = document.getElementById('hidden-' + day);

    [startEl, endEl, hidEl].forEach(function(el) {
        if (el) el.disabled = !enabled;
    });

    row.classList.toggle('enabled', enabled);
}

/* ── Availability form: strip disabled rows on submit ─ */
document.getElementById('availForm').addEventListener('submit', function(e) {
    // Re-index only the enabled rows so the server gets a clean array
    var enabled = [];
    @foreach($days as $day)
    if (document.getElementById('chk-{{ $day }}').checked) {
        enabled.push({
            day: '{{ $day }}',
            start: document.getElementById('start-{{ $day }}').value,
            end:   document.getElementById('end-{{ $day }}').value
        });
    }
    @endforeach

    // If nothing is enabled, allow blank submission (server will error gracefully)
    if (enabled.length === 0) return;

    e.preventDefault();

    var form = this;
    // Clear existing availability fields
    form.querySelectorAll('input[name^="availability"]').forEach(function(i) { i.remove(); });

    enabled.forEach(function(slot, idx) {
        function hid(n, v) {
            var i = document.createElement('input');
            i.type = 'hidden'; i.name = n; i.value = v;
            form.appendChild(i);
        }
        hid('availability[' + idx + '][day_of_week]', slot.day);
        hid('availability[' + idx + '][start_time]',  slot.start);
        hid('availability[' + idx + '][end_time]',    slot.end);
    });

    form.submit();
});
</script>
@endpush

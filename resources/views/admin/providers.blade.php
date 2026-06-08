@extends('layouts.admin')

@section('title', 'Provider Approvals')

@section('admin-content')

<div class="admin-page-hdr">
    <h1>
        Provider Approvals
        @if($providers->total() > 0)
            <span class="sub">{{ $providers->total() }} pending</span>
        @endif
    </h1>
</div>

<div class="admin-card">
    <div class="admin-card-title">Providers awaiting approval</div>

    @if($providers->count())
        <div class="tbl-scroll">
            <table class="admin-tbl">
                <thead>
                    <tr>
                        <th>Provider</th>
                        <th>Experience</th>
                        <th>City</th>
                        <th>Bio</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($providers as $provider)
                        @php $pp = $provider->providerProfile; @endphp
                        <tr>
                            <td>
                                <div class="provider-info">
                                    <div class="pname">{{ $provider->name }}</div>
                                    <div class="pmeta">{{ $provider->email }}</div>
                                    @if($provider->phone)
                                        <div class="pmeta">📞 {{ $provider->phone }}</div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($pp && $pp->experience_years > 0)
                                    <span style="font-weight:600;">{{ $pp->experience_years }}</span>
                                    <span class="muted"> yr{{ $pp->experience_years !== 1 ? 's' : '' }}</span>
                                @else
                                    <span class="muted">—</span>
                                @endif
                            </td>
                            <td>
                                {{ $provider->city ?? '—' }}
                            </td>
                            <td>
                                @if($pp && $pp->bio)
                                    <span class="muted" style="font-style:italic;">
                                        {{ Str::limit($pp->bio, 80) }}
                                    </span>
                                @else
                                    <span class="muted">No bio provided.</span>
                                @endif
                            </td>
                            <td class="muted" style="white-space:nowrap;">
                                {{ $provider->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <div class="btn-group">
                                    {{-- Approve --}}
                                    <form action="{{ route('admin.providers.approve', $provider) }}"
                                          method="POST" style="display:contents;">
                                        @csrf
                                        <input type="hidden" name="action" value="approve">
                                        <button type="submit" class="btn-xs btn-approve"
                                                onclick="return confirm('Approve {{ addslashes($provider->name) }}?')">
                                            ✓ Approve
                                        </button>
                                    </form>
                                    {{-- Reject --}}
                                    <form action="{{ route('admin.providers.approve', $provider) }}"
                                          method="POST" style="display:contents;">
                                        @csrf
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn-xs btn-reject"
                                                onclick="return confirm('Reject {{ addslashes($provider->name) }}? They will be notified.')">
                                            ✕ Reject
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($providers->hasPages())
            <div class="admin-pagination">{{ $providers->links() }}</div>
        @endif

    @else
        <div class="admin-empty">
            <div class="icon">🎉</div>
            <p>No providers pending approval right now.</p>
        </div>
    @endif
</div>

@endsection

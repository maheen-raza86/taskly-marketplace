@extends('layouts.admin')

@section('title', 'Manage Categories')

@section('admin-content')

<div class="admin-page-hdr">
    <h1>Categories</h1>
    <span class="sub">{{ $categories->total() }} total</span>
</div>

{{-- ── Add category form ─────────────────────────────────────── --}}
<div class="inline-form">
    <div class="form-title">Add New Category</div>
    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf
        <div class="inline-form-row">
            <div class="fld" style="flex:2;">
                <label for="cat_name">Name <span style="color:#ef4444;">*</span></label>
                <input
                    type="text"
                    id="cat_name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="e.g. Painting"
                    maxlength="100"
                    required
                    class="{{ $errors->has('name') ? 'is-invalid' : '' }}"
                >
                @error('name')
                    <span style="font-size:.73rem;color:#dc2626;margin-top:.2rem;">{{ $message }}</span>
                @enderror
            </div>
            <div class="fld" style="flex:1;">
                <label for="cat_icon">
                    Icon <span style="font-weight:400;color:#9ca3af;text-transform:none;font-size:.7rem;">(optional — name or emoji)</span>
                </label>
                <input
                    type="text"
                    id="cat_icon"
                    name="icon"
                    value="{{ old('icon') }}"
                    placeholder="🎨 or paint-brush"
                    maxlength="100"
                >
            </div>
            <div class="fld" style="flex:0;">
                <label>&nbsp;</label>
                <button type="submit" class="btn-save">Add</button>
            </div>
        </div>
    </form>
</div>

{{-- ── Categories table ──────────────────────────────────────── --}}
<div class="admin-card">
    <div class="admin-card-title">All Categories</div>

    @if($categories->count())
        <div class="tbl-scroll">
            <table class="admin-tbl">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Icon</th>
                        <th>Name</th>
                        <th>Services</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $cat)
                        <tr>
                            <td class="muted">{{ $cat->id }}</td>
                            <td style="font-size:1.3rem;text-align:center;">
                                {{ $cat->icon ?? '—' }}
                            </td>
                            <td style="font-weight:600;">{{ $cat->name }}</td>
                            <td>
                                <span style="font-weight:600;">{{ $cat->services_count }}</span>
                                <span class="muted"> service{{ $cat->services_count !== 1 ? 's' : '' }}</span>
                            </td>
                            <td class="muted" style="white-space:nowrap;">
                                {{ $cat->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <div class="btn-group">
                                    {{-- Edit → opens modal --}}
                                    <button
                                        class="btn-xs btn-edit"
                                        onclick="openEditModal({{ $cat->id }}, '{{ addslashes($cat->name) }}', '{{ addslashes($cat->icon ?? '') }}')"
                                    >
                                        Edit
                                    </button>
                                    {{-- Delete --}}
                                    @if($cat->services_count === 0)
                                        <form action="{{ route('admin.categories.destroy', $cat) }}"
                                              method="POST" style="display:contents;"
                                              onsubmit="return confirm('Delete category &quot;{{ addslashes($cat->name) }}&quot;? This cannot be undone.')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-xs btn-delete">Delete</button>
                                        </form>
                                    @else
                                        <span class="btn-xs"
                                              style="background:#f3f4f6;color:#9ca3af;cursor:not-allowed;"
                                              title="Cannot delete — has {{ $cat->services_count }} service(s)">
                                            Delete
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($categories->hasPages())
            <div class="admin-pagination">{{ $categories->links() }}</div>
        @endif

    @else
        <div class="admin-empty">
            <div class="icon">🏷</div>
            <p>No categories yet. Add one above.</p>
        </div>
    @endif
</div>

{{-- ── Edit modal ────────────────────────────────────────────── --}}
<div class="modal-overlay" id="editModal">
    <div class="modal-box">
        <button class="modal-close" onclick="closeEditModal()" title="Close">×</button>
        <h3>Edit Category</h3>

        <form id="editCatForm" method="POST">
            @csrf @method('PUT')

            <div class="fld">
                <label for="edit_name">Name <span style="color:#ef4444;">*</span></label>
                <input type="text" id="edit_name" name="name" required maxlength="100">
            </div>

            <div class="fld">
                <label for="edit_icon">Icon <span style="font-weight:400;color:#9ca3af;">(optional)</span></label>
                <input type="text" id="edit_icon" name="icon" maxlength="100">
            </div>

            <div class="modal-actions">
                <button type="submit" class="btn-save">Save Changes</button>
                <button type="button" class="btn-xs btn-edit" onclick="closeEditModal()"
                        style="padding:.5rem 1rem;font-size:.875rem;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openEditModal(id, name, icon) {
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_icon').value = icon;
    // Build the PUT route URL dynamically
    document.getElementById('editCatForm').action =
        "{{ url('admin/categories') }}/" + id;
    document.getElementById('editModal').classList.add('open');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('open');
}

// Close on overlay click
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeEditModal();
});
</script>
@endpush

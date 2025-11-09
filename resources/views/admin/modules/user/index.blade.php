@extends('admin.layouts.app2')
@section('title', 'Manage Users')

@section('links')

@endsection

@section('content')
<div class="userlist-card">
    <h2>Users List</h2>

    <!-- Filter/Search Bar -->
    <form method="GET" action="{{ route('admin.admin.users.index') }}" class="user-filter-bar">
        <input type="text" name="name" placeholder="Search by name" value="{{ request('name') }}">
        <select name="status">
            <option value="">All Status</option>
            <option value="1" {{ request('status')=='1' ? 'selected' : '' }}>Active</option>
            <option value="2" {{ request('status')=='2' ? 'selected' : '' }}>Inactive</option>
            <option value="0" {{ request('status')=='0' ? 'selected' : '' }}>Unverified</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="{{ route('admin.admin.users.index') }}" class="btn btn-primary">Reset</a>
    </form>

    <!-- Users Table -->
    @php $slNo = ($users->currentPage() - 1) * $users->perPage(); @endphp
    <table class="users-table-modern">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Registered At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($users as $user)
            @php $slNo++; @endphp
            <tr>
                <td>{{ $slNo }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @if(!$user->email_verified_at)
                        <span class="status-badge unverified">Unverified</span>
                    @elseif($user->status == 1)
                        <span class="status-badge active">Active</span>
                    @elseif($user->status == 2)
                        <span class="status-badge inactive">Inactive</span>
                    @endif
                </td>
                <td>{{ $user->created_at->format('d M Y h:i A') }}</td>
                <td>
    <div class="d-flex gap-2">
        <button class="btn btn-info btn-pill btn-sm"
            style="min-width:94px; font-weight:600; font-size:1rem;"
            data-bs-toggle="modal"
            data-bs-target="#userDetailModal"
            data-user='@json($user)'>
            <i class="bi bi-eye"></i> View
        </button>
        @if(!$user->email_verified_at)
            <form action="{{ route('admin.admin.users.verifyEmail', $user) }}" method="POST" class="verify-form d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-pill btn-sm"
                    style="min-width:94px; font-weight:600; font-size:1rem;">
                    <i class="fa fa-check-circle me-1"></i> Verify
                </button>
            </form>
        @else
            <form action="{{ route('admin.admin.users.toggleStatus', $user) }}" method="POST" class="status-form d-inline">
                @csrf
                @if($user->status == 1)
                    <button type="submit" class="btn btn-danger btn-pill btn-sm"
                        style="min-width:94px; font-weight:600; font-size:1rem;">
                        <i class="fa fa-ban me-1"></i> Block
                    </button>
                @else
                    <button type="submit" class="btn btn-primary btn-pill btn-sm"
                        style="min-width:94px; font-weight:600; font-size:1rem;">
                        <i class="fa fa-user-check me-1"></i> Activate
                    </button>
                @endif
            </form>
        @endif
    </div>
</td>

            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $users->links() }}</div>
</div>

<!-- User Detail Modal -->
<div class="modal fade" id="userDetailModal" tabindex="-1" aria-labelledby="userDetailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="userDetailModalLabel"><i class="bi bi-person-circle me-2"></i> User Details</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
            <div class="col-md-4 text-center">
                <img id="modal-user-image" src="" class="img-fluid rounded-circle border p-1 shadow-sm" style="width:150px;height:150px;">
            </div>
            <div class="col-md-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3">
                        <h5 class="card-title mb-3" id="modal-user-name"></h5>
                        <p class="mb-2"><i class="bi bi-envelope-fill me-2"></i> <span id="modal-user-email"></span></p>
                        <p class="mb-2"><i class="bi bi-geo-alt-fill me-2"></i> <span id="modal-user-country"></span>, <span id="modal-user-state"></span>, <span id="modal-user-city"></span></p>
                        <p class="mb-2"><i class="bi bi-mailbox me-2"></i> Zipcode: <span id="modal-user-zipcode"></span></p>
                        <p class="mb-2"><i class="bi bi-calendar-check-fill me-2"></i> Verified At: <span id="modal-user-verified"></span></p>
                        <p class="mb-0"><i class="bi bi-clock-fill me-2"></i> Registered: <span id="modal-user-created"></span></p>
                    </div>
                </div>
            </div>
        </div>
      </div>
      <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="bi bi-x-circle me-1"></i> Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Confirm block / activate
    document.querySelectorAll('.status-form').forEach(form => {
        form.addEventListener('submit', function(e){
            e.preventDefault();
            const action = form.querySelector('button').textContent.trim();
            Swal.fire({
                title: 'Are you sure?',
                text: `You are about to ${action.toLowerCase()} this user.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, proceed!'
            }).then(result => {
                if(result.isConfirmed) form.submit();
            });
        });
    });

    // Confirm verify email
    document.querySelectorAll('.verify-form').forEach(form => {
        form.addEventListener('submit', function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Verify Email?',
                text: `This will mark the user's email as verified.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, verify!'
            }).then(result => {
                if(result.isConfirmed) form.submit();
            });
        });
    });

    // Populate modal on view button click
    document.querySelectorAll('.view-user-btn').forEach(btn => {
        btn.addEventListener('click', function(){
            const user = JSON.parse(this.dataset.user);

            document.getElementById('modal-user-image').src = user.image ? `/storage/images/user_image/${user.image}` : `/assets/images/users/7.png`;
            document.getElementById('modal-user-name').textContent = user.name;
            document.getElementById('modal-user-email').textContent = user.email;
            document.getElementById('modal-user-country').textContent = user.country ?? '-';
            document.getElementById('modal-user-state').textContent = user.state ?? '-';
            document.getElementById('modal-user-city').textContent = user.city ?? '-';
            document.getElementById('modal-user-zipcode').textContent = user.zipcode ?? '-';

            const verifiedAt = user.email_verified_at ? new Date(user.email_verified_at) : null;
            document.getElementById('modal-user-verified').textContent = verifiedAt ? verifiedAt.toLocaleString('en-US', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: true
            }) : '-';

            const createdAt = new Date(user.created_at);
            document.getElementById('modal-user-created').textContent = createdAt.toLocaleString('en-US', {
                day: '2-digit', month: 'short', year: 'numeric',
                hour: '2-digit', minute: '2-digit', hour12: true
            });
        });
    });
</script>
@endsection

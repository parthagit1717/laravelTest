@extends('admin.layouts.app2')
@section('title','Manage Comments')

@section('links')
 
@endsection

@section('content')
<div class="userlist-card">
    <h2>Comments List</h2>

    <!-- Filter Bar -->
    <form method="GET" class="user-filter-bar d-flex flex-wrap gap-2 mb-3">
        <input type="text" name="user_name" placeholder="Search by User Name" value="{{ request('user_name') }}">
        <select name="status">
            <option value="">All Status</option>
            <option value="0" {{ request('status')=='0'?'selected':'' }}>Active</option>
            <option value="1" {{ request('status')=='1'?'selected':'' }}>Blocked</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="{{ route('admin.comments.index') }}" class="btn btn-primary">Reset</a>
    </form>

    @php $slNo = ($comments->currentPage() - 1) * $comments->perPage(); @endphp

    <table class="users-table-modern">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Post</th>
                <th>User</th>
                <th>Comment</th>
                <th>Date & Time</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($comments as $c)
            @php $slNo++; @endphp
            <tr>
                <td>{{ $slNo }}</td>
                <td>{{ $c->post->title ?? '-' }}</td>
                <td>{{ $c->user->name ?? '-' }}</td>
                <td class="comment-cell" title="{{ $c->body }}">
                    {{ Str::limit($c->body, 60) }}
                </td>
                <td>{{ $c->created_at->format('d M Y, h:i A') }}</td>
                <td>
                    @if($c->is_blocked)
                        <span class="status-badge inactive">Blocked</span>
                    @else
                        <span class="status-badge active">Active</span>
                    @endif
                </td>
                <td>
                    <div class="action-btn-group">
                        <button class="btn btn-info btn-sm view-comment-btn action-btn"
                            data-bs-toggle="modal"
                            data-bs-target="#commentDetailModal"
                            data-comment='@json($c->load('user','post'))'>
                            <i class="bi bi-eye"></i> View
                        </button>
                        <form action="{{ route('admin.comments.toggleStatus', $c) }}" method="POST" style="display:inline;" class="status-form">
                            @csrf
                            @if($c->is_blocked)
                                <button type="submit" class="action-btn btn btn-primary btn-sm activate">Unblock</button>
                            @else
                                <button type="submit" class="action-btn btn btn-danger btn-sm block">
                                    <i class="fa fa-ban me-1"></i>Block
                                </button>
                            @endif
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $comments->links() }}</div>
</div>

<!-- Comment Detail Modal -->
<div class="modal fade" id="commentDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modal-comment-user"></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>Post:</strong> <span id="modal-comment-post"></span></p>
        <p><strong>Comment:</strong></p>
        <p id="modal-comment-body"></p>
        <p><strong>Date & Time:</strong> <span id="modal-comment-datetime"></span></p>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.querySelectorAll('.view-comment-btn').forEach(btn=>{
    btn.addEventListener('click',function(){
        const comment = JSON.parse(this.dataset.comment);
        document.getElementById('modal-comment-user').textContent = comment.user?.name ?? '-';
        document.getElementById('modal-comment-post').textContent = comment.post?.title ?? '-';
        document.getElementById('modal-comment-body').textContent = comment.body;
        document.getElementById('modal-comment-datetime').textContent = new Date(comment.created_at).toLocaleString();
    });
});

// SweetAlert2 confirmation for Block/Unblock
document.querySelectorAll('.status-form').forEach(form => {
    form.addEventListener('submit', function(e){
        e.preventDefault();
        const button = form.querySelector('button');
        const action = button.textContent.trim();
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to ${action.toLowerCase()} this comment.`,
            icon: action.toLowerCase().includes('unblock') ? 'info' : 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed!'
        }).then(result => {
            if(result.isConfirmed) form.submit();
        });
    });
});
</script>
@endsection

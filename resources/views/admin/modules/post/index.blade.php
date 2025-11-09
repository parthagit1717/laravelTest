@extends('admin.layouts.app2')
@section('title','Manage Posts')

@section('links')
 
@endsection

@section('content')
<div class="userlist-card">
    <h2>Posts List</h2>

    <!-- Filter/Search Bar -->
    <form method="GET" class="user-filter-bar">
        <input type="text" name="title" placeholder="Search by title" value="{{ request('title') }}">
        <input type="text" name="topic" placeholder="Topic" value="{{ request('topic') }}">
        <select name="status">
            <option value="">All Status</option>
            <option value="0" {{ request('status')=='0'?'selected':'' }}>Active</option>
            <option value="1" {{ request('status')=='1'?'selected':'' }}>Blocked</option>
        </select>
        <button type="submit" class="btn btn-secondary">Filter</button>
        <a href="{{ route('admin.posts.index') }}" class="btn btn-primary">Reset</a>
    </form>

    @php $slNo = ($posts->currentPage() - 1) * $posts->perPage(); @endphp
    <table class="users-table-modern">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>Title</th>
                <th>User</th>
                <th>Topic</th>
                <th>Image</th>
                <th>Likes</th>
                <th>Comments</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($posts as $post)
            @php $slNo++; @endphp
            <tr>
                <td>{{ $slNo }}</td>
                <td>{{ $post->title }}</td>
                <td>{{ $post->user->name ?? '-' }}</td>
                <td>{{ $post->topic ?? '-' }}</td>
                <td>
                    @if($post->image)
                        <img src="{{ asset('storage/'.$post->image) }}" class="post-image" width="120" height="100">
                    @else
                        -
                    @endif
                </td>
                <td>{{ $post->likes()->count() }}</td>
                <td>{{ $post->comments()->count() }}</td>
                <td>
                    @if($post->is_blocked)
                        <span class="status-badge inactive">Blocked</span>
                    @else
                        <span class="status-badge active">Active</span>
                    @endif
                </td>
                <td>
                    <div class="d-flex gap-2">
                        <button class="btn btn-info btn-pill btn-sm"
                            style="min-width:94px; font-weight:600; font-size:1rem;"
                            data-bs-toggle="modal"
                            data-bs-target="#postDetailModal"
                            data-post='@json($post->load('comments.user'))'>
                            <i class="fa fa-eye"></i> View
                        </button>
                        <form action="{{ route('admin.posts.toggleStatus',$post) }}" method="POST" class="post-status-form d-inline">
                            @csrf
                            @if($post->is_blocked)
                                <button type="submit"
                                    class="btn btn-primary btn-pill btn-sm"
                                    style="min-width:94px; font-weight:600; font-size:1rem;">
                                    Unblock
                                </button>
                            @else
                                <button type="submit"
                                    class="btn btn-danger btn-pill btn-sm"
                                    style="min-width:94px; font-weight:600; font-size:1rem;">
                                    <i class="fa fa-ban me-1"></i> Block
                                </button>
                            @endif
                        </form>
                    </div>
                </td>



            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $posts->links() }}</div>
</div>

<!-- Post Detail Modal -->
<div class="modal fade" id="postDetailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modal-post-title"></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p><strong>User:</strong> <span id="modal-post-user"></span></p>
        <p><strong>Topic:</strong> <span id="modal-post-topic"></span></p>
        <p><strong>Content:</strong></p>
        <p id="modal-post-content"></p>
        <p><strong>Image:</strong></p>
        <img id="modal-post-image" class="modal-post-image" src="" width="120" height="100">
        <hr>
        <h6>Comments ( <span id="modal-post-comments-count"></span> )</h6>
        <div id="modal-post-comments"></div>
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
document.querySelectorAll('.view-post-btn').forEach(btn=>{
    btn.addEventListener('click',function(){
        const post = JSON.parse(this.dataset.post);
        document.getElementById('modal-post-title').textContent = post.title;
        document.getElementById('modal-post-user').textContent = post.user?.name ?? '-';
        document.getElementById('modal-post-topic').textContent = post.topic ?? '-';
        document.getElementById('modal-post-content').textContent = post.content ?? '-';
        document.getElementById('modal-post-image').src = post.image ? `/storage/${post.image}` : '';
        document.getElementById('modal-post-image').style.display = post.image ? 'block' : 'none';
        const commentsContainer = document.getElementById('modal-post-comments');
        commentsContainer.innerHTML = '';
        (post.comments || []).forEach(c=>{
            const div = document.createElement('div');
            div.classList.add('comment-box');
            div.innerHTML = `<strong>${c.user?.name ?? 'Unknown'}:</strong> ${c.body}`;
            commentsContainer.appendChild(div);
        });
        document.getElementById('modal-post-comments-count').textContent = post.comments ? post.comments.length : 0;
    });
});

// SweetAlert2 for block/activate confirmation
document.querySelectorAll('.post-status-form').forEach(form => {
    form.addEventListener('submit', function(e){
        e.preventDefault();
        const button = form.querySelector('button');
        const action = button.textContent.trim();
        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to ${action.toLowerCase()} this post.`,
            icon: action.toLowerCase().includes('activate') ? 'info' : 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, proceed!'
        }).then(result => {
            if(result.isConfirmed) form.submit();
        });
    });
});
</script>
@endsection

@extends('layouts.app2')
@section('title', 'Dashboard')

@section('content')

<style>
.btn-like {
  background: #f8fafc;
  color: #48525c;
  border: 1px solid #dbeafe;
  font-weight: 500;
}
.btn-like.liked {
  background: #dff3fb !important;
  color: #0d6efd !important;
  font-weight: bold !important;
  border: 1px solid #aee1fa !important;
}
.badge-owner {
  background: #ffe066;
  color: #856404;
  font-size: 0.75em;
  border-radius: 0.7em;
  padding: 0.24em 0.65em;
  margin-left: 0.5em;
  vertical-align: middle;
}
.likers-list {
  background: #f8fafc;
  border: 1px solid #e3eaf3;
  border-radius: 6px;
  padding: 0.4em 1em 0.6em 1em;
  margin-bottom: 1em;
}
.likers-list-user i {
  color: #1570ef;
  margin-right: 0.4em;
}
a.show-likers {
  text-decoration: underline;
  cursor: pointer;
}
a.show-likers:hover {
  color: #1570ef;
}
.btn-comment-toggle {
  background: #f5f7fa;
  color: #495869;
  border: 1px solid #e0e6ef;
}
.btn-comment-toggle:hover {
  background: #f0faff;
  color: #1570ef;
}
</style>

<div class="fb-feed-bg min-h-screen py-8">
  <div class="max-w-xl mx-auto">

    <div class="mb-8 header-row d-flex justify-content-between align-items-center">
      <div>
        <h1 class="text-2xl fw-bold text-dark mb-1">
          Welcome back, <span class="text-primary">{{ Auth::user()->name }}</span> 👋
        </h1>
        <p class="text-secondary mt-1 mb-0" style="font-size:1rem;">Here’s what’s happening on your feed today.</p>
      </div>
      <button class="btn btn-primary rounded-pill shadow create-post-btn" data-bs-toggle="modal" data-bs-target="#createPostModal">
        <i class="bi bi-plus-circle-fill"></i> Create Post
      </button>
    </div>

    <div id="posts-container">
      @foreach($posts as $post)
      <div class="fb-post-card mb-4 p-3 rounded-3 shadow-sm bg-white border" data-owner="{{ $post->user_id }}">
        <div class="d-flex justify-content-between mb-2">
          <div class="d-flex align-items-center gap-2">
            <img src="{{ $post->user->image ? asset('storage/images/user_image/'.$post->user->image) : asset('assets/images/users/7.png') }}" class="fb-avatar rounded-circle border" width="45" height="45" alt="{{ $post->user->name }}">
            <div>
              <span class="fw-semibold">{{ $post->user->name }}</span><br>
              <span class="text-muted small">{{ $post->created_at->diffForHumans() }}</span>
            </div>
          </div>

          @if(Auth::id() === $post->user_id)
          <div class="dropdown">
            <button class="btn btn-sm border-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
            <ul class="dropdown-menu">
              <li>
                <a href="#" class="dropdown-item edit-post-link" data-bs-toggle="modal" data-bs-target="#editPostModal"
                 data-id="{{ $post->id }}"
                 data-title="{{ $post->title }}"
                 data-content="{{ htmlentities($post->content) }}"
                 data-topic="{{ $post->topic }}"
                 data-image="{{ $post->image ? asset('storage/' . $post->image) : '' }}">
                 <i class="bi bi-pencil-square me-1"></i> Edit
              </a>
              </li>
              <li>
                <form method="POST" action="{{ route('posts.destroy', $post) }}" class="delete-post-form" data-post-id="{{ $post->id }}">
                  @csrf @method('DELETE')
                  <button type="button" class="dropdown-item text-danger delete-post-btn">
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                </form>
              </li>
            </ul>
          </div>
          @endif
        </div>

        <div class="fw-bold fs-5 mb-1">{{ $post->title }}</div>
        <div class="mb-2">{{ $post->content }}</div>

        @if($post->image)
        <div class="rounded-2 mb-2 overflow-hidden border">
          <img src="{{ asset('storage/' . $post->image) }}" class="w-100" style="max-height:400px;object-fit:cover;">
        </div>
        @endif

        <div class="d-flex justify-content-between text-muted small mb-1">
          <span id="likes-count-{{ $post->id }}">
            <i class="bi bi-hand-thumbs-up-fill text-primary"></i>
            <a class="show-likers text-primary" data-post="{{ $post->id }}">{{ $post->likes->count() }} Likes</a>
          </span>
          <span class="btn-comment-toggle" data-post="{{ $post->id }}">
            <i class="bi bi-chat-dots"></i>
            <span id="comments-count-{{ $post->id }}">{{ $post->comments->count() }}</span> Comments
          </span>
        </div>

        <div class="likers-list" id="likers-list-{{ $post->id }}" style="display:none;">
          @foreach($post->likes as $like)
          <div class="likers-list-user">
            <i class="bi bi-person-fill"></i> {{ $like->user->name }}
          </div>
          @endforeach
        </div>

        @php
        $userLiked = $post->likes->contains('user_id', auth()->id());
        @endphp
        <div class="d-flex gap-2 border-top pt-2 mt-2">
          <button class="btn btn-sm flex-fill btn-like {{ $userLiked ? 'liked' : '' }}" data-post="{{ $post->id }}" aria-pressed="{{ $userLiked ? 'true' : 'false' }}">
            <i class="bi {{ $userLiked ? 'bi-hand-thumbs-up-fill' : 'bi-hand-thumbs-up' }}"></i> Like
          </button>
          <button class="btn btn-sm flex-fill btn-comment-toggle" data-post="{{ $post->id }}">💬 Comment</button>
        </div>

        <div class="comments-wrapper mt-2" id="comments-{{ $post->id }}" style="display:none;">
          <div class="existing-comments mb-2">
            @foreach($post->comments()->with('user')->latest()->get() as $comment)
            <div class="comment mb-2 border-bottom pb-1" id="comment-{{ $comment->id }}">
              <strong>{{ $comment->user->name }}
                @if($post->user_id == $comment->user_id)
                <span class="badge-owner" title="Post owner">Owner</span>
                @endif
              </strong>
              <small class="text-muted">{{ $comment->created_at->diffForHumans() }}</small>
              <div>{{ $comment->body }}</div>
              @if(auth()->id() === $comment->user_id)
              <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="d-inline delete-comment-form" data-comment-id="{{ $comment->id }}" data-post-id="{{ $post->id }}">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-link btn-sm text-danger p-0">Delete</button>
              </form>
              @endif
            </div>
            @endforeach
          </div>
          <form class="comment-form" data-post="{{ $post->id }}" method="POST" action="{{ route('posts.comments.store', $post) }}">
            @csrf
            <div class="input-group input-group-sm">
              <input type="text" name="body" class="form-control" placeholder="Write a comment..." required>
              <button class="btn btn-primary" type="submit">Post</button>
            </div>
          </form>
        </div>
      </div>
      @endforeach
    </div>

    @if($posts->hasMorePages())
    <div class="text-center my-4" id="loadMoreWrapper">
      <button id="loadMoreBtn" class="btn btn-primary rounded-pill shadow" data-next-url="{{ $posts->nextPageUrl() }}">
        Show More
      </button>
    </div>
    @endif

  </div>
</div>

{{-- Create Post Modal --}}
<div class="modal fade" id="createPostModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data">
      @csrf
      <input type="hidden" name="form_type" value="create">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Create Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
            @error('title') <div class="text-danger-small">{{ $message }}</div> @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
            @error('content') <div class="text-danger-small">{{ $message }}</div> @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Topic</label>
            <input type="text" name="topic" class="form-control @error('topic') is-invalid @enderror" value="{{ old('topic') }}">
            @error('topic') <div class="text-danger-small">{{ $message }}</div> @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="image" class="form-control @error('image') is-invalid @enderror">
            @error('image') <div class="text-danger-small">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create</button>
        </div>
      </div>
    </form>
  </div>
</div>

{{-- Edit Post Modal --}}
<div class="modal fade" id="editPostModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <form id="edit-post-form" method="POST" enctype="multipart/form-data">
      @csrf
      @method('PUT')
      <input type="hidden" name="form_type" value="edit">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Post</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Title</label>
            <input type="text" name="title" id="edit-post-title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}">
            @error('title') <div class="text-danger-small">{{ $message }}</div> @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Content</label>
            <textarea name="content" id="edit-post-content" class="form-control @error('content') is-invalid @enderror">{{ old('content') }}</textarea>
            @error('content') <div class="text-danger-small">{{ $message }}</div> @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Topic</label>
            <input type="text" name="topic" id="edit-post-topic" class="form-control @error('topic') is-invalid @enderror" value="{{ old('topic') }}">
            @error('topic') <div class="text-danger-small">{{ $message }}</div> @enderror
          </div>
          <div class="mb-3">
            <label class="form-label">Image</label>
            <input type="file" name="image" id="edit-post-image" class="form-control @error('image') is-invalid @enderror">
            <img id="edit-post-image-preview" src="" class="mt-2 w-100" style="max-height:200px;object-fit:cover;">
            @error('image') <div class="text-danger-small">{{ $message }}</div> @enderror
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Update Post</button>
        </div>
      </div>
    </form>
  </div>
</div>

@if ($errors->any())
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      @if(old('form_type') == 'edit')
        new bootstrap.Modal(document.getElementById('editPostModal')).show();
      @else
        new bootstrap.Modal(document.getElementById('createPostModal')).show();
      @endif
    });
  </script>
@endif

<script>
document.addEventListener('DOMContentLoaded', () => {
  function bindLikeButtons() {
    document.querySelectorAll('.btn-like').forEach(btn => {
      btn.onclick = async e => {
        e.preventDefault();
        const postId = btn.dataset.post;
        const token = "{{ csrf_token() }}";
        const url = `/posts/${postId}/like`;
        const res = await fetch(url, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({})
        });
        if (!res.ok) return alert('Error toggling like');
        const data = await res.json();
        document.getElementById(`likes-count-${postId}`).innerHTML = `
          <i class="bi bi-hand-thumbs-up-fill text-primary"></i>
          <a class="show-likers text-primary" data-post="${postId}">${data.likes_count} Likes</a>`;
        if (data.likers && Array.isArray(data.likers)) {
          document.getElementById(`likers-list-${postId}`).innerHTML =
            data.likers.map(user => `<div class="likers-list-user"><i class="bi bi-person-fill"></i> ${user}</div>`).join('');
        }
        bindLikers();
        bindLikeButtons();
        btn.classList.toggle('liked', data.liked);
        btn.querySelector('i').className = data.liked ? 'bi bi-hand-thumbs-up-fill' : 'bi bi-hand-thumbs-up';
      };
    });
  }

  function bindCommentToggles() {
    document.querySelectorAll('.btn-comment-toggle').forEach(btn => {
      btn.onclick = () => {
        const postId = btn.dataset.post;
        const wrapper = document.getElementById(`comments-${postId}`);
        wrapper.style.display = (wrapper.style.display === 'none' || !wrapper.style.display) ? 'block' : 'none';
      };
    });
  }

  function bindDeletePostForms() {

  document.querySelectorAll('.delete-post-form').forEach(form => {
     
    const btn = form.querySelector('.delete-post-btn');
    if (btn && !btn.dataset.bound) {
      btn.dataset.bound = true;
      btn.addEventListener('click', async e => {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this post?')) return;

        const postCard = form.closest('.fb-post-card');
        const token = form.querySelector('input[name="_token"]').value;

        // Use FormData instead of JSON
        const data = new FormData();
        data.append('_method', 'DELETE');

        try {
          const res = await fetch(form.action, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': token,
            },
            body: data
          });

          if (res.ok) {
            postCard.remove();
          } else {
            const text = await res.text();
            alert('Failed to delete post: ' + text);
          }
        } catch (err) {
          alert('Error deleting post: ' + err.message);
        }
      });
    }
  });
}

  function bindDeleteCommentForms() {
    document.querySelectorAll('.delete-comment-form').forEach(form => {
      if (!form.dataset.bound) {
        form.dataset.bound = true;
        form.onsubmit = async e => {
          e.preventDefault();
          if (!confirm('Are you sure you want to delete this comment?')) return;
          const token = form.querySelector('input[name="_token"]').value;
          const commentId = form.dataset.commentId;
          const postId = form.dataset.postId;

          const response = await fetch(form.action, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': token,
              'Accept': 'application/json',
              'Content-Type': 'application/json'
            },
            body: JSON.stringify({ _method: 'DELETE' })
          });
          if (response.ok) {
            document.getElementById(`comment-${commentId}`)?.remove();
            const countEl = document.getElementById(`comments-count-${postId}`);
            if (countEl) countEl.textContent = Math.max(0, parseInt(countEl.textContent) - 1);
          } else {
            alert('Failed to delete comment');
          }
        };
      }
    });
  }

  function bindLikers() {
    document.querySelectorAll('.show-likers').forEach(link => {
      link.onclick = () => {
        const postId = link.dataset.post;
        const box = document.getElementById('likers-list-' + postId);
        box.style.display = (box.style.display === 'none' || !box.style.display) ? 'block' : 'none';
      };
    });
  }

  function bindCommentForms(){
    document.querySelectorAll('.comment-form').forEach(form => {
      if (!form.dataset.bound) {
        form.dataset.bound = true;
        form.onsubmit = async e => {
          e.preventDefault();
          const postCard = form.closest('.fb-post-card');
          const postId = form.dataset.post;
          const ownerId = postCard.dataset.owner;
          const input = form.querySelector('input[name="body"]');
          const body = input.value.trim();
          if (!body) return;
          const token = "{{ csrf_token() }}";

          try {
            const res = await fetch(form.action, {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
              },
              body: JSON.stringify({ body })
            });
            if (!res.ok) throw new Error('Failed to post comment');
            const comment = await res.json();

            let badge = '';
            if (comment.user && comment.user.id == ownerId) {
              badge = '<span class="badge-owner" title="Post owner">Owner</span>';
            }

            const html = `
              <div class="comment mb-2 border-bottom pb-1" id="comment-${comment.id}">
                <strong>${comment.user.name} ${badge}</strong>
                <small class="text-muted"> just now</small>
                <div>${comment.body}</div>
                <form method="POST" action="/comments/${comment.id}" class="d-inline delete-comment-form" data-comment-id="${comment.id}" data-post-id="${postId}">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-link btn-sm text-danger p-0">Delete</button>
                </form>
              </div>`;

            const commentList = form.closest('.comments-wrapper').querySelector('.existing-comments');
            commentList.insertAdjacentHTML('afterbegin', html);

            const countEl = document.getElementById(`comments-count-${postId}`);
            if (countEl) countEl.textContent = parseInt(countEl.textContent) + 1;

            input.value = '';
            bindDeleteCommentForms();
            bindCommentToggles();

          } catch (err) {
            alert('Error: ' + err.message);
          }
        };
      }
    });
  }

  // Initial bind
  bindLikeButtons();
  bindCommentToggles();
  bindDeletePostForms();
  bindDeleteCommentForms();
  bindLikers();
  bindCommentForms();

  const loadMoreBtn = document.getElementById('loadMoreBtn');
  if (loadMoreBtn) {
    loadMoreBtn.addEventListener('click', async () => {
      const nextUrl = loadMoreBtn.getAttribute('data-next-url');
      if (!nextUrl) return;

      try {
        const res = await fetch(nextUrl, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        if (!res.ok) throw new Error('Failed to load more posts.');
        const html = await res.text();

        const container = document.getElementById('posts-container');
        container.insertAdjacentHTML('beforeend', html);

        if (html.trim() === '') {
          loadMoreBtn.style.display = 'none';
        } else {
          let newUrl = new URL(nextUrl);
          let currentPage = parseInt(newUrl.searchParams.get('page'));
          if (!currentPage) currentPage = 1;
          const newPage = currentPage + 1;
          newUrl.searchParams.set('page', newPage);
          loadMoreBtn.setAttribute('data-next-url', newUrl.toString());
        }

        // Rebind events after loading more posts
        bindLikeButtons();
        bindCommentToggles();
        bindDeletePostForms();
        bindDeleteCommentForms();
        bindLikers();
        bindCommentForms();

      } catch (error) {
        alert(error.message);
      }
    });
  }
});
</script>
<script type="text/javascript">
  document.addEventListener('DOMContentLoaded', function(){
    document.querySelectorAll('.edit-post-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // Set form action
            document.getElementById('edit-post-form').action = '/posts/' + this.dataset.id;

            // Populate modal fields
            document.getElementById('edit-post-title').value = this.dataset.title || '';
            document.getElementById('edit-post-content').value = this.dataset.content || '';
            document.getElementById('edit-post-topic').value = this.dataset.topic || '';
            // Show image if exists
            const img = document.getElementById('edit-post-image-preview');
            if(this.dataset.image) {
                img.src = this.dataset.image;
                img.style.display = 'block';
            } else {
                img.src = '';
                img.style.display = 'none';
            }
        });
    });
});

</script>
@endsection

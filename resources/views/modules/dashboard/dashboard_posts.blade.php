@foreach($posts as $post)
<div class="fb-post-card mb-4 p-3 rounded-3 shadow-sm bg-white border" data-owner="{{ $post->user_id }}">
  <div class="d-flex justify-content-between mb-2">
    <div class="d-flex align-items-center gap-2">
      <img src="{{ $post->user->image ? asset('storage/images/user_image/'.$post->user->image) : asset('assets/images/users/7.png') }}"
           class="fb-avatar rounded-circle border" width="45" height="45" alt="{{ $post->user->name }}">
      <div>
        <span class="fw-semibold">{{ $post->user->name }}</span><br>
        <span class="text-muted small">{{ $post->created_at->diffForHumans() }}</span>
      </div>
    </div>
    @if(auth()->id() === $post->user_id)
    <div class="dropdown">
      <button class="btn btn-sm border-0" data-bs-toggle="dropdown"><i class="bi bi-three-dots"></i></button>
      <ul class="dropdown-menu">
        <li>
          <a href="#"
             class="dropdown-item edit-post-link"
             data-bs-toggle="modal"
             data-bs-target="#editPostModal"
             data-id="{{ $post->id }}"
             data-title="{{ $post->title }}"
             data-content="{{ htmlentities($post->content) }}"
             data-topic="{{ $post->topic }}"
             data-image="{{ $post->image ? asset('storage/' . $post->image) : '' }}">
            <i class="bi bi-pencil-square me-1"></i> Edit
          </a>
        </li>
        <li>
          <form method="POST" action="{{ route('posts.destroy', $post) }}" class="delete-post-form">
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
          @if(auth()->id() === $comment->user_id)
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

@extends('admin.layouts.app2')
@section('title','Manage Comments')

@section('content')
<div class="userlist-card">
    <h2>Comments for Post: {{ $post->title }}</h2>

    @php $slNo = ($comments->currentPage() - 1) * $comments->perPage(); @endphp
    <table class="users-table-modern">
        <thead>
            <tr>
                <th>Sl No</th>
                <th>User</th>
                <th>Comment</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        @foreach($comments as $comment)
            @php $slNo++; @endphp
            <tr>
                <td>{{ $slNo }}</td>
                <td>{{ $comment->user->name }}</td>
                <td>{{ $comment->body }}</td>
                <td>
                    @if($comment->is_blocked)
                        <span class="status-badge inactive">Blocked</span>
                    @else
                        <span class="status-badge active">Active</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('admin.comments.toggleStatus',$comment) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="user-btn-action {{ $comment->is_blocked?'activate':'block' }}">
                            {{ $comment->is_blocked?'Activate':'Block' }}
                        </button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="mt-4">{{ $comments->links() }}</div>
</div>
@endsection

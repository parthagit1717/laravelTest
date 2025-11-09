<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\Post;

class CommentController extends Controller
{
    // List all comments with eager loading
    public function index(Request $request)
    {
        // Base query with eager loading
        $query = Comment::with([
            'user:id,name',   // Load only id and name from users
            'post:id,title'   // Load only id and title from posts
        ]);

        // Filter by post
        if ($request->filled('post_id')) {
            $query->where('post_id', $request->post_id);
        }

        // Filter by user name
        if ($request->filled('user_name')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->user_name.'%');
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_blocked', $request->status);
        }

        // Order by latest first
        $comments = $query->orderBy('created_at', 'desc')->paginate(10);

        // Load posts for dropdown filter (minimal columns)
        $posts = Post::select('id', 'title')->get();

        return view('admin.modules.comments.index', compact('comments', 'posts'));
    }

    // Block / Unblock comment
    public function toggleStatus(Comment $comment)
    {
        $comment->is_blocked = !$comment->is_blocked;
        $comment->save();

        return redirect()->back()->with('success', 'Comment status updated successfully.');
    }
}

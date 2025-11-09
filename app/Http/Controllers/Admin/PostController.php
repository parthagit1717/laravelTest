<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;

class PostController extends Controller
{
    // Post listing with filter
    public function index(Request $request)
    {
        $query = Post::with('user')->withCount('likes');

        if($request->filled('title')){
            $query->where('title', 'like', '%'.$request->title.'%');
        }

        if($request->filled('topic')){
            $query->where('topic', $request->topic);
        }

        if($request->filled('status')){
            $query->where('is_blocked', $request->status);
        }

        $posts = $query->orderBy('created_at','desc')->paginate(10);

        return view('admin.modules.post.index', compact('posts'));
    }

    // Block/Unblock post
    public function toggleStatus(Post $post)
    {
        $post->is_blocked = !$post->is_blocked;
        $post->save();

        return redirect()->back()->with('success', 'Post status updated.');
    }

    // Post comments listing
    public function comments(Post $post)
    {
        $comments = $post->comments()->with('user')->orderBy('created_at','desc')->paginate(10);
        return view('admin.modules.post.comments', compact('post','comments'));
    }

    // Block/Unblock comment
    public function toggleCommentStatus(Comment $comment)
    {
        $comment->is_blocked = !$comment->is_blocked;
        $comment->save();

        return redirect()->back()->with('success','Comment status updated.');
    }
}

<?php

namespace App\Http\Controllers\Modules\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Post;

class PostController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Display user dashboard with posts
    public function index(Request $request)
    {
        // Eager loading user, likes, and comments for optimization
        $posts = Post::with('user', 'likes', 'comments')
            ->where('is_blocked', false)
            ->latest()
            ->paginate(5); // 5 posts per page

        // If AJAX request (Load More), return only the post cards HTML
        if ($request->ajax()) {
            // Render the posts HTML directly using the same Blade section
            return view('user.dashboard_posts', compact('posts'))->render();
        }

        return view('user.dashboard', compact('posts'));
    }

    // Store a new post
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:80',
            'content' => 'required|string|max:1000',
            'topic'   => 'nullable|string|max:50',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('images/user_image', 'public');
        }

        $validated['user_id'] = Auth::id();

        Post::create($validated);

        return back()->with('success', 'Post created successfully!');
    }

    // Edit post
    public function edit(Post $post)
    {
         
        return view('user.posts.edit', compact('post'));
    }

    // Update post
    public function update(Request $request, Post $post)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:80',
            'content' => 'required|string|max:1000',
            'topic'   => 'nullable|string|max:50',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($post->image) {
                Storage::disk('public')->delete($post->image);
            }
            $validated['image'] = $request->file('image')->store('images/user_image', 'public');
        }

        $post->update($validated);

        return redirect()->back()->with('success', 'Post updated successfully.');
    }

    // Delete post (soft delete)
    public function destroy(Post $post)
    {
        $post->delete();
        return back()->with('success', 'Post deleted successfully.');
    }
}

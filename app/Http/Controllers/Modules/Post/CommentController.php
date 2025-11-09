<?php
namespace App\Http\Controllers\Modules\Post;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function __construct(){ $this->middleware('auth'); }

     
	public function store(Request $request, Post $post)
	{
	    $validated = $request->validate([
	        'body' => 'required|string|max:1000',
	    ]);

	    $comment = $post->comments()->create([
	        'user_id' => auth()->id(),
	        'body'    => $validated['body'],
	    ]);

	    // Load the related user
	    $comment->load('user');

	    return response()->json([
	        'id'   => $comment->id,
	        'body' => $comment->body,
	        'user' => [
	            'id'   => (int) $comment->user->id, // force numeric ID
	            'name' => $comment->user->name,
	        ],
	        'created_at' => $comment->created_at->diffForHumans(),
	    ]);
	}



    public function update(Request $request, Comment $comment)
    {
         

        $data = $request->validate(['body' => 'required|string|max:1000']);
        $comment->update(['body' => $data['body']]);

        if ($request->wantsJson()) return response()->json($comment);
        return back()->with('success', 'Comment updated.');
    }

    public function destroy(Comment $comment)
    {
         
        $comment->delete();
	    if(request()->wantsJson()){
	        return response()->json(['success' => true]);
	    }
	    return back()->with('success', 'Comment deleted.');
    }
}

<?php
namespace App\Http\Controllers\Modules\Post;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostLikeController extends Controller
{
    public function __construct(){ $this->middleware('auth'); }

    // toggle like
    public function toggle(Post $post)
	{
	    $userId = Auth::id();

	    $like = $post->likes()->where('user_id', $userId)->first();

	    if ($like) {
	        $like->delete();
	        $liked = false;
	    } else {
	        $post->likes()->create(['user_id' => $userId]);
	        $liked = true;
	    }

	    // Get all likers with names
	    $likers = $post->likes()->with('user')->get()->pluck('user.name');

	    return response()->json([
	        'liked' => $liked,
	        'likes_count' => $post->likes()->count(),
	        'likers' => $likers
	    ]);
	}
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Show list of users with filters
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status === 'active' ? 1 : 2);
        }

        $users = $query->paginate(10)->withQueryString();
        return view('admin.modules.user.index', compact('users'));
    }

    


    /**
     * Toggle user status (active/block)
     */
    public function toggleStatus(User $user)
    {
        if ($user->user_type == 1) {
            return redirect()->back()->with('error', 'Cannot change status of SuperAdmin.');
        }

        $user->status = $user->status == 1 ? 2 : 1;
        $user->save();

        return redirect()->back()->with('success', 'User status updated successfully.');
    }

    /**
     * Verify user email
     */
     
    public function verifyEmail(User $user)
    {
        if ($user->user_type == 1) {
            return redirect()->back()->with('error', 'Cannot verify SuperAdmin.');
        }

        if ($user->email_verified_at) {
            return redirect()->back()->with('info', 'User already verified.');
        }

        $user->email_verified_at = now();
        $user->status = 1; // Activate user on verification
        $user->save();

        return redirect()->back()->with('success', 'User email verified successfully.');
    }
}

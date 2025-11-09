<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Modules\Dashboard\DashboardController; 
use App\Http\Controllers\Modules\User\EditProfileController;
use App\Http\Controllers\Modules\Product\ProductController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Modules\Post\PostController;
use App\Http\Controllers\Modules\Post\PostLikeController;
use App\Http\Controllers\Modules\Post\CommentController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

// Admin guest routes (login, forgot password)
Route::prefix('admin')->name('admin.')->group(function () {

    Route::middleware('guest.admin')->group(function () {
        // Login
        Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
        Route::post('login', [LoginController::class, 'login'])->name('login.submit');

        // Password reset (forgot)
        Route::get('password/reset', [App\Http\Controllers\Admin\Auth\ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
        Route::post('password/email', [App\Http\Controllers\Admin\Auth\ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');

        // Reset
        Route::get('password/reset/{token}', [App\Http\Controllers\Admin\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
        Route::post('password/reset', [App\Http\Controllers\Admin\Auth\ResetPasswordController::class, 'reset'])->name('password.update');
    });

    // Authenticated admin routes
    Route::middleware('auth.admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::post('logout', [LoginController::class, 'logout'])->name('logout');

        // Users
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('admin.users.toggleStatus');
        Route::post('/users/{user}/verify', [UserController::class, 'verifyEmail'])->name('admin.users.verifyEmail');
        Route::get('/users/{user}', [UserController::class, 'show'])->name('admin.users.show');

        // Posts
        Route::get('posts', [App\Http\Controllers\Admin\PostController::class,'index'])->name('posts.index');
        Route::post('posts/{post}/toggle-status', [App\Http\Controllers\Admin\PostController::class,'toggleStatus'])->name('posts.toggleStatus');

        // Comments
        Route::get('comments', [App\Http\Controllers\Admin\CommentController::class, 'index'])->name('comments.index');
        Route::post('comments/{comment}/toggle', [App\Http\Controllers\Admin\CommentController::class, 'toggleStatus'])->name('comments.toggleStatus');
    });
});

// --- GOOGLE SOCIALITE ROUTES ---
Route::get('auth/google/redirect', [GoogleController::class, 'redirectToGoogle'])->name('google.redirect');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback']);

// User verification & password reset routes
Route::get('user-verify/{email_vcode}/{id}', [RegisterController::class, 'verifyEmail'])->name('user.verify');

Route::get('password-reset-from', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('user.password.request');
Route::post('password-email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('user.password.email');

Route::get('password-reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('user.password.reset');
Route::post('password-update', [ResetPasswordController::class, 'reset'])->name('user.password.update');



// Authenticated user routes
Route::group(['middleware' => ['auth']], function() {

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('settings', [SettingController::class, 'settings'])->name('settings');

    // User edit profile
    Route::get('profile', [EditProfileController::class, 'userProfile'])->name('profile');
    Route::get('edit-profile', [EditProfileController::class, 'editProfile'])->name('edit_profile');
    Route::post('update-profile', [EditProfileController::class, 'updateProfile'])->name('update_profile');
    Route::post('update-password', [EditProfileController::class, 'updatePassword'])->name('update_password');
    Route::get('remove-profile-image/{user?}', [EditProfileController::class, 'removeProfileImage'])->name('remove_profile_image');

    // Posts
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
    Route::delete('/posts/{post}', [PostController::class, 'destroy'])->name('posts.destroy');
    Route::get('/dashboard/posts', [PostController::class, 'loadMorePosts'])->name('dashboard.posts.load');

    // Comments
    Route::post('/posts/{post}/comments', [CommentController::class, 'store'])->name('posts.comments.store');
    Route::put('/comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('/comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('/posts/{post}/like', [PostLikeController::class, 'toggle'])->name('posts.like'); 
});

<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Modules\Dashboard\DashboardController;
use App\Http\Controllers\Modules\Setting\SettingController;
use App\Http\Controllers\Modules\Admin\ManageUserController;
use App\Http\Controllers\Modules\Admin\ManageSubscriptionController;
use App\Http\Controllers\Modules\Admin\TransactionController;
use App\Http\Controllers\Modules\User\EditProfileController;
use App\Http\Controllers\Modules\User\Subscription\SubscriptionController;
use App\Http\Controllers\Modules\TaskProcesser\TaskProcesserController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('auth.login');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// Route::get('product-cat', [App\Http\Controllers\HomeController::class, 'productCat'])->name('product_cat');

Route::get('user-verify/{email_vcode}/{id}', [RegisterController::class, 'verifyEmail'])->name('user.verify');

Route::group(['middleware' => ['auth']],function(){

    Route::group(['middleware' => 'Subscription'], function() {
    	Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    	Route::get('settings', [SettingController::class, 'settings'])->name('settings');

        // EKM Connect....
        Route::post('authekmservice', [SettingController::class, 'authekmservice'])->name('authekmservice');


         
    	// User edit profile...
    	Route::get('profile', [EditProfileController::class, 'userProfile'])->name('profile');
    	Route::get('edit-profile', [EditProfileController::class, 'editProfile'])->name('edit_profile');
    	Route::post('update-profile', [EditProfileController::class, 'updateProfile'])->name('update_profile');
    	Route::post('update-pasword', [EditProfileController::class, 'updatePassword'])->name('update_pasword');

        // Task Processer Routes...

        Route::get('ekm-queue-task-generate', [TaskProcesserController::class, 'ekmImportprocessqueuetaskgenerate'])->name('ekmImportprocessqueuetaskgenerate');
        Route::get('ekm-queue-task-process', [TaskProcesserController::class, 'ekmImportprocessqueuetask'])->name('ekmImportprocessqueuetask');


    });


    // Subscription routes.....
    Route::get('subscription', [SubscriptionController::class, 'subPlanList'])->name('subPlanList');
    Route::get('add-subscription/{user_id}/{sub_id}', [SubscriptionController::class, 'addSubscription'])->name('add_subs');

    Route::get('subscription-over', [SubscriptionController::class, 'subPlanError'])->name('subscribe_error');
    Route::get('success', [SubscriptionController::class, 'success'])->name('success');
    Route::post('create-payment-intent', [SubscriptionController::class, 'createPaymentIntent'])->name('create_payment_intent');
    Route::post('stripe', [SubscriptionController::class, 'stripePost'])->name('stripe.post');

	// Admin Routes....
	Route::get('manage-user', [ManageUserController::class, 'manageUser'])->name('manage.user');
	Route::get('alluser', [ManageUserController::class, 'allUser'])->name('alluser');
    Route::get('viewuser/{id}', [ManageUserController::class, 'viewUser'])->name('view_user'); 
    Route::get('inactiveuser/{id}', [ManageUserController::class, 'inactiveUser'])->name('inactiveuser');
    Route::get('activeuser/{id}', [ManageUserController::class, 'activeuser'])->name('activeuser');
    Route::post('store-user', [ManageUserController::class, 'storeUser'])->name('store_user');

    Route::get('manage-transaction', [TransactionController::class, 'Transaction'])->name('transaction');
    Route::get('alltransaction', [TransactionController::class, 'allTransaction'])->name('alltransaction');

    Route::get('editemp/{id}', [ManageUserController::class, 'editEmp'])->name('edit.emp');

    // For manage subscription route for Admin....
    Route::get('manage-subscription', [ManageSubscriptionController::class, 'manageSubscription'])->name('manage_subs');

    Route::get('allsubscription', [ManageSubscriptionController::class, 'allSubscription'])->name('allSubscription');
    Route::post('store-subscription', [ManageSubscriptionController::class, 'storeSubscription'])->name('store_subscription');
    Route::get('editsubscription/{id}', [ManageSubscriptionController::class, 'editSubscription'])->name('edit.subscription');
    Route::get('inactivesubscription/{id}', [ManageSubscriptionController::class, 'inactiveSubscription'])->name('inactiveSubscription');
    Route::get('activesubscription/{id}', [ManageSubscriptionController::class, 'activeSubscription'])->name('activeSubscription'); 
});
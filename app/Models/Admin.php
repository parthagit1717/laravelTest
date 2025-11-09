<?php

namespace App\Models;

use App\Notifications\AdminResetPasswordNotification; // Import custom notification
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\CanResetPassword; // Import the essential interface

class Admin extends Authenticatable implements CanResetPassword // CRITICAL: Implement the interface
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     * @var string
     */
    protected $table = 'admins';

    /**
     * The authentication guard for this model.
     * @var string
     */
    protected $guard = 'admin';

    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    
    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        // THIS IS THE FIX: Tell the model to use the custom notification
        $this->notify(new AdminResetPasswordNotification($token));
    }
}
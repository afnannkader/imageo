<?php

namespace App\Models;

use App\Notifications\AdminResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $guard = 'admins';

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'avatar',
        'password',
        'google2fa_status',
        'google2fa_secret',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getNameAttribute()
    {
        if ($this->firstname && $this->lastname) {
            return $this->firstname . ' ' . $this->lastname;
        } elseif ($this->email) {
            $emailUsername = explode('@', $this->email);
            return $emailUsername[0];
        }
    }

    public function getGoogle2faSecretAttribute($value)
    {
        return decrypt($value);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new AdminResetPasswordNotification($token));
    }

    public function blogArticles()
    {
        return $this->hasMany(Blog_Article::class, 'id');
    }

    public function supportReplies()
    {
        return $this->hasMany(SupportReply::class, 'id');
    }
}

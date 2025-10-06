<?php

namespace App\Models;

use App\Notifications\UserResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    public function isSubscribed()
    {
        return $this->subscription;
    }

    public function isDataCompleted()
    {
        if (!$this->email || !$this->password) {
            return false;
        }
        return true;
    }

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'address',
        'avatar',
        'password',
        'facebook_id',
        'google_id',
        'microsoft_id',
        'vkontakte_id',
        'google2fa_status',
        'google2fa_secret',
        'is_viewed',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google2fa_secret',
    ];

    protected $casts = [
        'address' => 'object',
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

    public function deleteGeneratedImages()
    {
        foreach ($this->generated_images as $image) {
            $image->deleteResources();
        }
    }

    public function getGoogle2faSecretAttribute($value)
    {
        return decrypt($value);
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new UserResetPasswordNotification($token));
    }

    public function sendEmailVerificationNotification()
    {
        if (settings('actions')->email_verification_status) {
            $this->notify(new VerifyEmailNotification());
        }
    }

    public function logs()
    {
        return $this->hasMany(UserLog::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function generated_images()
    {
        return $this->hasMany(GeneratedImage::class);
    }
}
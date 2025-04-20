<?php

namespace App\Models;

use App\Enum\UserGender;
use App\Notifications\VerifyEmailQueue;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Scout\Searchable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, Searchable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'gender' => UserGender::class,
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name) => Str::of($name)->substr(0, 1))
            ->implode('');
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailQueue);
    }

    public function doctor()
    {
        return $this->hasOne(Doctor::class);
    }

    public function therapies()
    {
        return $this->hasMany(Therapy::class, 'patient_id');
    }

    public function rooms()
    {
        return $this->hasMany(ChatRoom::class);
    }

    public function sent()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function received()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    public function toSearchableArray()
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
        ];
    }
}

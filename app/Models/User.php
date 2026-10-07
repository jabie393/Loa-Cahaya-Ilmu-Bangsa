<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use App\Models\UserPlagiarismQuota;
use App\Models\PlagiarismCheck;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone', 'is_member', 'pin', 'avatar_url'])]
#[Hidden(['password', 'remember_token', 'pin'])]
class User extends Authenticatable implements FilamentUser, HasAvatar
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

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
            'is_member' => 'boolean',
        ];
    }

    public function isMember(): bool
    {
        if ($this->hasRole('ryu_dev')) {
            return true;
        }

        return (bool) $this->is_member;
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->assignRole('panel_user');
        });
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function userQuota(): HasOne
    {
        return $this->hasOne(UserQuota::class);
    }

    public function userPlagiarismQuota(): HasOne
    {
        return $this->hasOne(UserPlagiarismQuota::class);
    }

    public function plagiarismChecks(): HasMany
    {
        return $this->hasMany(PlagiarismCheck::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasRole(['super_admin', 'panel_user', 'ryu_dev']);
        }

        return true;
    }

    /**
     * Send password reset notification using the application's notification with Filament URL.
     */
    public function sendPasswordResetNotification($token): void
    {
        $notification = app(\App\Notifications\ResetPasswordNotification::class, ['token' => $token]);
        if (class_exists(\Filament\Facades\Filament::class)) {
            $notification->url = \Filament\Facades\Filament::getResetPasswordUrl($token, $this);
        }
        $this->notify($notification);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::disk('public')->url($this->avatar_url) : null;
    }
}

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
use Filament\Panel;
use App\Models\UserPlagiarismQuota;
use App\Models\PlagiarismCheck;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'phone'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
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
        ];
    }

    protected static function booted(): void
    {
        static::created(function (User $user) {
            $user->assignRole('panel_user');

            // Create default quota
            $user->userQuota()->create([
                'daily_limit' => config('quota.default_daily_limit', 2),
                'daily_used' => 0,
                'bonus_tokens' => 0,
                'total_used' => 0,
            ]);
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
            return $this->hasRole(['super_admin', 'panel_user']);
        }

        return true;
    }
}

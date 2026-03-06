<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use App\Modules\Shared\Data\UserViewData;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property UserRole $role
 * @property Carbon|null $email_verified_at
 */
final class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use MustVerifyEmail;
    use Notifiable;

    protected $guarded = [];

    protected $hidden = ['password', 'remember_token'];

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'users.'.$this->id.'.notifications';
    }

    public function toViewData(): UserViewData
    {
        return UserViewData::fromModel($this);
    }

    /**
     * @return array<string, class-string<UserRole>|string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }
}

<?php

namespace App\Models;

use App\Models\Enums\RoleSlug;
use App\Traits\HasRolesAndPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use HasRolesAndPermissions;
    use HasTeams;
    use LogsActivity;
    use Notifiable;
    use SoftDeletes;
    use TwoFactorAuthenticatable;

    /** Mass-assignable attributes. */
    protected $fillable = [
        'name',
        'email',
        'password',
        'current_team_id',
        'channel_id',
        'notification_emails',
        'external_customer_id',
    ];

    /** Hidden attributes for arrays. */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /** Appended accessors. */
    protected $appends = [
        'profile_photo_url',
    ];

    /** Attribute casting. */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'notification_emails' => 'array',
            'external_customer_id' => 'string', // cast as string
        ];
    }

    /** Relationship: a user belongs to a channel (nullable). */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    /** Scope: only API users (role = api-user) with non-null channel_id. */
    public function scopeApi($query)
    {
        return $query
            ->whereNotNull('channel_id')
            ->whereHas('roles', fn ($q) => $q->where('slug', RoleSlug::API_USER->value));
    }

    /** Scope: users of a specific channel (accepts Channel model or id). */
    public function scopeOfChannel($query, $channel)
    {
        $channelId = $channel instanceof Channel ? $channel->getKey() : $channel;
        return $query->where('channel_id', $channelId);
    }

    /** Convenience accessor: plain channel token without "id|" prefix. */
    public function getChannelPlainTokenAttribute(): ?string
    {
        return $this->channel?->plain_access_token;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('user');
    }
}

<?php

namespace App\Models;

use App\Models\Enums\RoleSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\Sanctum;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Channel extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    /** Mass-assignable attributes. */
    protected $fillable = [
        'name',
        'description',
        'token_id',
        'access_token',
        'user_id',
    ];

    /** Relationship: optional owner via Sanctum personal access token model. */
    public function token(): BelongsTo
    {
        return $this->belongsTo(Sanctum::$personalAccessTokenModel);
    }

    /** Relationship: optional "owner" user if you use channels.user_id. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Relationship: all users bound to this channel. */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Relationship: only API users of this channel (role = api-user). */
    public function apiUsers(): HasMany
    {
        return $this->hasMany(User::class)
            ->whereNotNull('channel_id')
            ->whereHas('roles', fn ($q) => $q->where('slug', RoleSlug::API_USER->value));
    }

    /** Accessor: numeric part before '|' (e.g., '123|abc' -> '123'). */
    public function getAccessTokenIdAttribute(): ?string
    {
        if (! $this->access_token) {
            return null;
        }

        return Str::before($this->access_token, '|');
    }

    /** Accessor: plain token after '|' (e.g., '123|abc' -> 'abc'). */
    public function getPlainAccessTokenAttribute(): ?string
    {
        if (! $this->access_token) {
            return null;
        }

        return Str::contains($this->access_token, '|')
            ? Str::after($this->access_token, '|')
            : $this->access_token;
    }

    /** Soft-delete with name suffix to keep uniqueness/traceability. */
    public function delete()
    {
        $this->name .= '_deleted_'.now()->timestamp;
        $this->save();

        return parent::delete();
    }

    /** Finder by full access token (if you store it as-is). */
    public function findForToken($token)
    {
        return $this->where('access_token', $token)->first();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->useLogName('channel');
    }
}

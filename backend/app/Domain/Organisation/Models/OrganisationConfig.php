<?php

namespace App\Domain\Organisation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganisationConfig extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_id',
        'primary_color',
        'secondary_color',
        'button_color',
        'tax_rate_id',
        'admins_require_2fa',
        'max_days_between_2fa',
        'social_facebook',
        'social_twitter',
        'social_instagram',
        'social_linkedin',
        'banner',
        'introduction',
        'about',
        'show_subscription_button',
        'show_events',
        'show_new_members',
    ];

    protected function casts(): array
    {
        return [
            'admins_require_2fa'       => 'boolean',
            'show_subscription_button' => 'boolean',
            'show_events'              => 'boolean',
            'show_new_members'         => 'boolean',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}

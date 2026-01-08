<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrganisationConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'primary_color',
        'secondary_color',
        'button_color',
        'tax_rate_id',
        'member_authorization',
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

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function taxRate()
    {
        return $this->belongsTo(TaxRate::class);
    }
}

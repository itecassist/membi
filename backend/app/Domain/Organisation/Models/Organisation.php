<?php

namespace App\Domain\Organisation\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'seo_name',
        'email',
        'phone',
        'logo',
        'website',
        'description',
        'timezone',
        'currency',
        'locale',
        'subscription_description',
        'free_trail',
        'free_trail_end_date',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active'           => 'boolean',
            'free_trail'          => 'boolean',
            'free_trail_end_date' => 'date',
        ];
    }

    public function members(): HasMany
    {
        return $this->hasMany(\App\Domain\Member\Models\Member::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(\App\Domain\Subscription\Models\Subscription::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(\App\Domain\Member\Models\Group::class);
    }

    public function config(): HasOne
    {
        return $this->hasOne(OrganisationConfig::class);
    }

    public function configMember(): HasOne
    {
        return $this->hasOne(OrganisationConfigMember::class);
    }

    public function configSubscription(): HasOne
    {
        return $this->hasOne(OrganisationConfigSubscription::class);
    }

    public function configFinancial(): HasOne
    {
        return $this->hasOne(OrganisationConfigFinancial::class);
    }

    public function configAdmins(): HasMany
    {
        return $this->hasMany(OrganisationConfigAdmin::class);
    }

    public function emailTemplates(): HasMany
    {
        return $this->hasMany(\App\Domain\Communication\Models\EmailTemplate::class);
    }

    public function organisationLists(): HasMany
    {
        return $this->hasMany(OrganisationList::class);
    }
}

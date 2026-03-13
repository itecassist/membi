<?php

namespace App\Domain\Communication\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Contact extends Model
{
    use HasUuids;

    protected $fillable = [
        'contactable_id',
        'contactable_type',
        'name',
        'email',
        'mobile_phone',
        'relation',
    ];

    public function contactable(): MorphTo
    {
        return $this->morphTo();
    }
}

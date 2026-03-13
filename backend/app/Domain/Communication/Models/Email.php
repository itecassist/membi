<?php

namespace App\Domain\Communication\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Email extends Model
{
    use HasUuids;

    protected $fillable = [
        'emailable_id',
        'emailable_type',
        'subject',
        'eml',
        'from',
        'to',
    ];

    public function emailable(): MorphTo
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Domain\Form\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualForm extends Model
{
    use HasUuids;

    protected $fillable = [
        'category',
        'name',
    ];

    public function fields(): HasMany
    {
        return $this->hasMany(VirtualField::class);
    }

    public function records(): HasMany
    {
        return $this->hasMany(VirtualRecord::class);
    }
}

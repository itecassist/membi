<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lookup extends Model
{
    use HasFactory;

    protected $fillable = [
        'organisation_id',
        'name',
        'description',
        'value',
    ];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function scopeOrganisation($query, $organisationId)
    {
        return $query->where('organisation_id', $organisationId);
    }

    public function scopeName($query, $name)
    {
        return $query->where('name', $name);
    }
}

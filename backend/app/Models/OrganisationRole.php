<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganisationRole extends Model
{
    use HasFactory;

    protected $fillable = ['organisation_id', 'name', 'description', 'permissions'];

    public function organisation()
    {
        return $this->belongsTo(Organisation::class);
    }

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}

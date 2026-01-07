<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VirtualForm extends Model
{
    use HasFactory;
    protected $fillable = ['category', 'name'];

    public function fields():HasMany
    {
        return $this->hasMany(VirtualField::class);
    }
}

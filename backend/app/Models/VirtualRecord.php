<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirtualRecord extends Model
{
    use HasFactory;
    protected $fillable = ['organisation_id', 'member_id', 'virtual_form_id', 'data'];
    protected $casts = [
        'data' => 'array',
    ];
    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }
}

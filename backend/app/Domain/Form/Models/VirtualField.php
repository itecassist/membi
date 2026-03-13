<?php

namespace App\Domain\Form\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VirtualField extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'virtual_form_id',
        'field_name',
        'description',
        'required',
        'type',
        'options',
        'gdpr_sensitive',
        'active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'required'       => 'boolean',
            'gdpr_sensitive' => 'boolean',
            'active'         => 'boolean',
            'options'        => 'array',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(VirtualForm::class, 'virtual_form_id');
    }
}

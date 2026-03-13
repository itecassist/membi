<?php

namespace App\Domain\Form\Models;

use App\Domain\Member\Models\Member;
use App\Domain\Organisation\Models\Organisation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirtualRecord extends Model
{
    use HasUuids;

    protected $fillable = [
        'organisation_id',
        'member_id',
        'virtual_form_id',
        'data',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(VirtualForm::class, 'virtual_form_id');
    }
}

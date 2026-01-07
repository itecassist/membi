<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VirtualField extends Model
{
    use HasFactory;
    protected $fillable = ['virtual_form_id', 'field_name', 'description', 'required', 'type', 'options', 'order', 'gdpr_sensitive', 'active'];
    protected $casts = ['options' => 'array'];

    public function form():BelongsTo
    {
        return $this->belongsTo(VirtualForm::class);
    }

}

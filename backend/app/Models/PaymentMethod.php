<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends Model
{
    use HasFactory;
    protected $fillable = [
        'type','explanation','is_active','default','details','surcharge_percentage','surcharge_fixed','accounting_code_id','checkout_text','success_text','make_admin_payment_method','default_payment_method'
        ];

    public function accountingCode(): BelongsTo
    {
        return $this->belongsTo(AccountingCode::class);
    }
}

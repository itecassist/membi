<?php

namespace App\Domain\Payment\Models;

use App\Domain\Member\Models\Member;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class MemberPaymentMethod extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'member_id',
        'payment_method_id',
        'label',
        'gateway_reference',
        'metadata',
        'is_active',
        'expires_at',
    ];

    protected $hidden = ['metadata'];

    protected function casts(): array
    {
        return [
            'is_active'  => 'boolean',
            'expires_at' => 'datetime',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    /**
     * Decrypt and return the metadata array (e.g. mandate details from GoCardless).
     */
    public function getDecryptedMetadata(): array
    {
        if (empty($this->metadata)) {
            return [];
        }

        return json_decode(Crypt::decryptString($this->metadata), true) ?? [];
    }

    public function setEncryptedMetadata(array $data): void
    {
        $this->metadata = Crypt::encryptString(json_encode($data));
        $this->save();
    }
}

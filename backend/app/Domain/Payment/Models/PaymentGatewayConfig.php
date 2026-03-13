<?php

namespace App\Domain\Payment\Models;

use App\Domain\Organisation\Models\Organisation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class PaymentGatewayConfig extends Model
{
    use HasUuids, SoftDeletes;

    protected $fillable = [
        'organisation_id',
        'type',
        'is_active',
        'config',
    ];

    protected $hidden = ['config'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Decrypt and return the config array.
     */
    public function getDecryptedConfig(): array
    {
        if (empty($this->config)) {
            return [];
        }

        return json_decode(Crypt::decryptString($this->config), true) ?? [];
    }

    /**
     * Encrypt and store the config array.
     */
    public function setEncryptedConfig(array $config): void
    {
        $this->config = Crypt::encryptString(json_encode($config));
        $this->save();
    }
}

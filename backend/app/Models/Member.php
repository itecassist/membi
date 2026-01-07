<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Member extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'title',
        'first_name',
        'last_name',
        'email',
        'mobile_phone',
        'date_of_birth',
        'gender',
        'member_number',
        'joined_at',
        'is_active',
        'roles',
        'last_login_at',
    ];

    public function organization(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

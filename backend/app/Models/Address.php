<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'addressable_id',
        'addressable_type',
        'line_1',
        'line_2',
        'line_3',
        'line_4',
        'postcode',
        'country_id',
        'zone_id',
    ];

    public function addressable()
    {
        return $this->morphTo();
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }
}

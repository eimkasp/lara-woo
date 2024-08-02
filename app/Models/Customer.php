<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'woocommerce_id',
        'first_name',
        'last_name',
        'email',
        'channel_id',
        'channel',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}


<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kodeine\Metable\Metable;

class Customer extends BaseModel
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
    // Add this method to define the badge


    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }
}


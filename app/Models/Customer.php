<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kodeine\Metable\Metable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Customer extends BaseModel
{
    use HasFactory;

    use LogsActivity;
    
    protected $fillable = [
        'woocommerce_id',
        'first_name',
        'last_name',
        'email',
        'channel_id',
        'channel',
    ];

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function latestOrder()
    {
        return $this->hasOne(Order::class)->latestOfMany();
    }

    // Helper method to get the last order date
    public function getLastOrderDateAttribute()
    {
        return $this->latestOrder?->created_at;
    }

    // Helper method to get customers with same email in different channels
    public function scopeWithDuplicateEmails($query)
    {
        return $query->whereIn('email', function ($subQuery) {
            $subQuery->select('email')
                ->from('customers')
                ->groupBy('email')
                ->havingRaw('COUNT(DISTINCT channel_id) > 1');
        });
    }

    public function getTotalSpentAttribute()
    {
        return $this->orders()->sum('total');
    }

    public function scopeWithTotalSpent($query)
    {
        return $query->addSelect([
            'total_spent' => Order::selectRaw('SUM(total)')
                ->whereColumn('customer_id', 'customers.id')
        ]);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('customers')
            ->setDescriptionForEvent(fn(string $eventName) => "Customer has been {$eventName}");
    }
}


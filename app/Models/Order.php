<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Order extends BaseModel
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'woocommerce_id',
        'channel_id',
        'channel',
        'total',
        'status',
        'customer_id',
        'billing_first_name',
        'billing_last_name',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_state',
        'billing_postcode',
        'billing_country',
        'billing_email',
        'billing_phone',
        'original_order_date',
        'shipping_method',
        'shipping_total',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_country',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function channel()
    {
        return $this->belongsTo(Channel::class);
    }

    public function getStatusColor(): string
    {
        return match ($this->status) {
            'processing' => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
            'refunded' => 'danger',
            'failed' => 'danger',
            'pending' => 'info',
            default => 'secondary',
        };
    }

    public function getFormattedBillingAddress(): string
    {
        $address = [];
        if ($this->billing_first_name || $this->billing_last_name) {
            $address[] = trim($this->billing_first_name . ' ' . $this->billing_last_name);
        }
        if ($this->billing_address_1) $address[] = $this->billing_address_1;
        if ($this->billing_address_2) $address[] = $this->billing_address_2;
        if ($this->billing_city) $address[] = $this->billing_city;
        if ($this->billing_state) $address[] = $this->billing_state;
        if ($this->billing_postcode) $address[] = $this->billing_postcode;
        if ($this->billing_country) $address[] = $this->billing_country;
        
        return implode(", ", $address);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('orders')
            ->setDescriptionForEvent(fn(string $eventName) => "Order has been {$eventName}");
    }
}


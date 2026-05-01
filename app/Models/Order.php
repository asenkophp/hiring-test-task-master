<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_SHIPPED = 'shipped';
    public const STATUS_CANCELLED = 'cancelled';

    protected $table = 'orders';

    public $relation = 'orders';

    protected $fillable = [
        'customer_id',
        'status',
        'total',
        'external_reference',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'total' => 'decimal:2',
    ];

    protected $with = ['items'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OrderLog::class);
    }

    public function getDisplayTotalAttribute(): string
    {
        $sum = 0;
        foreach ($this->items as $item) {
            $sum += $item->product->price * $item->quantity;
        }

        return number_format($sum, 2);
    }
}

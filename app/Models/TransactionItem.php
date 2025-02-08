<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'sugar_level',
        'iced_level',
        'take_away',
        'price',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getTotalAttribute()
    {
        return $this->quantity * $this->price;
    }

    public function getFormattedTotalAttribute()
    {
        return 'Rp ' . number_format($this->total, 0, ',', '.');
    }
}

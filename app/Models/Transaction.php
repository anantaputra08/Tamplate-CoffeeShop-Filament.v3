<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['gross_amount', 'order_id', 'user_id' , 'status', 'payment_type'];
    public static function generateOrderId()
    {
        $today = now()->format('dmY');
        $countToday = DB::table('transactions')
            ->whereDate('created_at', now()->toDateString())
            ->count() + 1;

        return $today . str_pad($countToday, 5, '0', STR_PAD_LEFT);
    }
    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}

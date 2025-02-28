<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Category extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'slug'];
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if (empty($category->slug) || $category->isDirty('name')) {
                $category->slug = Str::slug($category->name);
            }
        });
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }
    public function scopeActive($query)
    {
        return $query->where('deleted_at', null);
    }
    public function scopeWithProductsCount($query)
    {
        return $query->withCount('products');
    }
    
}

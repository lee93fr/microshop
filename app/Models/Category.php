<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn ($cat) => $cat->slug ??= Str::slug($cat->name));
        static::updating(function ($cat) {
            if ($cat->isDirty('name') && !$cat->isDirty('slug')) {
                $cat->slug = Str::slug($cat->name);
            }
        });
    }

    public function products() { return $this->hasMany(Product::class); }
}

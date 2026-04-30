<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'description', 'duration_minutes', 'price_cents', 'image', 'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'duration_minutes' => 'integer',
        'price_cents' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Service $service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->name);
            }
        });
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function getPriceAttribute(): float
    {
        return $this->price_cents / 100;
    }
}

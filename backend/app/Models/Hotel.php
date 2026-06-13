<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    use HasFactory;

    protected $fillable = [
        'zone', 'area', 'zone_color', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function properties(): HasMany
    {
        return $this->hasMany(HotelProperty::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function toPhpArray(): array
    {
        return [
            'zone'       => $this->zone,
            'area'       => $this->area ?? '',
            'color'      => $this->zone_color ?? '#2cb896',
            'properties' => $this->properties->map(fn ($p) => $p->toPhpArray())->toArray(),
        ];
    }
}

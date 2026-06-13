<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'package_code', 'title', 'subtitle', 'duration', 'category',
        'image_path', 'price_per_pax', 'price_label', 'min_pax',
        'includes', 'excludes', 'itinerary',
        'is_active', 'is_long_stay', 'sort_order',
    ];

    protected $casts = [
        'includes'     => 'array',
        'excludes'     => 'array',
        'itinerary'    => 'array',
        'is_active'    => 'boolean',
        'is_long_stay' => 'boolean',
        'price_per_pax'=> 'integer',
        'min_pax'      => 'integer',
        'sort_order'   => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeShortStay($query)
    {
        return $query->where('is_long_stay', false);
    }

    public function scopeLongStay($query)
    {
        return $query->where('is_long_stay', true);
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function getFormattedPriceAttribute(): string
    {
        if (!$this->price_per_pax) {
            return $this->price_label ?? 'Request Quote';
        }
        return 'Rp ' . number_format($this->price_per_pax, 0, ',', '.');
    }

    public function toPhpArray(): array
    {
        return [
            'id'          => $this->package_code,
            'title'       => $this->title,
            'subtitle'    => $this->subtitle ?? '',
            'duration'    => $this->duration ?? '',
            'category'    => $this->category ?? 'culture',
            'img'         => $this->image_path ?? '',
            'price'       => $this->price_per_pax ?? 0,
            'price_label' => $this->price_label ?? '',
            'min_pax'     => $this->min_pax ?? 2,
            'includes'    => $this->includes ?? [],
            'excludes'    => $this->excludes ?? [],
            'itinerary'   => $this->itinerary ?? [],
        ];
    }
}

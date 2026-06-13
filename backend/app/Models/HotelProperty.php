<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HotelProperty extends Model
{
    use HasFactory;

    protected $fillable = [
        'hotel_id', 'name', 'type', 'room_type', 'features',
        'price_low', 'price_high', 'breakfast', 'rating', 'review_text',
        'contact', 'image_path', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function hotel(): BelongsTo
    {
        return $this->belongsTo(Hotel::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function toPhpArray(): array
    {
        return [
            'img'     => $this->image_path ?? '',
            'name'    => $this->name,
            'type'    => $this->type ?? '',
            'room'    => $this->room_type ?? '',
            'features'=> $this->features ?? '',
            'low'     => $this->price_low ?? '0',
            'high'    => $this->price_high ?? '0',
            'bf'      => $this->breakfast ?? '',
            'rating'  => $this->rating ?? '',
            'review'  => $this->review_text ?? '',
            'contact' => $this->contact ?? '',
        ];
    }
}

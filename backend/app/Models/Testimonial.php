<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote', 'guest_name', 'guest_origin', 'experience',
        'rating', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'rating'     => 'integer',
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function toPhpArray(): array
    {
        return [
            'quote'      => $this->quote,
            'name'       => $this->guest_name ?? '',
            'origin'     => $this->guest_origin ?? '',
            'experience' => $this->experience ?? '',
        ];
    }
}

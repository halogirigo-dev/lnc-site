<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'role', 'specialization', 'years_experience', 'origin',
        'languages', 'certifications', 'bio', 'image_path',
        'is_active', 'sort_order',
    ];

    protected $casts = [
        'years_experience' => 'integer',
        'is_active'        => 'boolean',
        'sort_order'       => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function toPhpArray(): array
    {
        return [
            'name'   => $this->name,
            'role'   => $this->role ?? '',
            'spec'   => $this->specialization ?? '',
            'years'  => $this->years_experience ?? 0,
            'origin' => $this->origin ?? '',
            'lang'   => $this->languages ?? '',
            'cert'   => $this->certifications ?? '',
            'bio'    => $this->bio ?? '',
        ];
    }
}

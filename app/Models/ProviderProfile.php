<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProviderProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bio',
        'experience_years',
        'is_approved',
        'avg_rating',
        'total_reviews',
    ];

    protected $casts = [
        'is_approved'      => 'boolean',
        'avg_rating'       => 'decimal:2',
        'experience_years' => 'integer',
        'total_reviews'    => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

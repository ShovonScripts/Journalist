<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerHistory extends Model
{
    use HasFactory;

    protected $table = 'career_history';

    protected $fillable = [
        'journalist_profile_id',
        'role',
        'organization',
        'start_date',
        'end_date',
        'description',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function profile(): BelongsTo
    {
        return $this->belongsTo(JournalistProfile::class, 'journalist_profile_id');
    }

    public function isCurrent(): bool
    {
        return $this->end_date === null;
    }
}

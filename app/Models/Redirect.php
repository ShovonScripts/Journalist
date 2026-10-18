<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    use HasFactory;

    public const PERMANENT = 301;

    public const TEMPORARY = 302;

    protected $fillable = [
        'from_path',
        'to_path',
        'status_code',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
        ];
    }
}

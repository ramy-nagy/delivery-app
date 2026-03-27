<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'cast_type'];

    protected function casts(): array
    {
        return [
            'value' => 'json',
        ];
    }
}

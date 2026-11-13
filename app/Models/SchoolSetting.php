<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolSetting extends Model
{
    protected $fillable = [
        'name',
        'contact_email',
        'phone',
        'address',
    ];

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['name' => 'Madrasaa Ifaa Islaamaa'],
        );
    }
}

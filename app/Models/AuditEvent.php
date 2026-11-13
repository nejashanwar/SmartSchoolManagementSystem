<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditEvent extends Model
{
    protected $fillable = [
        'causer_id',
        'event',
        'subject_type',
        'subject_id',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
        ];
    }

    public static function record(string $event, ?Model $subject = null, array $properties = []): self
    {
        return static::query()->create([
            'causer_id' => Auth::id(),
            'event' => $event,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'properties' => $properties,
        ]);
    }

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }
}

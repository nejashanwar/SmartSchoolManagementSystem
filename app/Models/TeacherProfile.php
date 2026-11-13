<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TeacherProfile extends Model
{
    protected $fillable = ['user_id', 'phone', 'joined_on', 'employment_status'];

    protected function casts(): array
    {
        return ['joined_on' => 'date'];
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function assignments(): HasMany { return $this->hasMany(TeacherAssignment::class); }
}

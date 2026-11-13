<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class GradeScale extends Model { protected $fillable=['label','min_percentage','max_percentage','sort_order','is_active']; protected function casts(): array { return ['min_percentage'=>'decimal:2','max_percentage'=>'decimal:2','is_active'=>'boolean']; } }

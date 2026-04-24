<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    protected $fillable = [
        'name',
        'latitude',
        'longitude',
        'radius_meters',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'radius_meters' => 'integer',
    ];

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}

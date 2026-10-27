<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoredEndpoint extends Model
{
    protected $fillable = [
        'name',
        'url',
    ];

    public function checkResults(): HasMany
    {
        return $this->hasMany(CheckResult::class, 'endpoint_id');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckResult extends Model
{
    protected $fillable = [
        'endpoint_id',
        'status',
        'http_status_code',
        'response_time_ms',
        'error_message',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'checked_at' => 'datetime',
        ];
    }

    public function endpoint(): BelongsTo
    {
        return $this->belongsTo(MonitoredEndpoint::class, 'endpoint_id');
    }
}
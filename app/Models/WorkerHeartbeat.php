<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkerHeartbeat extends Model
{
    protected $fillable = ['worker_name', 'status', 'last_seen_at', 'metadata'];

    protected function casts(): array
    {
        return ['last_seen_at' => 'datetime', 'metadata' => 'array'];
    }
}

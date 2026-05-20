<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = [
        'title',
        'description',
        'is_completed',
        'start_date',
        'end_date',
        'assigned_to',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiGenerationLog extends Model
{
    protected $fillable = [
        'form_id', 'model', 'prompt_tokens', 'completion_tokens',
        'latency_ms', 'status', 'prompt', 'error',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}

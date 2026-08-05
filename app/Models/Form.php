<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Form extends Model
{
    protected $fillable = [
        'user_id', 'title', 'slug', 'description', 'schema', 'settings', 'status',
    ];

    protected $casts = [
        'schema'   => 'array',
        'settings' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (Form $form) {
            if (empty($form->slug)) {
                $form->slug = Str::slug($form->title) . '-' . Str::random(6);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FormVersion::class)->orderByDesc('version_number');
    }

    public function aiLogs(): HasMany
    {
        return $this->hasMany(AiGenerationLog::class);
    }

    public function getPublicUrlAttribute(): string
    {
        return route('forms.fill', $this->slug);
    }

    public function getFieldsAttribute(): array
    {
        return $this->schema['fields'] ?? [];
    }

    public function snapshotVersion(): FormVersion
    {
        $number = $this->versions()->max('version_number') + 1;

        return $this->versions()->create([
            'schema'         => $this->schema,
            'version_number' => $number,
            'label'          => "Version {$number}",
        ]);
    }
}

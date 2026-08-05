<?php

namespace App\Jobs;

use App\Models\AiGenerationLog;
use App\Models\Form;
use App\Services\AIService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class GenerateFormWithAI implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly Form   $form,
        public readonly string $prompt,
        public readonly string $mode = 'create'
    ) {}

    public function handle(AIService $ai): void
    {
        Cache::put("ai_job_status_{$this->form->id}", 'processing', 600);

        try {
            $existingSchema = $this->mode === 'edit'
                ? json_encode($this->form->schema)
                : null;

            $result = $ai->generateForm($this->prompt, $existingSchema);

            $this->form->update(['schema' => $result['schema']]);
            $this->form->snapshotVersion();

            Cache::forget("form_schema_{$this->form->slug}");

            AiGenerationLog::create([
                'form_id'           => $this->form->id,
                'model'             => $result['model'],
                'prompt_tokens'     => $result['usage']['prompt_tokens'] ?? 0,
                'completion_tokens' => $result['usage']['completion_tokens'] ?? 0,
                'latency_ms'        => $result['latency'],
                'status'            => 'success',
                'prompt'            => $this->prompt,
            ]);

            Cache::put("ai_job_status_{$this->form->id}", 'done', 600);
        } catch (\Throwable $e) {
            AiGenerationLog::create([
                'form_id' => $this->form->id,
                'model'   => 'unknown',
                'status'  => 'failed',
                'prompt'  => $this->prompt,
                'error'   => $e->getMessage(),
                'prompt_tokens'     => 0,
                'completion_tokens' => 0,
                'latency_ms'        => 0,
            ]);

            Cache::put("ai_job_status_{$this->form->id}", 'failed:' . $e->getMessage(), 600);

            throw $e;
        }
    }
}

<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class AIService
{
    private string $systemPrompt = <<<'PROMPT'
You are a form schema generator. Output ONLY valid JSON with this exact structure, no markdown, no explanation:
{
  "fields": [
    {
      "id": "generate-a-uuid-v4-here",
      "type": "one of: text|textarea|number|email|phone|date|dropdown|radio|checkbox|file|heading|rating|hidden",
      "label": "Human readable label",
      "key": "snake_case_unique_key",
      "placeholder": "helpful placeholder text",
      "help_text": "",
      "default": "",
      "required": false,
      "order": 0,
      "section": null,
      "options": [],
      "validation": {
        "min_length": null,
        "max_length": null,
        "min": null,
        "max": null,
        "regex": null,
        "file_types": [],
        "max_file_size_mb": null
      },
      "conditions": []
    }
  ]
}

Rules:
1. Use "dropdown" for 5+ options, "radio" for 2-4 options
2. Email fields must have type "email"
3. Phone fields must have type "phone"
4. File upload fields must have type "file" with file_types specified
5. Add "options" array only for dropdown, radio, checkbox types
6. Each field must have a unique "key" in snake_case
7. Generate proper UUID v4 for each "id"
8. "heading" type fields are section separators with no input
9. Output ONLY the JSON object, nothing else
PROMPT;

    public function generateForm(string $prompt, ?string $existingSchema = null): array
    {
        $userMessage = $existingSchema
            ? "Modify this existing form schema based on the instruction.\n\nCurrent schema:\n{$existingSchema}\n\nInstruction: {$prompt}\n\nReturn the complete updated schema with ALL fields (existing + new/modified)."
            : "Create a complete form for: {$prompt}";

        return $this->callAPI($userMessage);
    }

    private function callAPI(string $userMessage): array
    {
        $start = microtime(true);

        $provider = config('services.ai.provider', 'openai');

        if ($provider === 'claude') {
            return $this->callClaude($userMessage, $start);
        }

        return $this->callOpenAI($userMessage, $start);
    }

    private function callOpenAI(string $userMessage, float $start): array
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('services.openai.key'),
            'Content-Type'  => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/chat/completions', [
            'model'           => 'gpt-4o-mini',
            'messages'        => [
                ['role' => 'system', 'content' => $this->systemPrompt],
                ['role' => 'user',   'content' => $userMessage],
            ],
            'response_format' => ['type' => 'json_object'],
            'temperature'     => 0.3,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('OpenAI API error: ' . $response->body());
        }

        $data   = $response->json();
        $schema = json_decode($data['choices'][0]['message']['content'], true);

        return [
            'schema'  => $this->validateAndRepair($schema),
            'model'   => $data['model'],
            'usage'   => $data['usage'],
            'latency' => (int) ((microtime(true) - $start) * 1000),
        ];
    }

    private function callClaude(string $userMessage, float $start): array
    {
        $response = Http::withHeaders([
            'x-api-key'         => config('services.claude.key'),
            'anthropic-version' => '2023-06-01',
            'Content-Type'      => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model'      => 'claude-haiku-4-5-20251001',
            'max_tokens' => 4096,
            'system'     => $this->systemPrompt,
            'messages'   => [
                ['role' => 'user', 'content' => $userMessage],
            ],
        ]);

        if ($response->failed()) {
            throw new \RuntimeException('Claude API error: ' . $response->body());
        }

        $data   = $response->json();
        $schema = json_decode($data['content'][0]['text'], true);

        return [
            'schema'  => $this->validateAndRepair($schema),
            'model'   => $data['model'],
            'usage'   => ['prompt_tokens' => $data['usage']['input_tokens'], 'completion_tokens' => $data['usage']['output_tokens']],
            'latency' => (int) ((microtime(true) - $start) * 1000),
        ];
    }

    private function validateAndRepair(array $schema): array
    {
        $allowedTypes = ['text', 'textarea', 'number', 'email', 'phone', 'date',
                         'dropdown', 'radio', 'checkbox', 'file', 'heading', 'rating', 'hidden'];

        if (!isset($schema['fields']) || !is_array($schema['fields'])) {
            throw new \RuntimeException('AI returned invalid schema: missing fields array');
        }

        foreach ($schema['fields'] as $i => &$field) {
            if (!in_array($field['type'] ?? '', $allowedTypes)) {
                $field['type'] = 'text';
            }
            if (empty($field['id'])) {
                $field['id'] = (string) Str::uuid();
            }
            if (empty($field['key'])) {
                $field['key'] = Str::snake($field['label'] ?? 'field_' . $i);
            }
            if (!isset($field['required'])) {
                $field['required'] = false;
            }
            if (!isset($field['order'])) {
                $field['order'] = $i;
            }
            if (!isset($field['options'])) {
                $field['options'] = [];
            }
            if (!isset($field['validation'])) {
                $field['validation'] = [];
            }
            if (!isset($field['conditions'])) {
                $field['conditions'] = [];
            }
        }

        return $schema;
    }
}

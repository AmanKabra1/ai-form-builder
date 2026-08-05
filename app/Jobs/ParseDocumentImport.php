<?php

namespace App\Jobs;

use App\Models\ImportJob;
use App\Services\DocumentParser;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ParseDocumentImport implements ShouldQueue
{
    use Queueable;

    public int $tries   = 2;
    public int $timeout = 120;

    public function __construct(public readonly ImportJob $importJob) {}

    public function handle(DocumentParser $parser): void
    {
        $this->importJob->update(['status' => 'processing']);

        try {
            $path      = storage_path('app/private/' . $this->importJob->filename);
            $extension = strtolower(pathinfo($this->importJob->original_name, PATHINFO_EXTENSION));

            $schema = match ($extension) {
                'docx'  => $parser->parseDocx($path),
                'xlsx'  => $parser->parseXlsx($path),
                default => throw new \RuntimeException("Unsupported file type: {$extension}"),
            };

            $this->importJob->update([
                'status'        => 'done',
                'parsed_schema' => $schema,
            ]);
        } catch (\Throwable $e) {
            $this->importJob->update([
                'status' => 'failed',
                'error'  => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}

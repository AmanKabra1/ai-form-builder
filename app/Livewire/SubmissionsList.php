<?php

namespace App\Livewire;

use App\Models\Form;
use App\Models\FormSubmission;
use Illuminate\Support\Facades\Response;
use League\Csv\Writer;
use Livewire\Component;
use Livewire\WithPagination;

class SubmissionsList extends Component
{
    use WithPagination;

    public Form   $form;
    public string $search  = '';
    public string $perPage = '15';

    public function mount(Form $form): void
    {
        abort_unless($form->user_id === auth()->id(), 403);
        $this->form = $form;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function exportCsv()
    {
        $submissions = FormSubmission::where('form_id', $this->form->id)
            ->orderByDesc('created_at')
            ->get();

        $fields  = $this->form->fields;
        $headers = array_map(fn($f) => $f['label'], array_filter($fields, fn($f) => $f['type'] !== 'heading'));

        $csv = Writer::createFromString();
        $csv->insertOne(array_merge(['ID', 'Submitted At'], $headers));

        foreach ($submissions as $submission) {
            $row = [$submission->id, $submission->created_at->toDateTimeString()];
            foreach ($fields as $field) {
                if ($field['type'] === 'heading') {
                    continue;
                }
                $row[] = $submission->data[$field['key']] ?? '';
            }
            $csv->insertOne($row);
        }

        return Response::streamDownload(function () use ($csv) {
            echo $csv->toString();
        }, "submissions-{$this->form->slug}.csv", ['Content-Type' => 'text/csv']);
    }

    public function render()
    {
        $query = FormSubmission::where('form_id', $this->form->id)
            ->orderByDesc('created_at');

        if ($this->search) {
            $query->where('data', 'like', '%' . $this->search . '%');
        }

        $submissions = $query->paginate((int) $this->perPage);

        return view('livewire.submissions-list', compact('submissions'))
            ->layout('layouts.app');
    }
}

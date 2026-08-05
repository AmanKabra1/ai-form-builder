<?php

namespace App\Services;

use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory as WordFactory;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\ListItem;
use PhpOffice\PhpWord\Element\Title;
use PhpOffice\PhpSpreadsheet\IOFactory as SpreadsheetFactory;

class DocumentParser
{
    public function parseDocx(string $path): array
    {
        $phpWord = WordFactory::load($path);
        $fields  = [];
        $order   = 0;

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $text = $this->extractText($element);
                if (empty(trim($text))) {
                    continue;
                }

                if ($element instanceof Title) {
                    $fields[] = $this->makeField('heading', $text, $order++);
                    continue;
                }

                if ($element instanceof ListItem) {
                    if (!empty($fields)) {
                        $last = &$fields[count($fields) - 1];
                        if (in_array($last['type'], ['dropdown', 'radio', 'checkbox'])) {
                            $last['options'][] = ['label' => $text, 'value' => Str::slug($text)];
                            continue;
                        }
                    }
                }

                if ($element instanceof TextRun || $element instanceof Text) {
                    $fields[] = $this->inferField($text, $order++);
                }
            }
        }

        return ['fields' => $fields];
    }

    public function parseXlsx(string $path): array
    {
        $spreadsheet = SpreadsheetFactory::load($path);
        $sheet       = $spreadsheet->getActiveSheet();
        $headers     = [];
        $fields      = [];

        foreach ($sheet->getRowIterator(1, 1) as $row) {
            foreach ($row->getCellIterator() as $cell) {
                $val = $cell->getValue();
                if (!empty($val)) {
                    $headers[] = $val;
                }
            }
        }

        foreach ($headers as $i => $header) {
            $fields[] = $this->inferField((string) $header, $i);
        }

        return ['fields' => $fields];
    }

    private function inferField(string $text, int $order): array
    {
        $lower = strtolower($text);

        $type = match (true) {
            str_contains($lower, 'email')                                     => 'email',
            str_contains($lower, 'phone') || str_contains($lower, 'mobile')  => 'phone',
            str_contains($lower, 'date') || str_contains($lower, 'dob')      => 'date',
            str_contains($lower, 'upload') || str_contains($lower, 'resume') || str_contains($lower, 'file') => 'file',
            str_contains($lower, 'description') || str_contains($lower, 'message') || str_contains($lower, 'address') => 'textarea',
            str_contains($lower, 'rating') || str_contains($lower, 'score')  => 'rating',
            str_contains($lower, 'gender') || str_contains($lower, 'sex')    => 'radio',
            str_contains($lower, 'agree') || str_contains($lower, 'confirm') => 'checkbox',
            default                                                            => 'text',
        };

        $field = $this->makeField($type, $text, $order);

        if ($type === 'radio' && str_contains(strtolower($text), 'gender')) {
            $field['options'] = [
                ['label' => 'Male',   'value' => 'male'],
                ['label' => 'Female', 'value' => 'female'],
                ['label' => 'Other',  'value' => 'other'],
            ];
        }

        if ($type === 'file') {
            $field['validation']['file_types']     = ['pdf', 'doc', 'docx'];
            $field['validation']['max_file_size_mb'] = 5;
        }

        return $field;
    }

    private function makeField(string $type, string $label, int $order): array
    {
        return [
            'id'          => (string) Str::uuid(),
            'type'        => $type,
            'label'       => trim($label),
            'key'         => Str::snake(preg_replace('/[^a-zA-Z0-9\s]/', '', $label)),
            'placeholder' => '',
            'help_text'   => '',
            'default'     => '',
            'required'    => false,
            'order'       => $order,
            'section'     => null,
            'options'     => [],
            'validation'  => [
                'min_length'      => null,
                'max_length'      => null,
                'min'             => null,
                'max'             => null,
                'regex'           => null,
                'file_types'      => [],
                'max_file_size_mb' => null,
            ],
            'conditions'  => [],
        ];
    }

    private function extractText(mixed $element): string
    {
        if ($element instanceof Title) {
            $value = $element->getText();
            return is_string($value) ? $value : '';
        }
        if ($element instanceof TextRun) {
            $text = '';
            foreach ($element->getElements() as $child) {
                if (method_exists($child, 'getText')) {
                    $text .= $child->getText();
                }
            }
            return $text;
        }
        if ($element instanceof Text || $element instanceof ListItem) {
            return method_exists($element, 'getText') ? (string) $element->getText() : '';
        }
        return '';
    }
}

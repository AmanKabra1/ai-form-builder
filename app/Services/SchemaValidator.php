<?php

namespace App\Services;

class SchemaValidator
{
    private const ALLOWED_TYPES = [
        'text', 'textarea', 'number', 'email', 'phone', 'date',
        'dropdown', 'radio', 'checkbox', 'file', 'heading', 'rating', 'hidden',
    ];

    public function validate(array $schema): array
    {
        $errors = [];

        if (!isset($schema['fields']) || !is_array($schema['fields'])) {
            return ['Schema must have a fields array'];
        }

        $keys = [];
        foreach ($schema['fields'] as $i => $field) {
            $n = $i + 1;

            if (empty($field['id'])) {
                $errors[] = "Field #{$n}: missing id";
            }
            if (empty($field['label'])) {
                $errors[] = "Field #{$n}: missing label";
            }
            if (empty($field['key'])) {
                $errors[] = "Field #{$n}: missing key";
            } elseif (in_array($field['key'], $keys)) {
                $errors[] = "Field #{$n}: duplicate key '{$field['key']}'";
            } else {
                $keys[] = $field['key'];
            }
            if (!in_array($field['type'] ?? '', self::ALLOWED_TYPES)) {
                $errors[] = "Field #{$n}: invalid type '{$field['type']}'";
            }
            if (in_array($field['type'] ?? '', ['dropdown', 'radio', 'checkbox'])) {
                if (empty($field['options'])) {
                    $errors[] = "Field #{$n}: type '{$field['type']}' requires options";
                }
            }
        }

        return $errors;
    }

    public function isValid(array $schema): bool
    {
        return empty($this->validate($schema));
    }
}

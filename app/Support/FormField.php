<?php

namespace App\Support;

use Illuminate\Support\Str;

class FormField
{
    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            'text' => 'نص قصير',
            'textarea' => 'نص طويل',
            'email' => 'بريد إلكتروني',
            'tel' => 'رقم جوال',
            'number' => 'رقم',
            'date' => 'تاريخ',
            'select' => 'قائمة منسدلة',
            'radio' => 'اختيار واحد',
            'checkbox' => 'خانة اختيار',
            'file' => 'ملف',
        ];
    }

    public static function typeLabel(string $type): string
    {
        return self::typeOptions()[$type] ?? $type;
    }

    public static function typeIcon(string $type): string
    {
        return match ($type) {
            'textarea' => 'align-left',
            'email' => 'Mail',
            'tel' => 'Phone',
            'number' => 'Hash',
            'date' => 'Calendar',
            'select' => 'Selector',
            'radio' => 'circle-dot',
            'checkbox' => 'square-check',
            'file' => 'Paperclip',
            default => 'Forms',
        };
    }

    public static function hasOptions(string $type): bool
    {
        return in_array($type, ['select', 'radio'], true);
    }

    /**
     * @return array<string, mixed>
     */
    public static function make(string $type = 'text'): array
    {
        $id = 'fld_'.Str::lower(Str::random(8));

        return [
            'id' => $id,
            'type' => $type,
            'label' => '',
            'name' => $id,
            'placeholder' => '',
            'required' => false,
            'info' => '',
            'options' => self::hasOptions($type) ? ['', ''] : [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>|null  $fields
     * @return list<array<string, mixed>>
     */
    public static function normalize(?array $fields): array
    {
        if ($fields === null || $fields === []) {
            return [];
        }

        return collect($fields)
            ->filter(fn (mixed $field): bool => is_array($field) && filled($field['id'] ?? null))
            ->map(function (array $field): array {
                $type = (string) ($field['type'] ?? 'text');

                return [
                    'id' => (string) $field['id'],
                    'type' => array_key_exists($type, self::typeOptions()) ? $type : 'text',
                    'label' => (string) ($field['label'] ?? ''),
                    'name' => (string) ($field['name'] ?? $field['id']),
                    'placeholder' => (string) ($field['placeholder'] ?? ''),
                    'required' => (bool) ($field['required'] ?? false),
                    'info' => (string) ($field['info'] ?? ''),
                    'options' => self::normalizeOptions($field['options'] ?? []),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return list<string>
     */
    public static function normalizeOptions(mixed $options): array
    {
        if (! is_array($options)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn (mixed $option): string => trim((string) $option),
            $options,
        )));
    }

    /**
     * @param  list<array<string, mixed>>  $fields
     * @return list<array<string, mixed>>
     */
    public static function forStorage(array $fields): array
    {
        return collect($fields)
            ->values()
            ->map(fn (array $field, int $index): array => [
                'id' => (string) $field['id'],
                'type' => (string) $field['type'],
                'label' => (string) $field['label'],
                'name' => (string) $field['name'],
                'placeholder' => (string) $field['placeholder'],
                'required' => (bool) $field['required'],
                'info' => (string) $field['info'],
                'options' => self::normalizeOptions($field['options'] ?? []),
                'sort_order' => $index,
            ])
            ->all();
    }
}

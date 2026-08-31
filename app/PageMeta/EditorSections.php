<?php

namespace App\PageMeta;

use Illuminate\Support\Str;

final class EditorSections
{
    /**
     * @return list<array{key: string, label: string, fields: list<Field>}>
     */
    public static function for(PageDefinition $definition): array
    {
        $sections = [];

        foreach ($definition->fields() as $field) {
            $key = self::key($field);
            $sections[$key] ??= [
                'key' => $key,
                'label' => self::label($key),
                'fields' => [],
            ];
            $sections[$key]['fields'][] = $field;
        }

        return array_values($sections);
    }

    private static function key(Field $field): string
    {
        $source = $field->group ?? $field->key;

        return explode('.', $source)[0];
    }

    private static function label(string $key): string
    {
        return match ($key) {
            'slide' => 'Hero slider',
            'eco' => 'Ecosystem',
            'cct' => 'Colour temperature',
            'caps' => 'Capabilities',
            'intl' => 'International',
            'abn' => 'ABN',
            'acn' => 'ACN',
            'cta' => 'Call to action',
            'card' => 'Cards',
            default => Str::headline(str_replace('-', ' ', $key)),
        };
    }
}

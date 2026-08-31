<?php

namespace App\PageMeta;

final class Field
{
    /**
     * @param  array<string, string>  $options
     */
    public function __construct(
        public string $key,
        public string $label,
        public FieldType $type = FieldType::Text,
        public bool $repeatable = false,
        public ?string $group = null,
        public array $options = [],
    ) {}

    public static function text(string $key, string $label, bool $repeatable = false, ?string $group = null): self
    {
        return new self($key, $label, FieldType::Text, $repeatable, $group);
    }

    public static function textarea(string $key, string $label, bool $repeatable = false, ?string $group = null): self
    {
        return new self($key, $label, FieldType::Textarea, $repeatable, $group);
    }

    public static function html(string $key, string $label, bool $repeatable = false, ?string $group = null): self
    {
        return new self($key, $label, FieldType::Html, $repeatable, $group);
    }

    public static function url(string $key, string $label, bool $repeatable = false, ?string $group = null): self
    {
        return new self($key, $label, FieldType::Url, $repeatable, $group);
    }

    public static function image(string $key, string $label, bool $repeatable = false, ?string $group = null): self
    {
        return new self($key, $label, FieldType::Image, $repeatable, $group);
    }

    public static function video(string $key, string $label, bool $repeatable = false, ?string $group = null): self
    {
        return new self($key, $label, FieldType::Video, $repeatable, $group);
    }

    /**
     * @param  array<string, string>  $options
     */
    public static function select(string $key, string $label, array $options, bool $repeatable = false, ?string $group = null): self
    {
        return new self($key, $label, FieldType::Select, $repeatable, $group, $options);
    }
}

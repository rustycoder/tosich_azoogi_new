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
        public string $hint = '',
        public bool $typographic = false,
        public ?int $minCount = null,
        public ?int $maxCount = null,
        public ?string $counterType = null,
    ) {}

    public static function text(string $key, string $label, bool $repeatable = false, ?string $group = null, bool $typographic = false): self
    {
        return new self($key, $label, FieldType::Text, $repeatable, $group, typographic: $typographic);
    }

    public static function textarea(string $key, string $label, bool $repeatable = false, ?string $group = null, bool $typographic = false): self
    {
        return new self($key, $label, FieldType::Textarea, $repeatable, $group, typographic: $typographic);
    }

    public static function html(string $key, string $label, bool $repeatable = false, ?string $group = null, bool $typographic = false): self
    {
        return new self($key, $label, FieldType::Html, $repeatable, $group, typographic: $typographic);
    }

    public static function url(string $key, string $label, bool $repeatable = false, ?string $group = null): self
    {
        return new self($key, $label, FieldType::Url, $repeatable, $group);
    }

    public static function image(string $key, string $label, bool $repeatable = false, ?string $group = null, string $hint = ''): self
    {
        return new self($key, $label, FieldType::Image, $repeatable, $group, hint: $hint);
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

    public function recommendedChars(int $min, int $max): self
    {
        $this->minCount = $min;
        $this->maxCount = $max;
        $this->counterType = 'chars';

        return $this;
    }

    public function recommendedWords(int $min, int $max): self
    {
        $this->minCount = $min;
        $this->maxCount = $max;
        $this->counterType = 'words';

        return $this;
    }

    public function hasRecommendation(): bool
    {
        return $this->minCount !== null && $this->maxCount !== null && $this->counterType !== null;
    }

    public function recommendationLabel(): string
    {
        if (! $this->hasRecommendation()) {
            return '';
        }

        $unit = $this->counterType === 'words' ? 'words' : 'chars';

        return "({$this->minCount}–{$this->maxCount} {$unit} recommended)";
    }

    public function isTypographic(): bool
    {
        return $this->typographic && in_array($this->type, [FieldType::Text, FieldType::Textarea, FieldType::Html], true);
    }
}

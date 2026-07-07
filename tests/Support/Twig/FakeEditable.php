<?php

declare(strict_types=1);

namespace CombatUI\CombatUIOpenDxpBundle\Tests\Support\Twig;

/**
 * Stand-in for OpenDXP document editables in template tests. Mimics the API surface the brick
 * templates use (getData/isEmpty/isChecked plus the link and image accessors) and renders a
 * recognisable <x-editable> marker in editmode so tests can assert the editing UI survives
 * unescaped.
 */
final class FakeEditable implements \Stringable
{
    public function __construct(
        private readonly string $type,
        private readonly string $name,
        private readonly mixed $data,
        private readonly bool $editmode,
    ) {
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function isEmpty(): bool
    {
        return $this->data === null || $this->data === '' || $this->data === [];
    }

    public function isChecked(): bool
    {
        return (bool) $this->data;
    }

    public function getSrc(): string
    {
        return is_string($this->data) ? $this->data : '';
    }

    public function getHref(): string
    {
        return is_array($this->data) ? ($this->data['href'] ?? '') : '';
    }

    public function getText(): string
    {
        return is_array($this->data) ? ($this->data['text'] ?? '') : '';
    }

    public function getTarget(): string
    {
        return is_array($this->data) ? ($this->data['target'] ?? '') : '';
    }

    public function __toString(): string
    {
        if ($this->editmode) {
            return sprintf('<x-editable data-type="%s" data-name="%s"></x-editable>', $this->type, $this->name);
        }

        return match ($this->type) {
            'image' => $this->isEmpty() ? '' : sprintf('<img src="%s" alt="">', $this->getSrc()),
            'wysiwyg' => is_string($this->data) ? $this->data : '',
            'link' => $this->isEmpty() ? '' : sprintf('<a href="%s">%s</a>', $this->getHref(), $this->getText()),
            default => is_scalar($this->data) ? htmlspecialchars((string) $this->data) : '',
        };
    }
}

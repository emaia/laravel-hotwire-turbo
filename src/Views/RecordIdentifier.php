<?php

namespace Emaia\LaravelHotwireTurbo\Views;

use Closure;
use Emaia\LaravelHotwireTurbo\Models\Name;
use InvalidArgumentException;

class RecordIdentifier
{
    const NEW_PREFIX = 'create';

    const DELIMITER = '_';

    private readonly object $record;

    /** @var Closure(): mixed */
    private readonly Closure $keyResolver;

    public function __construct(object $record)
    {
        $this->record = $record;
        $this->keyResolver = match (true) {
            method_exists($record, 'getKey') => fn () => $record->getKey(),
            property_exists($record, 'id') => fn () => $record->id,
            default => throw new InvalidArgumentException(
                sprintf('%s must have a getKey() method or a public $id property.', $record::class)
            ),
        };
    }

    public function domId(?string $prefix = null): string
    {
        if ($recordId = ($this->keyResolver)()) {
            return sprintf('%s%s%s', $this->domClass($prefix), self::DELIMITER, $recordId);
        }

        return $this->domClass($prefix ?: static::NEW_PREFIX);
    }

    public function domClass(?string $prefix = null): string
    {
        $singular = Name::forModel($this->record)->singular;
        $delimiter = static::DELIMITER;

        return trim("{$prefix}{$delimiter}{$singular}", $delimiter);
    }
}

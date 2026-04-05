<?php

namespace Emaia\LaravelHotwireTurbo\Views;

use Emaia\LaravelHotwireTurbo\Models\Name;
use InvalidArgumentException;

class RecordIdentifier
{
    const NEW_PREFIX = 'create';

    const DELIMITER = '_';

    private readonly object $record;

    public function __construct(object $record)
    {
        if (! method_exists($record, 'getKey')) {
            throw new InvalidArgumentException(
                sprintf('Object of class %s does not have a getKey() method.', $record::class)
            );
        }

        $this->record = $record;
    }

    public function domId(?string $prefix = null): string
    {
        if ($recordId = $this->record->getKey()) {
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

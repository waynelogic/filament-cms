<?php

namespace Waynelogic\FilamentCms\Database\Traits;

trait HasExternalId
{
    private function getExternalIdField() : string
    {
        return defined('static::EXTERNAL_ID_FIELD') ? static::EXTERNAL_ID_FIELD : 'external_id';
    }
    protected function initializeHasExternalId(): void
    {
        $this->fillable[] = $this->getExternalIdField();
    }

    public static function takeByExternalId(mixed $externalId): ? static
    {
        $instance = new static;

        return static::query()
            ->where($instance->getExternalIdField(), $externalId)
            ->first();
    }
}

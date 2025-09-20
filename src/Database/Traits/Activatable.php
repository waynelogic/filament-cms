<?php

namespace Waynelogic\FilamentCms\Database\Traits;

trait Activatable
{
    private function getActiveField(): string
    {
        return defined('static::ACTIVE_FIELD') ? static::ACTIVE_FIELD : 'is_active';
    }
    protected function initializeActivatable(): void
    {
        $this->fillable[] = $this->getActiveField();
        $this->casts[$this->getActiveField()] = 'boolean';
    }
    public function scopeActive($query)
    {
        return $query->where($this->getActiveField(), true);
    }
    public function scopeInactive($query)
    {
        return $query->where($this->getActiveField(), false);
    }
}

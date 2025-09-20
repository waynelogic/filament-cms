<?php namespace Waynelogic\FilamentCms\Database\Traits;

use Illuminate\Database\Eloquent\Model;

trait Defaultable
{
    public static function bootDefaultable(): void
    {
        static::saving(function (Model $model) {
            if ($model->is_default) {
                static::query()->where('id', '!=', $model->id)->update(['is_default' => false]);
            }
        });
    }

    protected function initializeDefaultable(): void
    {
        $this->fillable[] = 'is_default';
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public static function getDefault()
    {
        return static::query()->default()->first();
    }
}

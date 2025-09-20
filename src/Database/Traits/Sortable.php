<?php namespace Waynelogic\FilamentCms\Database\Traits;

use Illuminate\Database\Eloquent\Builder;
use Exception;
use Waynelogic\FilamentCms\Database\Scopes\SortableScope;

trait Sortable
{
    /**
     * Boot the trait and apply the global scope.
     */
    public static function bootSortable(): void
    {
        static::addGlobalScope(new SortableScope);

        static::creating(function ($model) {
            $sortOrderColumn = $model->getSortOrderColumn();
            $parentColumn = $model->getParentColumn();

            if (is_null($model->$sortOrderColumn)) {
                $maxSortOrder = static::query()
                    ->when($model->$parentColumn, function (Builder $query) use ($parentColumn, $model) {
                        return $query->where($parentColumn, $model->$parentColumn);
                    }, function (Builder $query) {
                        return $query->whereNull($query->getModel()->getParentColumn());
                    })
                    ->max($sortOrderColumn);

                $model->$sortOrderColumn = ($maxSortOrder ?? 0) + 1;
            }
        });
    }

    /**
     * Set the sort order of items within a parent group.
     *
     * @param array $itemIds
     * @param mixed|null $parentId
     * @return void
     * @throws Exception
     */
    public function setSortableOrder(array $itemIds, $parentId = null): void
    {
        $sortOrderColumn = $this->getSortOrderColumn();
        $parentColumn = $this->getParentColumn();

        $query = $this->newQuery();
        if ($parentId !== null) {
            $query->where($parentColumn, $parentId);
        } else {
            $query->whereNull($parentColumn);
        }

        $items = $query->whereIn($this->getKeyName(), $itemIds)
            ->pluck($this->getKeyName(), $sortOrderColumn)
            ->flip();

        $updates = [];
        foreach ($itemIds as $index => $id) {
            if ($items->has($id)) {
                $updates[] = [
                    'id' => $id,
                    $sortOrderColumn => $index + 1
                ];
            }
        }

        if (count($updates) !== count($itemIds)) {
            throw new Exception('Invalid setSortableOrder call - some IDs not found.');
        }

        foreach ($updates as $update) {
            $this->newQuery()
                ->where($this->getKeyName(), $update['id'])
                ->update([$sortOrderColumn => $update[$sortOrderColumn]]);
        }
    }

    /**
     * Get the "sort order" column name.
     *
     * @return string
     */
    public function getSortOrderColumn(): string
    {
        return defined('static::SORT_ORDER') ? static::SORT_ORDER : 'sort_order';
    }

    /**
     * Get the "parent" column name.
     *
     * @return string
     */
    public function getParentColumn(): string
    {
        return defined('static::PARENT_COLUMN') ? static::PARENT_COLUMN : 'parent_id';
    }

    public function getQualifiedSortOrderColumn(): string
    {
        return $this->qualifyColumn($this->getSortOrderColumn());
    }
}

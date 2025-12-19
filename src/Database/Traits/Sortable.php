<?php namespace Waynelogic\FilamentCms\Database\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Use this trait to make a model sortable.
 * The model should have a default sort order column of "sort_order".
 * Add the column to the table as follows:
 *
 * $table->integer('sort_order')->default(0);
 * or
 * $table->sortable() // will create a sortable column // can be used with custom column name
 *
 * To change the sort order column name, add the following to your model:
 * protected string $sortOrderColumn = 'sort_order';
 *
 * If you want to sort by parent column, add the following to your model:
 * protected string $sortableParentColumn = 'parent_id';
 */
trait Sortable
{
    /**
     * Boot the Sortable trait.
     */
    public static function bootSortable(): void
    {
        static::addGlobalScope('sortable', function (Builder $builder) {
            $model = $builder->getModel();
            $table = $model->getTable();
            $sortColumn = $model->getSortOrderColumn();

            if (property_exists($model, 'sortableParentColumn')) {
                $builder->orderBy("{$table}.{$model->sortableParentColumn}");
            }
            $builder->orderBy("{$table}.{$sortColumn}");
        });

        static::creating(function (Model $model) {
            $model->{$model->getSortOrderColumn()} = $model->getNextSortOrder();
        });
    }

    /**
     * Get next sort_order value (within parent group if applicable).
     */
    protected function getNextSortOrder(): int
    {
        $query = DB::table($this->getTable());

        if (property_exists($this, 'sortableParentColumn')) {
            $parentColumn = $this->sortableParentColumn;
            $parentValue = $this->{$parentColumn} ?? null;
            $query->where($parentColumn, $parentValue);
        }

        $max = $query->max($this->getSortOrderColumn());
        return $max === null ? 0 : $max + 1;
    }

    /**
     * Column name for sort order.
     */
    public function getSortOrderColumn(): string
    {
        return property_exists($this, 'sortOrderColumn') ? $this->sortOrderColumn : 'sort_order';
    }

    // ────────────────────────────────
    // 🔄 MOVE METHODS
    // ────────────────────────────────

    /**
     * Переместить элемент на указанную позицию в группе (0-based index).
     */
    public function moveTo(int $index): self
    {
        $column = $this->getSortOrderColumn();

        // Определяем группу (если есть родитель)
        $query = DB::table($this->getTable());
        $binds = [];
        if (property_exists($this, 'sortableParentColumn')) {
            $parentCol = $this->sortableParentColumn;
            $parentVal = $this->{$parentCol} ?? null;
            $query->where($parentCol, $parentVal);
            $binds[$parentCol] = $parentVal;
        }

        // Получаем все sort_order в группе, отсортированные
        $orders = $query->orderBy($column)->pluck($column)->all();

        // Убираем текущий элемент из списка (чтобы не дублировался)
        $orders = array_filter($orders, fn($v) => $v != $this->{$column});

        // Убеждаемся, что индекс в границах
        $index = max(0, min($index, count($orders)));

        // Вставляем текущий элемент в нужную позицию (временное значение — используем отрицательное или большое)
        $tempOrder = -1;
        $this->update([$column => $tempOrder]);

        // Собираем новый порядок
        $newOrders = array_values($orders);
        array_splice($newOrders, $index, 0, [$tempOrder]);

        // Присваиваем новые значения: 0, 1, 2, ...
        foreach ($newOrders as $newIndex => $orderVal) {
            if ($orderVal === $tempOrder) {
                $this->update([$column => $newIndex]);
            } else {
                DB::table($this->getTable())
                    ->where($column, $orderVal)
                    ->when(property_exists($this, 'sortableParentColumn'), function ($q) use ($binds) {
                        $q->where($this->sortableParentColumn, $binds[$this->sortableParentColumn] ?? null);
                    })
                    ->update([$column => $newIndex]);
            }
        }

        // Обновляем модель локально
        $this->setAttribute($column, $index);

        return $this;
    }

    /**
     * Переместить элемент ПЕРЕД другим.
     */
    public function moveBefore(self $target): self
    {
        return $this->moveToRelative($target, 'before');
    }

    /**
     * Переместить элемент ПОСЛЕ другого.
     */
    public function moveAfter(self $target): self
    {
        return $this->moveToRelative($target, 'after');
    }

    /**
     * Вспомогательный метод для moveBefore / moveAfter.
     */
    protected function moveToRelative(self $target, string $position): self
    {
        // Убеждаемся, что оба в одной группе (если есть parent)
        if (property_exists($this, 'sortableParentColumn')) {
            $parentCol = $this->sortableParentColumn;
            if ($this->{$parentCol} !== $target->{$parentCol}) {
                // Можно бросить исключение или молча игнорировать — выбери по вкусу
                // Здесь — мягко: просто обновляем parent и продолжаем
                $this->update([$parentCol => $target->{$parentCol}]);
                $this->{$parentCol} = $target->{$parentCol};
            }
        }

        // Перезагружаем список с учётом новой группы
        $query = DB::table($this->getTable());
        if (property_exists($this, 'sortableParentColumn')) {
            $query->where($this->sortableParentColumn, $this->{$this->sortableParentColumn} ?? null);
        }
        $items = $query->orderBy($this->getSortOrderColumn())->pluck('id', $this->getSortOrderColumn())->all();

        // Удаляем себя из списка (если есть)
        $items = array_filter($items, fn($id) => $id != $this->getKey());

        // Инвертируем для поиска позиции
        $positions = array_flip($items);
        $targetPos = $positions[$target->getKey()] ?? null;

        if ($targetPos === null) {
            // Цель не найдена — добавляем в конец
            return $this->moveTo(count($items));
        }

        $newIndex = $position === 'before' ? $targetPos : $targetPos + 1;
        return $this->moveTo($newIndex);
    }
}

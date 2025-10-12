<?php namespace Waynelogic\FilamentCms\Database\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Use this trait to make a model sortable.
 * The model should have a default sort order column of "sort_order".
 * Add the column to the table as follows:
 *
 * $table->integer('sort_order')->default(0);
 * or
 * $table->sortable() // will create a sortable column // can be used with custom column name
 */
trait Sortable
{
    /**
     * Инициализация трейта.
     * Автоматически устанавливает позицию при создании новой модели.
     */
    protected static function bootSortableModel(): void
    {
        static::creating(function (Model $model) {
            if ($model->shouldSort()) {
                $model->{$model->getSortableField()} = $model->getNewHighestPosition() + 1;
            }
        });

        static::deleted(function (Model $model) {
            if ($model->shouldSort()) {
                $model->shiftLowerPositions($model->getPosition());
            }
        });
    }

    /**
     * Перемещает модель на указанную позицию.
     *
     * @param int $newPosition Новая позиция
     */
    public function moveTo(int $newPosition): void
    {
        $oldPosition = $this->getPosition();

        if ($newPosition === $oldPosition) {
            return;
        }

        // Ограничиваем новую позицию рамками существующих
        $newPosition = max(1, min($newPosition, $this->getNewHighestPosition()));

        $this->buildSortableQuery()->where($this->getSortableField(), '=', $this->getKey())->update([
            $this->getSortableField() => 0 // Временно "убираем" модель из списка
        ]);

        if ($newPosition > $oldPosition) {
            // Сдвигаем вверх элементы, которые находятся между старой и новой позицией
            $this->buildSortableQuery()
                ->whereBetween($this->getSortableField(), [$oldPosition + 1, $newPosition])
                ->decrement($this->getSortableField());
        } else {
            // Сдвигаем вниз элементы, которые находятся между новой и старой позицией
            $this->buildSortableQuery()
                ->whereBetween($this->getSortableField(), [$newPosition, $oldPosition - 1])
                ->increment($this->getSortableField());
        }

        // Устанавливаем новую позицию для текущей модели
        $this->buildSortableQuery()->where($this->getSortableField(), '=', 0)->update([
            $this->getSortableField() => $newPosition
        ]);

        $this->{$this->getSortableField()} = $newPosition;
    }

    /**
     * Сдвигает все элементы ниже указанной позиции вверх.
     *
     * @param int $fromPosition
     */
    protected function shiftLowerPositions(int $fromPosition): void
    {
        $this->buildSortableQuery()
            ->where($this->getSortableField(), '>', $fromPosition)
            ->decrement($this->getSortableField());
    }

    /**
     * Получает текущую максимальную позицию в группе.
     *
     * @return int
     */
    protected function getNewHighestPosition(): int
    {
        return (int) $this->buildSortableQuery()->max($this->getSortableField()) ?? 0;
    }

    /**
     * Scope для упорядочивания записей.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy($this->getSortableField());
    }

    /**
     * Создает базовый запрос для сортировки с учетом группы.
     *
     * @return Builder
     */
    public function buildSortableQuery(): Builder
    {
        $query = static::query();

        if (method_exists($this, 'getSortableGroupFields')) {
            foreach ($this->getSortableGroupFields() as $field) {
                $query->where($field, $this->{$field});
            }
        }

        return $query;
    }

    /**
     * Получает текущую позицию модели.
     *
     * @return int
     */
    public function getPosition(): int
    {
        return (int) $this->{$this->getSortableField()};
    }

    /**
     * Определяет, нужно ли применять сортировку к модели.
     * По умолчанию - всегда да. Можно переопределить в модели.
     *
     * @return bool
     */
    public function shouldSort(): bool
    {
        return true;
    }

    /**
     * Возвращает имя поля для сортировки.
     * По умолчанию 'position'. Можно переопределить в модели.
     *
     * @return string
     */
    public function getSortableField(): string
    {
        return 'sort_order';
    }

    public function getQualifiedSortOrderColumn()
    {
        return $this->qualifyColumn($this->getSortableField());
    }
}

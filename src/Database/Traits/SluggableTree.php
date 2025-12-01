<?php

namespace Waynelogic\FilamentCms\Database\Traits;

trait SluggableTree
{
    public static function bootSluggableTree(): void
    {
        static::saving(function ($model) {
            // Проверка наличия одного из требуемых трейтов
            $traits = class_uses_recursive($model::class);
            $hasTreeTrait = in_array(SimpleTree::class, $traits) || in_array(NestedTree::class, $traits);
            if (! $hasTreeTrait) {
                throw new \LogicException('Model must use either SimpleTree or NestedTree trait to use SluggableTree functionality.');
            }
        });

        static::saved(function ($model) {
            // Только если изменились slug или parent_id — пересчитываем поддерево
            if ($model->isDirty($model->getFullSluggableSlugColumnName()) || $model->isDirty('parent_id')) {
                $model->refresh(); // актуализируем данные после сохранения
                $model->fullSlugAttributes();
            }
        });
    }

    /**
     * fullSlugAttributes calculates full slugs for this model and all other related ones
     * @return void
     */
    public function fullSlugAttributes()
    {
        $this->setFullSluggedValue($this);
    }

    /**
     * setFullSluggedValue will set the fullslug value on a model
     */
    protected function setFullSluggedValue($model)
    {
        $fullslugAttr = $this->getFullSluggableFullSlugColumnName();
        $proposedSlug = $this->getFullSluggableAttributeValue($model);

        if ($model->{$fullslugAttr} != $proposedSlug) {
            $model
                ->newQuery()
                ->where($model->getKeyName(), $model->getKey())
                ->update([$fullslugAttr => $proposedSlug]);
        }

        if ($children = $model->children) {
            foreach ($children as $child) {
                $this->setFullSluggedValue($child);
            }
        }
    }

    /**
     * getFullSluggableAttributeValue
     */
    protected function getFullSluggableAttributeValue($model, $fullslug = '', $visited = []): string
    {
        $key = $model->getKey();
        if (in_array($key, $visited, true)) {
            // Обнаружен цикл — прерываем рекурсию
            throw new \LogicException("Circular reference detected in tree hierarchy for model ID: {$key}");
        }
        $visited[] = $key;

        $slugAttr = $this->getFullSluggableSlugColumnName();
        $fullslug = $model->{$slugAttr} . '/' . $fullslug;

        if ($parent = $model->parent()->withoutGlobalScopes()->first()) {
            $fullslug = $this->getFullSluggableAttributeValue($parent, $fullslug, $visited);
        }

        return rtrim($fullslug, '/');
    }

    /**
     * getFullSluggableFullSlugColumnName gets the name of the "fullslug" column.
     * @return string
     */
    public function getFullSluggableFullSlugColumnName(): string
    {
        return defined('static::FULLSLUG') ? static::FULLSLUG : 'fullslug';
    }

    /**
     * getFullSluggableSlugColumnName gets the name of the "slug" column.
     * @return string
     */
    public function getFullSluggableSlugColumnName(): string
    {
        return defined('static::SLUG') ? static::SLUG : 'slug';
    }
}
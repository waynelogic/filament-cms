<?php namespace Waynelogic\FilamentCms\Database\Scopes;

use Illuminate\Database\Eloquent\Model as ModelBase;
use Illuminate\Database\Eloquent\Scope as ScopeInterface;
use Illuminate\Database\Eloquent\Builder as BuilderBase;

class SortableScope implements ScopeInterface
{
    /**
     * apply the scope to a given Eloquent query builder.
     */
    public function apply(BuilderBase $builder, ModelBase $model): void
    {
        $builder->getQuery()->orderBy($model->getQualifiedSortOrderColumn());
    }

    /**
     * extend the Eloquent query builder.
     */
    public function extend(BuilderBase $builder): void
    {
        $removeOnMethods = ['reorder', 'orderBy', 'groupBy'];

        foreach ($removeOnMethods as $method) {
            $builder->macro($method, function ($builder, ...$args) use ($method) {
                $builder
                    ->withoutGlobalScope($this)
                    ->getQuery()
                    ->$method(...$args)
                ;

                return $builder;
            });
        }
    }
}

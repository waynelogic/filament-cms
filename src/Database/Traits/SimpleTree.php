<?php

namespace Waynelogic\FilamentCms\Database\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

trait SimpleTree
{
    /**
     * Boot the trait.
     */
    public static function bootSimpleTree(): void
    {
        // Опционально: отключите глобальную сортировку, если мешает
        static::addGlobalScope('simpleTree', fn($query) => $query->orderBy('parent_id')->orderBy('sort_order'));

        static::creating(fn($model) => $model->ensureSortOrder());
        static::updating(function ($model) {
            $model->ensureSortOrder();
            $model->preventCycles();
        });
    }

    public static function getTree(): Collection
    {
        $items = static::all();

        // Группируем по parent_id
        $grouped = $items->groupBy('parent_id');

        // Рекурсивно строим дерево
        $fn = function ($parentId = null) use (&$fn, $grouped) {
            return ($grouped[$parentId] ?? collect())->map(function ($item) use ($fn) {
                $item->setRelation('children', $fn($item->id));
                return $item;
            })->values(); // сохраняем индексы как 0,1,2...
        };

        return $fn();
    }

    /**
     * Ensure sort_order is set correctly based on current parent_id.
     */
    protected function ensureSortOrder(): void
    {
        if ($this->isDirty('sort_order') && $this->sort_order !== null) {
            return;
        }

        // Единственный запрос на MAX(sort_order)
        $maxOrder = DB::table($this->getTable())
            ->where('parent_id', $this->parent_id)
            ->max('sort_order');

        $this->sort_order = $maxOrder === null ? 0 : (int) $maxOrder + 1;
    }

    /**
     * Prevent cycles when changing parent_id.
     */
    protected function preventCycles(): void
    {
        if (!$this->isDirty('parent_id')) {
            return;
        }

        $newParentId = $this->parent_id;
        $myId = $this->getKey();

        if ($newParentId === null) return;
        if ($newParentId == $myId) {
            throw new \LogicException("A node cannot be its own parent.");
        }

        // Проверяем цикл: является ли newParentId потомком myId?
        if ($this->wouldCreateCycle($myId, $newParentId)) {
            throw new \LogicException("Cannot set parent: it would create a cycle.");
        }
    }

    /**
     * Check if setting $parentId as parent of $nodeId creates a cycle.
     * We walk UP from $parentId to root. If we meet $nodeId → cycle.
     */
    protected function wouldCreateCycle($forbiddenAncestorId, $currentId): bool
    {
        $visited = [];
        $table = $this->getTable();

        while ($currentId !== null) {
            if ($currentId == $forbiddenAncestorId) {
                return true;
            }

            // Защита от бесконечного цикла (если в БД уже есть цикл)
            if (isset($visited[$currentId])) {
                break;
            }
            $visited[$currentId] = true;

            // ОДИН запрос на уровень (минимум данных)
            $parent_id = DB::table($table)
                ->where('id', $currentId)
                ->value('parent_id');

            if ($parent_id === null) break;
            $currentId = (int) $parent_id;
        }

        return false;
    }

    // --- Relations ---

    public function parent()
    {
        return $this->belongsTo(static::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(static::class, 'parent_id')->orderBy('sort_order');
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    // --- Tree Navigation (оптимизировано) ---

    /**
     * Get ancestor IDs (not full models) — fast.
     */
    public function ancestorIds(): array
    {
        $ids = [];
        $currentId = $this->parent_id;
        $table = $this->getTable();
        $visited = [];

        while ($currentId !== null) {
            if (isset($visited[$currentId])) break; // cycle guard
            $visited[$currentId] = true;

            $ids[] = $currentId;

            $currentId = DB::table($table)
                ->where('id', $currentId)
                ->value('parent_id');
        }

        return array_reverse($ids); // root → direct parent
    }

    /**
     * Get full ancestor models (now efficient).
     */
    public function ancestors(): Collection
    {
        $ids = $this->ancestorIds();
        if (empty($ids)) return collect();

        return static::whereIn('id', $ids)
            ->orderByRaw(DB::raw("FIELD(id, " . implode(',', $ids) . ")"))
            ->get();
    }

    public function treePath(): Collection
    {
        return $this->ancestors()->push($this);
    }

    public function isAncestorOf($other): bool
    {
        $otherId = is_object($other) ? $other->getKey() : $other;
        return in_array($otherId, $this->descendantIds(), true);
    }

    public function isDescendantOf($other): bool
    {
        $otherId = is_object($other) ? $other->getKey() : $other;
        return in_array($otherId, $this->ancestorIds(), true);
    }

    /**
     * Get all descendant IDs (BFS, efficient).
     */
    public function descendantIds(): array
    {
        $ids = [];
        $queue = [$this->getKey()];
        $table = $this->getTable();

        while (!empty($queue)) {
            $parentId = array_shift($queue);
            $children = DB::table($table)
                ->where('parent_id', $parentId)
                ->pluck('id')
                ->toArray();

            if (!empty($children)) {
                $ids = array_merge($ids, $children);
                $queue = array_merge($queue, $children);
            }
        }

        return $ids;
    }

    /**
     * Get all descendant models.
     */
    public function descendants(): Collection
    {
        $ids = $this->descendantIds();
        return $ids ? static::whereIn('id', $ids)->get() : collect();
    }

    // --- Scopes ---

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOfParent($query, $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    // --- Utility ---

    public function moveToParent($parentId): self
    {
        $this->parent_id = $parentId;
        $this->sort_order = null;
        $this->save();
        return $this;
    }
}
<?php

namespace Modules\ResellerHub\App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasResellerSorting
{
    /**
     * Apply dynamic sorting to an Eloquent query using a declarative sort map.
     *
     * @param Builder $query
     * @param Request|null $request
     * @param array<string, string|callable> $sortMap
     * @param string $defaultColumn
     * @param string $defaultDirection
     * @return Builder
     */
    protected function applySorting(
        Builder $query,
        ?Request $request = null,
        array $sortMap = [],
        string $defaultColumn = 'id',
        string $defaultDirection = 'desc'
    ): Builder {
        $req = $request ?? request();
        $sortBy = (string)$req->input('sort_by', 'latest');
        $defaultOrder = strtolower($defaultDirection) === 'asc' ? 'asc' : 'desc';
        $sortOrder = strtolower((string)$req->input('sort_order', $defaultOrder)) === 'asc' ? 'asc' : 'desc';

        // Direct shortcut for oldest
        if ($sortBy === 'oldest') {
            return $query->orderBy($defaultColumn, 'asc');
        }

        // Direct shortcut for default / id / latest
        if (in_array($sortBy, ['latest', 'id', 'default', ''], true)) {
            return $query->orderBy($defaultColumn, $sortOrder);
        }

        // Check if sortBy itself is directly registered in sortMap (exact match first)
        if (isset($sortMap[$sortBy])) {
            $handler = $sortMap[$sortBy];
            if (is_callable($handler)) {
                $handler($query, $sortOrder);
            } else {
                $query->orderBy($handler, $sortOrder);
            }
            return $query;
        }

        // Detect direction suffix: _asc or _desc
        $extractedOrder = $sortOrder;
        $baseKey = $sortBy;
        if (str_ends_with($sortBy, '_asc')) {
            $baseKey = substr($sortBy, 0, -4);
            $extractedOrder = 'asc';
        } elseif (str_ends_with($sortBy, '_desc')) {
            $baseKey = substr($sortBy, 0, -5);
            $extractedOrder = 'desc';
        }

        if ($baseKey !== $sortBy && isset($sortMap[$baseKey])) {
            $handler = $sortMap[$baseKey];
            if (is_callable($handler)) {
                $handler($query, $extractedOrder);
            } else {
                $query->orderBy($handler, $extractedOrder);
            }
            return $query;
        }

        // Fallback default sorting
        return $query->orderBy($defaultColumn, $defaultDirection);
    }
}

<?php

namespace Modules\ResellerHub\App\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasResellerPagination
{
    /**
     * Resolve and validate the per_page parameter from request.
     *
     * @param Request|null $request
     * @param int $default
     * @param array<int> $allowed
     * @return int
     */
    protected function resolvePerPage(
        ?Request $request = null,
        int $default = 15,
        array $allowed = [5, 10, 15, 25, 50, 100]
    ): int {
        $req = $request ?? request();
        $perPage = (int) $req->input('per_page', $default);

        if (!in_array($perPage, $allowed, true)) {
            return $default;
        }

        return $perPage;
    }

    /**
     * Alias for resolvePerPage.
     *
     * @param Request|null $request
     * @param int $default
     * @param array<int> $allowed
     * @return int
     */
    protected function getPerPage(
        ?Request $request = null,
        int $default = 15,
        array $allowed = [5, 10, 15, 25, 50, 100]
    ): int {
        return $this->resolvePerPage($request, $default, $allowed);
    }

    /**
     * Paginate an Eloquent query with validated per_page and keep query string.
     *
     * @param Builder $query
     * @param Request|null $request
     * @param int $default
     * @param array<int> $allowed
     * @return LengthAwarePaginator
     */
    protected function paginateWithQuery(
        Builder $query,
        ?Request $request = null,
        int $default = 15,
        array $allowed = [5, 10, 15, 25, 50, 100]
    ): LengthAwarePaginator {
        $perPage = $this->resolvePerPage($request, $default, $allowed);

        return $query->paginate($perPage)->withQueryString();
    }
}

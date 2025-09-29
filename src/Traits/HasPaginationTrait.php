<?php

declare(strict_types=1);

namespace Asterios\Core\Traits;

use Asterios\Core\Exception\ModelInvalidArgumentException;
use Asterios\Core\Model;
use Asterios\Core\Support\PaginateResult;
use Asterios\Core\Exception\ModelException;
use Asterios\Core\Logger;

/**
 * @mixin Model
 */
trait HasPaginationTrait
{
    /**
     * @return int
     */
    protected function getTotalCount(): int
    {
        $countModel = clone $this;

        try
        {
            $countModel->get_count();
            if ($countModel->has_result())
            {
                return (int) $countModel->as_array()[0]['count'];
            }
        }
        catch (ModelException $e)
        {
            Logger::forge()->fatal(
                'Could not get total count in HasPaginationTrait!',
                ['exception' => $e->getMessage()]
            );
        }

        return 0;
    }

    /**
     * @param int $page
     * @param int $perPage
     * @param int $total
     * @return int
     */
    protected function getOffset(int $page, int $perPage, int $total): int
    {
        $totalPages = (int) ceil($total / $perPage);
        $currentPage = min(max(1, $page), $totalPages > 0 ? $totalPages : 1);

        return ($currentPage - 1) * $perPage;
    }

    /**
     * @param int $page
     * @param int $perPage
     * @param int $total
     * @return int
     */
    protected function resolveCurrentPage(int $page = 1, int $perPage = 15, int $total = 0): int
    {
        $totalPages = (int) ceil($total / $perPage);
        return min(max(1, $page), $totalPages > 0 ? $totalPages : 1);
    }

    /**
     * @param int $page
     * @param int $perPage
     * @return PaginateResult
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     */
    public function paginate(int $page = 1, int $perPage = 15): PaginateResult
    {
        $total = $this->getTotalCount();
        $currentPage = $this->resolveCurrentPage($page, $perPage, $total);
        $offset = $this->getOffset($currentPage, $perPage, $total);

        $itemsModel = clone $this;
        $itemsModel->limit($perPage, $offset)->execute();

        $items = $itemsModel->has_result() ? $itemsModel->as_array() : [];

        return new PaginateResult(
            $items,
            $total,
            $page,
            $perPage,
            $currentPage
        );
    }
}

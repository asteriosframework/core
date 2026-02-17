<?php

declare(strict_types=1);

namespace Asterios\Core\Traits;

use Asterios\Core\Exception\ConfigLoadException;
use Asterios\Core\Exception\ModelException;
use Asterios\Core\Exception\ModelInvalidArgumentException;
use Asterios\Core\Logger;
use Asterios\Core\Model;
use Asterios\Core\Support\PaginateResult;

/**
 * @mixin Model
 */
trait HasPaginationTrait
{
    /**
     * @param int $page
     * @param int $perPage
     * @return PaginateResult
     * @throws ConfigLoadException
     * @throws ModelException
     * @throws ModelInvalidArgumentException
     */
    public function paginate(int $page = 1, int $perPage = 15): PaginateResult
    {
        $total = $this->getTotalCount();
        $currentPage = $this->resolveCurrentPage($page, $perPage, $total);
        $offset = $this->getOffset($currentPage, $perPage, $total);

        $itemsModel = clone $this;
        $itemsModel->limit($perPage, $offset)
            ->execute();

        $items = $itemsModel->hasResult()
            ? $itemsModel->asArray()
            : [];

        return new PaginateResult(
            $items,
            $total,
            $page,
            $perPage,
            $currentPage
        );
    }

    /**
     * @return int
     */
    protected function getTotalCount(): int
    {
        $countModel = clone $this;

        try
        {
            $countModel->getCount();

            if ($countModel->hasResult())
            {
                $result = $countModel->asArray();

                return isset($result[0]['count'])
                    ? (int)$result[0]['count']
                    : 0;
            }
        } catch (ModelException|ConfigLoadException $e)
        {
            Logger::forge()
                ->fatal(
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
    protected function resolveCurrentPage(int $page = 1, int $perPage = 15, int $total = 0): int
    {
        $totalPages = (int)ceil($total / $perPage);

        return min(max(1, $page), $totalPages > 0 ? $totalPages : 1);
    }

    /**
     * @param int $page
     * @param int $perPage
     * @param int $total
     * @return int
     */
    protected function getOffset(int $page, int $perPage, int $total): int
    {
        $totalPages = (int)ceil($total / $perPage);
        $currentPage = min(max(1, $page), $totalPages > 0 ? $totalPages : 1);

        return ($currentPage - 1) * $perPage;
    }
}

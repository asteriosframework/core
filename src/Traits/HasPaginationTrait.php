<?php

declare(strict_types=1);

namespace Asterios\Core\Traits;

use Asterios\Core\Cast;
use Asterios\Core\Exception\ModelException;
use Asterios\Core\Logger;

trait HasPaginationTrait
{
    /**
     * @param $model
     * @return int
     */
    protected function getTotalCount($model): int
    {
        $return = 0;
        $countModel = clone $model;
        $countModel->get_count();

        try
        {
            if ($countModel->has_result())
            {
                $return = Cast::forge()
                    ->int($countModel->as_array()[0]['count']);
            }
        }
        catch (ModelException $e)
        {
            Logger::forge()
                ->fatal('Could not get total count in HasPaginationTrait!', ['exception' => $e->getMessage()]);
        }

        return $return;
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

    /**
     * @param int $page
     * @param int $perPage
     * @param int $total
     * @return int
     */
    protected function resolveCurrentPage(int $page, int $perPage, int $total): int
    {
        $totalPages = (int)ceil($total / $perPage);

        return min(max(1, $page), $totalPages > 0 ? $totalPages : 1);
    }
}
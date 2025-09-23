<?php

declare(strict_types=1);

namespace Asterios\Core\Support;

class PaginateResult
{
    /**
     * @var array $items
     */
    private array $items;

    /**
     * @var int
     */
    private int $total;

    /**
     * @var int
     */
    private int $page;

    /**
     * @var int
     */
    private int $perPage;

    /**
     * @var int $currentPage
     */
    private int $currentPage;

    /**
     * @param array $items
     * @param int $total
     * @param int $page
     * @param int $perPage
     * @param int $currentPage
     */
    public function __construct(
        array $items,
        int $total,
        int $page,
        int $perPage,
        int $currentPage
    ) {
        $this->items = $items;
        $this->total = $total;
        $this->page = max(1, $page);
        $this->perPage = max(1, $perPage);
        $this->currentPage = $currentPage;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function getTotalPages(): int
    {
        return (int)ceil($this->total / $this->perPage);
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'data' => $this->items,
            'meta' => [
                'total' => $this->total,
                'page' => $this->page,
                'currentPage' => $this->currentPage,
                'perPage' => $this->perPage,
                'totalPages' => $this->getTotalPages(),
            ],
        ];
    }
}

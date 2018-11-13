<?php

namespace Beapp\Doctrine\Pagination;

use Doctrine\ORM\Query\Expr\OrderBy;

abstract class Pageable
{
    const ASC = 'ASC';

    /** @var int $page */
    private $page;

    /** @var int $size */
    private $size;

    /** @var OrderBy[] */
    private $orders = [];

    /** @var SearchPart[] */
    private $search;

    /**
     * PageRequest constructor.
     * @param int $page
     * @param int $size
     * @param OrderBy[]|array $orders
     * @param SearchPart[]|array $search
     */
    public function __construct(int $page, int $size, array $orders = [], array $search = [])
    {
        $this->page = $page;
        $this->size = $size;
        $this->orders = $orders;
        $this->search = $search;
    }

    /**
     * Returns the page to be returned.
     * @return int
     */
    function getPage(): int
    {
        return $this->page;
    }

    /**
     * Returns the number of items to be returned.
     * @return int
     */
    function getSize(): int
    {
        return $this->size;
    }

    /**
     * Returns the sorting parameters.
     * @return OrderBy[]
     */
    function getOrders(): array
    {
        return $this->orders;
    }

    /**
     * Returns search instructions to add to the query
     * @return SearchPart[]
     */
    public function getSearch(): array
    {
        return $this->search;
    }
}

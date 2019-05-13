<?php

namespace Beapp\Doctrine\Pagination;


use Doctrine\Common\Collections\Collection;
use IteratorAggregate;

interface PaginationInterface extends IteratorAggregate
{

    /**
     * Return the filtered collection
     *
     * @return Collection
     */
    public function getCollection(): Collection;

    /**
     * Return the total count of data without filters nor pagination
     *
     * @return int
     */
    public function count(): int;


    /**
     * Return the count of data after filtering and pagination
     *
     * @return int
     */
    public function getUnfilteredCount(): int;


}

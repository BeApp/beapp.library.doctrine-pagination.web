<?php

namespace Beapp\Doctrine\Pagination\Repository;

use Beapp\Doctrine\Pagination\Pageable;
use Beapp\Doctrine\Pagination\Pagination;
use Doctrine\ORM\EntityRepository;

abstract class PaginationRepository extends EntityRepository
{

    /**
     * @param Pageable|null $pageable
     * @param bool $fetchJoinCollection
     * @param bool|null $useOutputWalkers
     * @return Pagination
     */
    public function findAllPaginated(?Pageable $pageable, bool $fetchJoinCollection = true, ?bool $useOutputWalkers = null): Pagination
    {
        return new Pagination($this->createQueryBuilder('e'), $pageable, $fetchJoinCollection, $useOutputWalkers);
    }

}

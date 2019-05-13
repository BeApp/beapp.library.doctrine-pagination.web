<?php

namespace Beapp\Doctrine\Pagination\Repository;

use Beapp\Doctrine\Pagination\Pageable;
use Beapp\Doctrine\Pagination\DoctrinePagination;
use Doctrine\ORM\EntityRepository;

abstract class PaginationRepository extends EntityRepository
{

    /**
     * @param Pageable|null $pageable
     * @param bool          $fetchJoinCollection
     * @param bool|null     $useOutputWalkers
     * @param string        $entityAlias
     *
     * @return DoctrinePagination
     */
    public function findAllPaginated(?Pageable $pageable, bool $fetchJoinCollection = true, ?bool $useOutputWalkers = null, $entityAlias = 'e'): DoctrinePagination
    {
        return new DoctrinePagination($this->createQueryBuilder($entityAlias), $pageable, $fetchJoinCollection, $useOutputWalkers);
    }

}

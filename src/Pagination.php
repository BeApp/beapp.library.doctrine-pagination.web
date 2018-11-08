<?php

namespace App\Core\Doctrine\Pagination;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountOutputWalker;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator;

class Pagination extends Paginator
{

    /** @var QueryBuilder */
    private $originalQueryBuilder;

    /** @var int unfilteredCount */
    private $unfilteredCount;

    /**
     * Pagination constructor.
     * @param QueryBuilder $queryBuilder
     * @param Pageable|null $pageable
     * @param bool $fetchJoinCollection
     */
    public function __construct(QueryBuilder $queryBuilder, Pageable $pageable = null, bool $fetchJoinCollection = true)
    {
        $this->originalQueryBuilder = $this->cloneQuery($queryBuilder->getQuery());

        if (!is_null($pageable)) {
            $queryBuilder->setFirstResult($pageable->getPage() * $pageable->getSize());
            $queryBuilder->setMaxResults($pageable->getSize());

            // Sorting
            foreach ($pageable->getOrders() as $orderBy) {
                $queryBuilder->addOrderBy($orderBy);
            }

            // Filtering
            if (count($pageable->getSearch()) > 0) {
                $orX = $queryBuilder->expr()->orX();

                foreach ($pageable->getSearch() as $searchIndex => $search) {
                    $queryArgName = ':_search_part_' . $searchIndex;

                    if ($search->isRegex()) {
                        $orX->add($queryBuilder->expr()->like($search->getField(), $queryArgName));
                        $queryBuilder->setParameter($queryArgName, '%' . $search->getValue() . '%');
                    } else {
                        $orX->add($queryBuilder->expr()->eq($search->getField(), $queryArgName));
                        $queryBuilder->setParameter($queryArgName, $search->getValue());
                    }
                }

                $queryBuilder->andWhere($orX);
            }
        }

        parent::__construct($queryBuilder, $fetchJoinCollection);
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return new ArrayCollection($this->getIterator()->getArrayCopy());
    }

    /**
     * Sets the position of the first result to retrieve (the "offset").
     *
     * @param integer|null $firstResult The first result to return.
     * @return $this
     */
    public function setFirstResult($firstResult): self
    {
        $this->getQuery()->setFirstResult($firstResult);

        return $this;
    }

    /**
     * Sets the maximum number of results to retrieve (the "limit").
     *
     * @param integer|null $maxResults The maximum number of results to retrieve.
     * @return $this
     */
    public function setMaxResults($maxResults): self
    {
        $this->getQuery()->setMaxResults($maxResults);

        return $this;
    }

    /**
     * Fork of {@link Paginator#count()} to return count of all data without search parts for the given query.
     * This result may be different than a count on the whole entity as the original query builder may already have some basic filtering (ex: active entities, ...)
     *
     * @return int
     */
    public function getUnfilteredCount(): int
    {
        if ($this->unfilteredCount === null) {
            try {
                $this->unfilteredCount = array_sum(array_map('current', $this->getUnfilteredCountQuery()->getScalarResult()));
            } catch (\Exception $e) {
                $this->unfilteredCount = 0;
            }
        }

        return $this->unfilteredCount;
    }

    /**
     * Fork of {@code getCountQuery()} to count all data based on the origin query (without filtering nor pagination)
     *
     * @return \Doctrine\ORM\Query|Query
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getUnfilteredCountQuery(): Query
    {
        /* @var $countQuery Query */
        $countQuery = $this->cloneQuery($this->originalQueryBuilder);

        if (!$countQuery->hasHint(CountWalker::HINT_DISTINCT)) {
            $countQuery->setHint(CountWalker::HINT_DISTINCT, true);
        }

        $platform = $countQuery->getEntityManager()->getConnection()->getDatabasePlatform(); // law of demeter win

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult($platform->getSQLResultCasing('dctrn_count'), 'count');

        $countQuery->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, CountOutputWalker::class);
        $countQuery->setResultSetMapping($rsm);

        $countQuery->setFirstResult(null)->setMaxResults(null);

        $parser = new Parser($countQuery);
        $parameterMappings = $parser->parse()->getParameterMappings();
        /* @var $parameters \Doctrine\Common\Collections\Collection|\Doctrine\ORM\Query\Parameter[] */
        $parameters = $countQuery->getParameters();

        foreach ($parameters as $key => $parameter) {
            $parameterName = $parameter->getName();

            if (!(isset($parameterMappings[$parameterName]) || array_key_exists($parameterName, $parameterMappings))) {
                unset($parameters[$key]);
            }
        }

        $countQuery->setParameters($parameters);

        return $countQuery;
    }

    /**
     * Clones the given query.
     *
     * @param Query $query The query.
     * @return Query The cloned query.
     */
    private function cloneQuery(Query $query): Query
    {
        /* @var $cloneQuery Query */
        $cloneQuery = clone $query;

        $cloneQuery->setParameters(clone $query->getParameters());
        $cloneQuery->setCacheable(false);

        foreach ($query->getHints() as $name => $value) {
            $cloneQuery->setHint($name, $value);
        }

        return $cloneQuery;
    }
}

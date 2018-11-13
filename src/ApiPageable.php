<?php

namespace Beapp\Doctrine\Pagination;

use Doctrine\ORM\Query\Expr\OrderBy;
use Symfony\Component\HttpFoundation\Request;

class ApiPageable extends Pageable
{
    /**
     * @param Request $request
     * @param string $pageKey
     * @param string $limitKey
     * @param string $sortKey
     * @param string $directionKey
     * @return ApiPageable
     */
    public static function fromRequest(Request $request, string $pageKey = 'page', string $limitKey = 'limit', string $sortKey = 'sort', string $directionKey = 'direction'): self
    {
        $orders = [];
        if ($request->get($sortKey)) {
            $order = strtoupper($request->get($directionKey, self::ASC));
            if ($order !== 'ASC' && $order !== 'DESC') {
                $order = 'ASC';
            }

            $orders[] = new OrderBy($request->get($sortKey), $order);
        }

        return new self(
            max(0, intval($request->get($pageKey, 0))),
            max(0, intval($request->get($limitKey, 20))),
            $orders
        );
    }
}

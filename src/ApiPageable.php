<?php

namespace Beapp\Doctrine\Pagination;

use Doctrine\ORM\Query\Expr\OrderBy;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Request;

class ApiPageable extends Pageable
{
    /**
     * @param Request $request
     * @param string  $pageKey
     * @param string  $limitKey
     * @param string  $sortKey
     * @param string  $directionKey
     * @param array   $sortMapping
     *
     * @return ApiPageable
     */
    public static function fromRequest(Request $request, string $pageKey = 'page', string $limitKey = 'limit', string $sortKey = 'sort', string $directionKey = 'direction', $sortMapping = []): self
    {
        $orders = [];
        if ($sortField = $request->get($sortKey)) {
            if (array_key_exists($sortField, $sortMapping)) {
                $order = strtoupper($request->get($directionKey, self::ASC));
                if ($order !== 'ASC' && $order !== 'DESC') {
                    $order = 'ASC';
                }

                $orders[] = new OrderBy($sortMapping[$sortField], $order);
            }
        }

        return new self(
            max(0, intval($request->get($pageKey, 0))),
            max(0, intval($request->get($limitKey, 20))),
            $orders
        );
    }

    /**
     * @param ParamFetcher $paramFetcher
     * @param string       $pageKey
     * @param string       $limitKey
     * @param string       $sortKey
     * @param string       $directionKey
     * @param array        $sortMapping
     *
     * @return ApiPageable
     */
    public static function fromParamFetcher($paramFetcher, string $pageKey = 'page', string $limitKey = 'limit', string $sortKey = 'sort', string $directionKey = 'direction', $sortMapping = []): self
    {
        if (!class_exists('FOS\RestBundle\Request\ParamFetcher')) {
            throw new \LogicException('You must install friendsofsymfony/rest-bundle package in order to use this method');
        }

        if (!$paramFetcher instanceof ParamFetcher) {
            throw new \InvalidArgumentException(
                sprintf('paramFetcher must be an instance of ParamFetcher, %s provided', get_class($paramFetcher))
            );
        }

        $orders = [];
        if ($sortField = $paramFetcher->get($sortKey)) {
            if (array_key_exists($sortField, $sortMapping)) {
                $order = strtoupper($paramFetcher->get($directionKey));
                if ($order !== 'ASC' && $order !== 'DESC') {
                    $order = 'ASC';
                }

                $orders[] = new OrderBy($sortMapping[$sortField], $order);
            }
        }

        return new self(
            max(0, intval($paramFetcher->get($pageKey))),
            max(0, intval($paramFetcher->get($limitKey))),
            $orders
        );
    }
}

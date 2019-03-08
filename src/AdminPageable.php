<?php

namespace Beapp\Doctrine\Pagination;

use Doctrine\ORM\Query\Expr\OrderBy;
use Symfony\Component\HttpFoundation\Request;

class AdminPageable extends Pageable
{

    /**
     * @param Request $request
     * @param array $mapping
     * @return AdminPageable
     */
    public static function fromJqueryDatatablesRequest(Request $request, array $mapping): self
    {
        // Pagination
        $start = max(0, intval($request->get('start', 0)));
        $length = max(0, intval($request->get('length', 20)));

        $page = $start / $length;

        $columns = $request->get('columns', []);
        $orders = $request->get('order', []);
        $search = $request->get('search', []);

        if (is_string($search)) {
            $search = [
                'value' => $search,
                'regex' => false
            ];
        }

        // Sorting
        $orderExprs = [];
        foreach ($orders as $order) {
            $column = $columns[$order['column']];

            if ($column['orderable'] && array_key_exists($column['data'], $mapping)) {
                array_push($orderExprs, new OrderBy($mapping[$column['data']], $order['dir']));
            }
        }

        // Filtering
        $searchExprs = [];
        foreach ($columns as $column) {
            if ($column['searchable'] && array_key_exists($column['data'], $mapping)) {
                if (!empty($search['value'])) {
                    array_push($searchExprs, new SearchPart($mapping[$column['data']], $search['value'], boolval($search['regex'])));
                } elseif (!empty($column['search']['value'])) {
                    array_push($searchExprs, new SearchPart($mapping[$column['data']], $column['search']['value'], boolval($column['search']['regex'])));
                }
            }
        }

        return new self($page, $length, $orderExprs, $searchExprs);
    }

    public static function fromExportJqueryDatatablesRequest(Request $request, array $mapping): self
    {
        $request->query->set('start', 0);

        // @TODO: Maybe it's better to set the length to null in order to get all the values in the list
        $request->query->set('length', 9999);

        return self::fromJqueryDatatablesRequest($request, $mapping);
    }

    /**
     * @param Request $request
     * @return AdminPageable
     */
    public static function fromSelect2Request(Request $request): self
    {
        return new self(
            $request->get('page', 0),
            $request->get('length', 10)
        );
    }

    /**
     * @param Request $request
     * @return AdminPageable
     */
    public static function fromRequest(Request $request): self
    {
        $orders = [];
        if ($request->get('sort')) {
            $orders[] = new OrderBy($request->get('sort'), $request->get('direction', self::ASC));
        }

        return new self(
            $request->get('page', 0),
            $request->get('limit', 10),
            $orders
        );
    }
}

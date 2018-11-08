<?php

namespace App\Core\Doctrine\Pagination;

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
            $search = ['value' => $search];
        }

        // If the search value is empty the var search is empty too
        if (!empty($search) && strlen($search['value']) == 0) {
            $search = [];
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
        if (!empty($search)) {
            // Global filtering
            foreach ($columns as $column) {

                if ($column['searchable'] && array_key_exists($column['data'], $mapping) && !empty($column['search']['value'])) {
                    array_push(
                        $searchExprs,
                        new SearchPart($mapping[$column['data']], $search['value'], true)
                    );    // TODO Configure all jquery datatables in order to use regexp with search
//                array_push($searchExprs, new SearchPart($mapping[$column['data']], $search['value'], $search['regex']));
                }
            }
        } else {
            // Per-column Filtering
            foreach ($columns as $column) {
                $search = $column['search'];

                if ($column['searchable'] && array_key_exists($column['data'], $mapping) && !empty($search['value'])) {
                    array_push(
                        $searchExprs,
                        new SearchPart($mapping[$column['data']], $search['value'], true)
                    );    // TODO Configure all jquery datatables in order to use regexp with search
//                array_push($searchExprs, new SearchPart($mapping[$column['data']], $search['value'], $search['regex']));
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

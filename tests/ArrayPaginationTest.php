<?php

namespace Beapp\Doctrine\Pagination;

use PHPUnit\Framework\TestCase;

class ArrayPaginationTest extends TestCase
{

    public function testCount_noPageable()
    {
        $values = range(1, 100);
        self::assertEquals(0, (new ArrayPagination([]))->count());
        self::assertEquals(100, (new ArrayPagination($values))->count());
    }

    public function testCount_noFilter()
    {
        $values = range(1, 100);

        // Small pagination
        self::assertEquals(100, (new ArrayPagination($values, new Pageable(0, 10)))->count());
        self::assertEquals(100, (new ArrayPagination($values, new Pageable(3, 10)))->count());

        // Pagination bigger than set
        self::assertEquals(5, (new ArrayPagination(range(1, 5), new Pageable(0, 10)))->count());
        self::assertEquals(5, (new ArrayPagination(range(1, 5), new Pageable(1, 10)))->count());
    }

    public function testGetFilteredAndSlicedValues_noPageable()
    {
        $values = range(1, 100);
        self::assertEquals([], (new ArrayPagination([]))->getFilteredAndSlicedValues());
        self::assertEquals(range(1, 100), (new ArrayPagination($values))->getFilteredAndSlicedValues());
    }

    public function testGetFilteredAndSlicedValues_noFilter()
    {
        $values = range(1, 100);

        // Small pagination
        self::assertEquals(range(1, 10), (new ArrayPagination($values, new Pageable(0, 10)))->getFilteredAndSlicedValues());
        self::assertEquals(range(31, 40), (new ArrayPagination($values, new Pageable(3, 10)))->getFilteredAndSlicedValues());

        // Pagination bigger than set
        self::assertEquals(range(1, 5), (new ArrayPagination(range(1, 5), new Pageable(0, 10)))->getFilteredAndSlicedValues());
        self::assertEquals([], (new ArrayPagination(range(1, 5), new Pageable(1, 10)))->getFilteredAndSlicedValues());
    }

    public function testGetUnfilteredCount_noPageable()
    {
        $values = range(1, 100);
        self::assertEquals(0, (new ArrayPagination([]))->getUnfilteredCount());
        self::assertEquals(100, (new ArrayPagination($values))->getUnfilteredCount());
    }

    public function testGetUnfilteredCount_noFilter()
    {
        $values = range(1, 100);

        // Small pagination
        self::assertEquals(100, (new ArrayPagination($values, new Pageable(0, 10)))->getUnfilteredCount());
        self::assertEquals(100, (new ArrayPagination($values, new Pageable(3, 10)))->getUnfilteredCount());

        // Pagination bigger than set
        self::assertEquals(5, (new ArrayPagination(range(1, 5), new Pageable(0, 10)))->getUnfilteredCount());
        self::assertEquals(5, (new ArrayPagination(range(1, 5), new Pageable(1, 10)))->getUnfilteredCount());
    }
}

<?php

namespace Beapp\Doctrine\Pagination;


use ArrayIterator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Iterator;

class ArrayPagination implements PaginationInterface
{

    /** @var array */
    private $values;
    /** @var array|null */
    private $filteredValues;
    /** @var Pageable */
    private $pageable;

    public function __construct(array $values, Pageable $pageable = null)
    {
        $this->values = $values;
        $this->pageable = $pageable;
    }

    public function getIterator(): Iterator
    {
        return new ArrayIterator($this->getFilteredAndSlicedValues());
    }

    public function getCollection(): Collection
    {
        return new ArrayCollection($this->getFilteredAndSlicedValues());
    }

    public function count(): int
    {
        return count($this->getFilteredValues());
    }

    public function getUnfilteredCount(): int
    {
        return count($this->values);
    }

    private function getFilteredAndSlicedValues()
    {
        return array_slice($this->getFilteredValues(), $this->pageable->getPage() * $this->pageable->getSize(), $this->pageable->getSize());
    }

    private function getFilteredValues()
    {
        if ($this->filteredValues == null) {
            $values = $this->values;

            // Sort values
            if (!empty($this->pageable->getOrders())) {
                foreach ($this->pageable->getOrders() as $order) {
                    foreach ($order->getParts() as $orderPart) {
                        $order = explode(' ', $orderPart);

                        usort($values, function ($value1, $value2) use ($order) {
                            if (mb_stristr($order[1], 'ASC') !== false) {
                                return strcmp($this->entryField($value1, $order[0]), $this->entryField($value2, $order[0]));
                            } else {
                                return strcmp($this->entryField($value2, $order[0]), $this->entryField($value1, $order[0]));
                            }
                        });
                    }
                }
            }

            // Filter values
            if (!empty($this->pageable->getSearch())) {
                $values = array_values(array_filter($values, function ($value) {
                    foreach ($this->pageable->getSearch() as $search) {
                        if ($search->isRegex()) {
                            $pattern = '#' . str_replace('#', '\#', $search->getValue()) . '#i';
                            if (preg_match($pattern, $this->entryField($value, $search->getField()))) {
                                return true;
                            }
                        } else {
                            if (mb_stristr($this->entryField($value, $search->getField()), $search->getValue()) !== false) {
                                return true;
                            }
                        }
                    }
                    return false;
                }));
            }

            $this->filteredValues = $values;
        }

        return $this->filteredValues;
    }

    private function entryField($entry, $fieldName)
    {
        if (is_object($entry)) {
            return $entry->$fieldName;
        } elseif (is_array($entry)) {
            return $entry[$fieldName];
        }
        return null;
    }

}

<?php

namespace App\Core\Doctrine\Pagination;

class SearchPart
{

    /** @var string $field */
    private $field;

    /** @var string $value */
    private $value;

    /** @var bool $regex */
    private $regex;

    /**
     * SearchPart constructor.
     * @param string $field
     * @param string $value
     * @param bool $regex
     */
    public function __construct(string $field, string $value, bool $regex)
    {
        $this->field = $field;
        $this->value = $value;
        $this->regex = $regex;
    }

    /**
     * @return string
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return bool
     */
    public function isRegex(): bool
    {
        return $this->regex;
    }

}

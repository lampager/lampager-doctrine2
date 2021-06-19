<?php

namespace Lampager\Doctrine2;

use Lampager\ArrayProcessor;

class Processor extends ArrayProcessor
{
    /**
     * @var string[]
     */
    protected $mapping;

    /**
     * Mapping: columnName/cursorKey -> fieldName
     *
     * @param  string[] $mapping
     * @return $this
     */
    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;

        return $this;
    }

    /**
     * Return comparable value from a row.
     *
     * @param  mixed      $row
     * @param  string     $column
     * @return int|string
     */
    protected function field($row, $column)
    {
        $column = $this->getMappedName($column);

        return is_callable([$row, $getter = 'get' . ucfirst($column)])
            ? $row->{$getter}()
            : parent::field($row, $column);
    }

    /**
     * Resolve column/cursor name in the result set.
     *
     * @param  string $column
     * @return string
     */
    protected function getMappedName($column)
    {
        return isset($this->mapping[$column])
            ? $this->mapping[$column]
            : static::dropTablePrefix($column);
    }

    /**
     * Drop table prefix on column name.
     *
     * @param  string $column
     * @return string
     */
    protected static function dropTablePrefix($column)
    {
        $segments = explode('.', $column);

        return end($segments);
    }
}

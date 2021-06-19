<?php

namespace Lampager\Doctrine2;

use Doctrine\ORM\Query as DoctrineQuery;
use Doctrine\ORM\QueryBuilder;
use Lampager\Concerns\HasProcessor;
use Lampager\Contracts\Cursor;
use Lampager\Paginator as BasePaginator;
use Lampager\Query;

class Paginator extends BasePaginator
{
    use HasProcessor;

    /**
     * @var bool
     */
    public $aggregated;

    /**
     * @return static
     */
    public static function create(QueryBuilder $builder)
    {
        return new static($builder);
    }

    public function __construct(QueryBuilder $builder)
    {
        $this->builder = $builder;
        $this->processor = new Processor();
    }

    /**
     * Alias for Paginator::limit().
     *
     * @param  int   $maxResults
     * @return $this
     */
    public function setMaxResults($maxResults)
    {
        return $this->limit($maxResults);
    }

    /**
     * Mapping: columnName/cursorKey -> fieldName
     *
     * @param  string[] $mapping
     * @return $this
     */
    public function setMapping(array $mapping)
    {
        $this->processor->setMapping($mapping);
        return $this;
    }

    /**
     * Declare that HAVING should be used instead of WHERE.
     *
     * @param  bool  $aggregated
     * @return $this
     */
    public function aggregated($aggregated = true)
    {
        $this->aggregated = $aggregated;
        return $this;
    }

    /**
     * Convert: Lampager Query -> Doctrine Query
     *
     * @return DoctrineQuery
     */
    public function transform(Query $query)
    {
        $compiler = new Compiler($this->aggregated);

        return $compiler
            ->compile($this->builder, $query->selectOrUnionAll())
            ->getQuery();
    }

    /**
     * Build Doctrine Query from cursor.
     *
     * @param  Cursor|int[]|string[] $cursor
     * @return DoctrineQuery
     */
    public function build($cursor = [])
    {
        return $this->transform($this->configure($cursor));
    }

    /**
     * Run Doctrine Query from cursor to process results.
     *
     * @param  Cursor|int[]|string[] $cursor
     * @param  null|int              $hydrationMode
     * @return array
     */
    public function paginate($cursor = [], $hydrationMode = null)
    {
        $query = $this->configure($cursor);
        $doctrineQuery = $this->transform($query);

        if ($hydrationMode) {
            $doctrineQuery->setHydrationMode($hydrationMode);
        }

        return $this->process($query, $doctrineQuery->execute());
    }
}

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
     * 「SQLカラム => 結果セットのキー名」の対応関係をマッピング
     *
     * @return $this
     */
    public function setMapping(array $mapping)
    {
        $this->processor->setMapping($mapping);
        return $this;
    }

    /**
     * Lampager Query を Doctrine Query に変換
     *
     * @return DoctrineQuery
     */
    public function transform(Query $query)
    {
        $compiler = new Compiler();

        return $compiler
            ->compile($this->builder, $query->selectOrUnionAll())
            ->getQuery();
    }

    /**
     * カーソルを使って Doctrine Query を作成
     *
     * @param  Cursor|int[]|string[] $cursor
     * @return DoctrineQuery
     */
    public function build($cursor = [])
    {
        return $this->transform($this->configure($cursor));
    }

    /**
     * カーソルを使って Doctrine Query を作成し，実行した結果を加工する
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

        return $this->process($query, $doctrineQuery->getResult());
    }
}

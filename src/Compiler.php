<?php

namespace Lampager\Doctrine2;

use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Lampager\Query\Select;
use Lampager\Query\SelectOrUnionAll;
use Lampager\Query\UnionAll;

class Compiler
{
    /**
     * Lampager Query を Doctrine Query Builder に変換
     *
     * @return QueryBuilder
     */
    public function compile(QueryBuilder $builder, SelectOrUnionAll $selectOrUnionAll)
    {
        if ($selectOrUnionAll instanceof Select) {
            return $this->compileSelect($builder, $selectOrUnionAll);
        }

        // @codeCoverageIgnoreStart
        if ($selectOrUnionAll instanceof UnionAll) {
            throw new \LogicException('Currently seekable pagination is not supported');
        }
        throw new \LogicException('Unreachable here');
        // @codeCoverageIgnoreEnd
    }

    /**
     * @return QueryBuilder
     */
    protected function compileSelect(QueryBuilder $builder, Select $select)
    {
        $this
            ->compileWhere($builder, $select)
            ->compileOrderBy($builder, $select)
            ->compileLimit($builder, $select);

        return $builder;
    }

    /**
     * @return $this
     */
    protected function compileWhere(QueryBuilder $builder, Select $select)
    {
        $orX = [];
        $bindingCount = 0;

        foreach ($select->where() as $group) {
            $andX = [];

            foreach ($group as $condition) {
                $key = 'lampager_parameter_' . ++$bindingCount;
                $andX[] = new Comparison($condition->left(), $condition->comparator(), ":$key");
                $builder->setParameter($key, $condition->right());
            }

            if ($andX) {
                $orX[] = $builder->expr()->andX(...$andX);
            }
        }

        if ($orX) {
            $builder->andWhere($builder->expr()->orX(...$orX));
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function compileOrderBy(QueryBuilder $builder, Select $select)
    {
        $builder->resetDQLPart('orderBy');

        foreach ($select->orders() as $order) {
            $builder->addOrderBy(...$order->toArray());
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function compileLimit(QueryBuilder $builder, Select $select)
    {
        $builder->setMaxResults($select->limit()->toInteger());

        return $this;
    }
}

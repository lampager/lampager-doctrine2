<?php

namespace Lampager\Doctrine2\Tests;

use Doctrine\ORM\Query;
use Lampager\Doctrine2\Paginator;
use Lampager\Doctrine2\Processor;
use Lampager\PaginationResult;
use PHPUnit\Framework\Attributes\After;
use PHPUnit\Framework\Attributes\Before;
use PHPUnit\Framework\Attributes\Test;

class ProcessorTest extends TestCase
{
    #[Before]
    protected function setUpFormatter(): void
    {
        Processor::setDefaultFormatter(function ($rows, array $meta) {
            foreach (['previous', 'next'] as $type) {
                if (isset($meta["{$type}Cursor"])) {
                    foreach ($meta["{$type}Cursor"] as $key => $value) {
                        if ($value instanceof \DateTimeInterface) {
                            $meta["{$type}Cursor"][$key] = $value->format('Y-m-d H:i:s');
                        }
                    }
                }
            }
            return new PaginationResult($rows, $meta);
        });
    }

    #[After]
    protected function removeFormatter(): void
    {
        Processor::restoreDefaultFormatter();
    }

    #[Test]
    public function testAscendingForwardStartInclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 1, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 3, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'p.updatedAt' => '2017-01-01 11:00:00',
                    'p.id' => 2,
                ],
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->forward()->limit(3)
                ->orderBy('p.updatedAt')
                ->orderBy('p.id')
                ->paginate()
        );
    }

    #[Test]
    public function testAscendingForwardStartExclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 1, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 3, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 5,
                ],
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->forward()->limit(3)
                ->orderBy('p.updatedAt')
                ->orderBy('p.id')
                ->exclusive()
                ->paginate()
        );
    }

    #[Test]
    public function testAscendingForwardInclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 3, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 2, 'updatedAt' => '2017-01-01 11:00:00'],
                ],
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'p.updatedAt' => '2017-01-01 11:00:00',
                    'p.id' => 4,
                ],
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->forward()->limit(3)
                ->orderBy('p.updatedAt')
                ->orderBy('p.id')
                ->paginate([
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 3,
                ])
        );
    }

    #[Test]
    public function testAscendingForwardExclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 2, 'updatedAt' => '2017-01-01 11:00:00'],
                    ['id' => 4, 'updatedAt' => '2017-01-01 11:00:00'],
                ],
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => false,
                'nextCursor' => null,
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->forward()->limit(3)
                ->orderBy('p.updatedAt')
                ->orderBy('p.id')
                ->exclusive()
                ->paginate([
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 3,
                ])
        );
    }

    #[Test]
    public function testAscendingBackwardStartInclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 2, 'updatedAt' => '2017-01-01 11:00:00'],
                    ['id' => 4, 'updatedAt' => '2017-01-01 11:00:00'],
                ],
                'hasPrevious' => true,
                'previousCursor' => [
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 3,
                ],
                'hasNext' => null,
                'nextCursor' => null,
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->backward()->limit(3)
                ->orderBy('p.updatedAt')
                ->orderBy('p.id')
                ->paginate()
        );
    }

    #[Test]
    public function testAscendingBackwardStartExclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 2, 'updatedAt' => '2017-01-01 11:00:00'],
                    ['id' => 4, 'updatedAt' => '2017-01-01 11:00:00'],
                ],
                'hasPrevious' => true,
                'previousCursor' => [
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 5,
                ],
                'hasNext' => null,
                'nextCursor' => null,
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->backward()->limit(3)
                ->orderBy('p.updatedAt')
                ->orderBy('p.id')
                ->exclusive()
                ->paginate()
        );
    }

    #[Test]
    public function testAscendingBackwardInclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 1, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 3, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => false,
                'previousCursor' => null,
                'hasNext' => null,
                'nextCursor' => null,
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->backward()->limit(3)
                ->orderBy('p.updatedAt')
                ->orderBy('p.id')
                ->paginate([
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 3,
                ])
        );
    }

    #[Test]
    public function testAscendingBackwardExclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 1, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => false,
                'previousCursor' => null,
                'hasNext' => null,
                'nextCursor' => null,
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->backward()->limit(3)
                ->orderBy('p.updatedAt')
                ->orderBy('p.id')
                ->exclusive()
                ->paginate([
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 3,
                ])
        );
    }

    #[Test]
    public function testDescendingForwardStartInclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 4, 'updatedAt' => '2017-01-01 11:00:00'],
                    ['id' => 2, 'updatedAt' => '2017-01-01 11:00:00'],
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 3,
                ],
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->forward()->limit(3)
                ->orderByDesc('p.updatedAt')
                ->orderByDesc('p.id')
                ->paginate()
        );
    }

    #[Test]
    public function testDescendingForwardStartExclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 4, 'updatedAt' => '2017-01-01 11:00:00'],
                    ['id' => 2, 'updatedAt' => '2017-01-01 11:00:00'],
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 5,
                ],
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->forward()->limit(3)
                ->orderByDesc('p.updatedAt')
                ->orderByDesc('p.id')
                ->exclusive()
                ->paginate()
        );
    }

    #[Test]
    public function testDescendingForwardInclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 3, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 1, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => false,
                'nextCursor' => null,
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->forward()->limit(3)
                ->orderByDesc('p.updatedAt')
                ->orderByDesc('p.id')
                ->paginate([
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 3,
                ])
        );
    }

    #[Test]
    public function testDescendingForwardExclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 1, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => false,
                'nextCursor' => null,
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->forward()->limit(3)
                ->orderByDesc('p.updatedAt')
                ->orderByDesc('p.id')
                ->exclusive()
                ->paginate([
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 3,
                ])
        );
    }

    #[Test]
    public function testDescendingBackwardStartInclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 3, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 1, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => true,
                'previousCursor' => [
                    'p.updatedAt' => '2017-01-01 11:00:00',
                    'p.id' => 2,
                ],
                'hasNext' => null,
                'nextCursor' => null,
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->backward()->limit(3)
                ->orderByDesc('p.updatedAt')
                ->orderByDesc('p.id')
                ->paginate()
        );
    }

    #[Test]
    public function testDescendingBackwardStartExclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 3, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 1, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => true,
                'previousCursor' => [
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 5,
                ],
                'hasNext' => null,
                'nextCursor' => null,
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->backward()->limit(3)
                ->orderByDesc('p.updatedAt')
                ->orderByDesc('p.id')
                ->exclusive()
                ->paginate()
        );
    }

    #[Test]
    public function testDescendingBackwardInclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 2, 'updatedAt' => '2017-01-01 11:00:00'],
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                    ['id' => 3, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => true,
                'previousCursor' => [
                    'p.updatedAt' => '2017-01-01 11:00:00',
                    'p.id' => 4,
                ],
                'hasNext' => null,
                'nextCursor' => null,
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->backward()->limit(3)
                ->orderByDesc('p.updatedAt')
                ->orderByDesc('p.id')
                ->paginate([
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 3,
                ])
        );
    }

    #[Test]
    public function testDescendingBackwardExclusive(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['id' => 4, 'updatedAt' => '2017-01-01 11:00:00'],
                    ['id' => 2, 'updatedAt' => '2017-01-01 11:00:00'],
                    ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
                ],
                'hasPrevious' => false,
                'previousCursor' => null,
                'hasNext' => null,
                'nextCursor' => null,
            ],
            Paginator::create($this->posts->createQueryBuilder('p'))
                ->backward()->limit(3)
                ->orderByDesc('p.updatedAt')
                ->orderByDesc('p.id')
                ->exclusive()
                ->paginate([
                    'p.updatedAt' => '2017-01-01 10:00:00',
                    'p.id' => 3,
                ])
        );
    }

    #[Test]
    public function testArrayResult(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    ['somethingId' => 3, 'somethingUpdatedAt' => '2017-01-01 10:00:00'],
                    ['somethingId' => 5, 'somethingUpdatedAt' => '2017-01-01 10:00:00'],
                    ['somethingId' => 2, 'somethingUpdatedAt' => '2017-01-01 11:00:00'],
                ],
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'p.updatedAt' => '2017-01-01 11:00:00',
                    'p.id' => 4,
                ],
            ],
            Paginator::create(
                $this->posts
                    ->createQueryBuilder('p')
                    ->select('p.id as somethingId', "CONCAT('', p.updatedAt) as somethingUpdatedAt")
            )
                ->forward()->setMaxResults(3)
                ->orderBy('p.updatedAt')
                ->orderBy('p.id')
                ->setMapping([
                    'p.id' => 'somethingId',
                    'p.updatedAt' => 'somethingUpdatedAt',
                ])
                ->paginate(
                    [
                        'p.updatedAt' => '2017-01-01 10:00:00',
                        'p.id' => 3,
                    ],
                    Query::HYDRATE_ARRAY
                )
        );
    }

    #[Test]
    public function testAggregatedPagination(): void
    {
        $this->assertResultSame(
            [
                'records' => [
                    [
                        'minId' => 1,
                        'maxId' => 5,
                        'groupedUpdatedAt' => '2017-01-01 10:00:00',
                    ],
                ],
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'groupedUpdatedAt' => '2017-01-01 11:00:00',
                    'maxId' => 2,
                ],
            ],
            Paginator::create(
                $this->posts
                    ->createQueryBuilder('p')
                    ->select('min(p.id) as minId, max(p.id) as maxId', "CONCAT('', p.updatedAt) as groupedUpdatedAt")
                    ->groupBy('p.updatedAt')
            )
                ->forward()->setMaxResults(1)
                ->aggregated()
                ->orderBy('groupedUpdatedAt')
                ->orderBy('maxId')
                ->setMapping([
                    'maxId' => 'minId',
                ])
                ->paginate([], Query::HYDRATE_ARRAY)
        );
    }
}

<?php

namespace Lampager\Doctrine2\Tests;

use Doctrine\ORM\Query;
use Lampager\Doctrine2\Paginator;
use Lampager\Doctrine2\Processor;
use Lampager\PaginationResult;

class ProcessorTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();

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

    protected function tearDown()
    {
        parent::tearDown();

        Processor::restoreDefaultFormatter();
    }

    /**
     * @test
     */
    public function testAscendingForwardStartInclusive()
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

    /**
     * @test
     */
    public function testAscendingForwardStartExclusive()
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

    /**
     * @test
     */
    public function testAscendingForwardInclusive()
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

    /**
     * @test
     */
    public function testAscendingForwardExclusive()
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

    /**
     * @test
     */
    public function testAscendingBackwardStartInclusive()
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

    /**
     * @test
     */
    public function testAscendingBackwardStartExclusive()
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

    /**
     * @test
     */
    public function testAscendingBackwardInclusive()
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

    /**
     * @test
     */
    public function testAscendingBackwardExclusive()
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

    /**
     * @test
     */
    public function testDescendingForwardStartInclusive()
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

    /**
     * @test
     */
    public function testDescendingForwardStartExclusive()
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

    /**
     * @test
     */
    public function testDescendingForwardInclusive()
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

    /**
     * @test
     */
    public function testDescendingForwardExclusive()
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

    /**
     * @test
     */
    public function testDescendingBackwardStartInclusive()
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

    /**
     * @test
     */
    public function testDescendingBackwardStartExclusive()
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

    /**
     * @test
     */
    public function testDescendingBackwardInclusive()
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

    /**
     * @test
     */
    public function testDescendingBackwardExclusive()
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

    /**
     * @test
     */
    public function testArrayResult()
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

    /**
     * @test
     */
    public function testAggregatedPagination()
    {
        $this->assertResultSame(
            [
                'records' => [
                    [
                        'minId' => '1',
                        'maxId' => '5',
                        'groupedUpdatedAt' => '2017-01-01 10:00:00',
                    ],
                ],
                'hasPrevious' => null,
                'previousCursor' => null,
                'hasNext' => true,
                'nextCursor' => [
                    'groupedUpdatedAt' => '2017-01-01 11:00:00',
                    'maxId' => '2',
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

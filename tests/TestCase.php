<?php

namespace Lampager\Doctrine2\Tests;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use Lampager\Doctrine2\Tests\Entities\Post;
use Lampager\Doctrine2\Tests\Repositories\PostRepository;
use PHPUnit\Framework\TestCase as BasesTestCase;

abstract class TestCase extends BasesTestCase
{
    /**
     * @var EntityManager
     */
    protected $entities;

    /**
     * @var PostRepository
     */
    protected $posts;

    protected static $data = [
        'posts' => [
            ['id' => 1, 'updatedAt' => '2017-01-01 10:00:00'],
            ['id' => 3, 'updatedAt' => '2017-01-01 10:00:00'],
            ['id' => 5, 'updatedAt' => '2017-01-01 10:00:00'],
            ['id' => 2, 'updatedAt' => '2017-01-01 11:00:00'],
            ['id' => 4, 'updatedAt' => '2017-01-01 11:00:00'],
        ],
    ];

    /**
     * @before
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
    protected function setUpDatabase()
    {
        $config = Setup::createAnnotationMetadataConfiguration(
            [__DIR__ . '/Entities'],
            true,
            null,
            null,
            false
        );
        $config->setAutoGenerateProxyClasses(true);
        $connection = [
            'driver' => 'pdo_sqlite',
            'dbname' => ':memory:',
        ];

        $this->entities = EntityManager::create($connection, $config);
        $this->posts = $this->entities->getRepository(Post::class);

        $method = [$this->connection(), 'executeStatement'];
        if (!is_callable($method)) {
            $method = [$this->connection(), 'exec'];
        }
        $method('CREATE TABLE posts(id INTEGER PRIMARY KEY, updated_at TEXT NOT NULL)');

        foreach (static::$data['posts'] as $row) {
            $post = new Post();
            $post->setId($row['id']);
            $post->setUpdatedAt(new \DateTime($row['updatedAt']));
            $this->entities->persist($post);
        }

        $this->entities->flush();
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    protected function connection()
    {
        return $this->entities->getConnection();
    }

    /**
     * @param mixed $expected
     * @param mixed $actual
     */
    protected function assertResultSame($expected, $actual)
    {
        $this->assertSame(
            json_decode(json_encode($expected), true),
            json_decode(json_encode($actual), true)
        );
    }

    /**
     * @param int $number
     * @return int|string
     */
    protected function number($number)
    {
        return version_compare(PHP_VERSION, '8.1', '>=') ? $number : "$number";
    }
}

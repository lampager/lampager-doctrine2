<?php

namespace Lampager\Doctrine2\Tests\Entities;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Lampager\Doctrine2\Tests\Repositories\PostRepository;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'posts')]
class Post implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    protected DateTimeInterface $updatedAt;

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setUpdatedAt(DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getUpdatedAt(): DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function __debugInfo(): array
    {
        return [
            'id' => $this->getId(),
            'updatedAt' => $this->updatedAt,
        ];
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'updatedAt' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}

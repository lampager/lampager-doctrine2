<?php

namespace Lampager\Doctrine2\Tests\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Lampager\Doctrine2\Tests\Repositories\TagRepository")
 * @ORM\Table(name="tags")
 */
class Tag
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var ArrayCollection|Post[]
     * @ORM\ManyToMany(targetEntity="Post", mappedBy="tags", fetch="EXTRA_LAZY")
     */
    private $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection|Post[]
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'id' => $this->getId(),
        ];
    }
}

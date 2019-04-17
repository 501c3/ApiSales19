<?php

namespace App\Entity\Model;

use Doctrine\Common\Collections\Collection;
use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * Person
 *
 * @ORM\Table(name="person")
 * @ORM\Entity(repositoryClass="App\Repository\Model\PersonRepository")
 */
class Person
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="years", type="smallint", nullable=false)
     */
    private $years;

    /**
     * @var array|null
     *
     * @ORM\Column(name="describe", type="json", nullable=true)
     */
    private $describe;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Model\Team", inversedBy="person")
     * @ORM\JoinTable(name="person_has_team",
     *   joinColumns={
     *     @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="team_id", referencedColumnName="id")
     *   }
     * )
     */
    private $team;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Model\Value", inversedBy="person")
     * @ORM\JoinTable(name="person_has_value",
     *   joinColumns={
     *     @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="value_id", referencedColumnName="id")
     *   }
     * )
     */
    private $value;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->team = new \Doctrine\Common\Collections\ArrayCollection();
        $this->value = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Person
     */
    public function setId(int $id): Person
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getYears(): int
    {
        return $this->years;
    }

    /**
     * @param int $years
     * @return Person
     */
    public function setYears(int $years): Person
    {
        $this->years = $years;
        return $this;
    }

    /**
     * @return array|null
     */
    public function getDescribe(): ?array
    {
        return $this->describe;
    }

    /**
     * @param array|null $describe
     * @return Person
     */
    public function setDescribe(?array $describe): Person
    {
        $this->describe = $describe;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getTeam(): Collection
    {
        return $this->team;
    }

    /**
     * @param Collection $team
     * @return Person
     */
    public function setTeam(Collection $team): Person
    {
        $this->team = $team;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getValue(): Collection
    {
        return $this->value;
    }

    /**
     * @param Collection $value
     * @return Person
     */
    public function setValue(Collection $value): Person
    {
        $this->value = $value;
        return $this;
    }



}

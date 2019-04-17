<?php

namespace App\Entity\Model;

use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * Team
 *
 * @ORM\Table(name="team", indexes={@ORM\Index(name="fk_team_team_class1_idx", columns={"team_class_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Model\TeamRepository")
 */
class Team
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
     * @var \App\Entity\Model\TeamClass
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Model\TeamClass")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="team_class_id", referencedColumnName="id")
     * })
     */
    private $teamClass;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Model\Person", mappedBy="team")
     */
    private $person;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->person = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Team
     */
    public function setId(int $id): Team
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return TeamClass
     */
    public function getTeamClass(): TeamClass
    {
        return $this->teamClass;
    }

    /**
     * @param TeamClass $teamClass
     * @return Team
     */
    public function setTeamClass(TeamClass $teamClass): Team
    {
        $this->teamClass = $teamClass;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPerson(): \Doctrine\Common\Collections\Collection
    {
        return $this->person;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $person
     * @return Team
     */
    public function setPerson(\Doctrine\Common\Collections\Collection $person): Team
    {
        $this->person = $person;
        return $this;
    }



}

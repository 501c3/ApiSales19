<?php

namespace App\Entity\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * Event
 *
 * @ORM\Table(name="event", indexes={@ORM\Index(name="fk_event_model1_idx", columns={"model_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Model\EventRepository")
 */
class Event
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
     * @var array|null
     *
     * @ORM\Column(name="describe", type="json", nullable=true)
     */
    private $describe;

    /**
     * @var \App\Entity\Model\Model
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Model\Model")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="model_id", referencedColumnName="id")
     * })
     */
    private $model;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Model\TeamClass", inversedBy="event")
     * @ORM\JoinTable(name="event_has_team_class",
     *   joinColumns={
     *     @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="team_class_id", referencedColumnName="id")
     *   }
     * )
     */
    private $teamClass;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->teamClass = new ArrayCollection();
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
     * @return Event
     */
    public function setId(int $id): Event
    {
        $this->id = $id;
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
     * @return Event
     */
    public function setDescribe(?array $describe): Event
    {
        $this->describe = $describe;
        return $this;
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @param Model $model
     * @return Event
     */
    public function setModel(Model $model): Event
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getTeamClass(): Collection
    {
        return $this->teamClass;
    }

    /**
     * @param Collection $teamClass
     * @return Event
     */
    public function setTeamClass(Collection $teamClass): Event
    {
        $this->teamClass = $teamClass;
        return $this;
    }


}

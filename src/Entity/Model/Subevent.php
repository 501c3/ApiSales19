<?php

namespace App\Entity\Model;

use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * Subevent
 *
 * @ORM\Table(name="subevent", indexes={@ORM\Index(name="fk_subevent_event1_idx", columns={"event_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Model\SubeventRepository")
 */
class Subevent
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
     * @var array
     *
     * @ORM\Column(name="`describe`", type="json", nullable=false)
     */
    private $describe;

    /**
     * @var \App\Entity\Model\Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Model\Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    private $event;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Subevent
     */
    public function setId(int $id): Subevent
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array
     */
    public function getDescribe(): array
    {
        return $this->describe;
    }

    /**
     * @param array $describe
     * @return Subevent
     */
    public function setDescribe(array $describe): Subevent
    {
        $this->describe = $describe;
        return $this;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     * @return Subevent
     */
    public function setEvent(Event $event): Subevent
    {
        $this->event = $event;
        return $this;
    }



}

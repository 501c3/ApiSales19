<?php

namespace App\Entity\Competition;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subevent
 *
 * @ORM\Table(name="subevent", indexes={@ORM\Index(name="fk_subevent_event1_idx", columns={"event_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Competition\SubeventRepository")
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
     * @var json
     *
     * @ORM\Column(name="describe", type="json", nullable=false)
     */
    private $describe;

    /**
     * @var \App\Entity\Competition\Event
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition\Event")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="event_id", referencedColumnName="id")
     * })
     */
    private $event;


}

<?php

namespace App\Entity\Competition;

use Doctrine\ORM\Mapping as ORM;

/**
 * Heat
 *
 * @ORM\Table(name="heat", indexes={@ORM\Index(name="fk_heat_subevent1_idx", columns={"subevent_id"}), @ORM\Index(name="fk_heat_competition1_idx", columns={"competition_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Competition\HeatRepository")
 */
class Heat
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
     * @var int|null
     *
     * @ORM\Column(name="number", type="smallint", nullable=true)
     */
    private $number;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=1, nullable=false, options={"fixed"=true})
     */
    private $status;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="time", type="datetime", nullable=true)
     */
    private $time;

    /**
     * @var \App\Entity\Competition\Competition
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition\Competition")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="competition_id", referencedColumnName="id")
     * })
     */
    private $competition;

    /**
     * @var \App\Entity\Competition\Subevent
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Competition\Subevent")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="subevent_id", referencedColumnName="id")
     * })
     */
    private $subevent;


}

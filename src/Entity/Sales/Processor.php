<?php

namespace App\Entity\Sales;

use Doctrine\ORM\Mapping as ORM;

/**
 * Processor
 *
 * @ORM\Table(name="processor", indexes={@ORM\Index(name="fk_processor_channel1_idx", columns={"channel_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\ProcessorRepository")
 */
class Processor
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var bool|null
     *
     * @ORM\Column(name="is_live", type="boolean", nullable=true)
     */
    private $isLive;

    /**
     * @var json|null
     *
     * @ORM\Column(name="live", type="json", nullable=true)
     */
    private $live;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var json|null
     *
     * @ORM\Column(name="test", type="json", nullable=true)
     */
    private $test;

    /**
     * @var \App\Entity\Sales\Channel
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Channel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     * })
     */
    private $channel;


}

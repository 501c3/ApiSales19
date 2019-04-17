<?php

namespace App\Entity\Sales;

use Doctrine\ORM\Mapping as ORM;

/**
 * Receipts
 *
 * @ORM\Table(name="receipts", indexes={@ORM\Index(name="fk_receipts_processor1_idx", columns={"processor_id"}), @ORM\Index(name="fk_receipts_workarea1_idx", columns={"workarea_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\ReceiptsRepository")
 */
class Receipts
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
     * @var string
     *
     * @ORM\Column(name="amount", type="decimal", precision=7, scale=2, nullable=false)
     */
    private $amount;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var json
     *
     * @ORM\Column(name="data", type="json", nullable=false)
     */
    private $data;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=80, nullable=false)
     */
    private $name;

    /**
     * @var \App\Entity\Sales\Processor
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Processor")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="processor_id", referencedColumnName="id")
     * })
     */
    private $processor;

    /**
     * @var \App\Entity\Sales\Workarea
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Workarea")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="workarea_id", referencedColumnName="id")
     * })
     */
    private $workarea;


}

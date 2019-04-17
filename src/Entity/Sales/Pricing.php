<?php

namespace App\Entity\Sales;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pricing
 *
 * @ORM\Table(name="pricing", indexes={@ORM\Index(name="fk_pricing_channel1_idx", columns={"channel_id"}), @ORM\Index(name="idx_start_at", columns={"start_at"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\PricingRepository")
 */
class Pricing
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_at", type="string", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $startAt;

    /**
     * @var array|null
     *
     * @ORM\Column(name="inventory", type="json", nullable=true)
     */
    private $inventory;

    /**
     * @var \App\Entity\Sales\Channel
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="App\Entity\Sales\Channel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     * })
     */
    private $channel;

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getStartAt(): \DateTime
    {
        return new \DateTime($this->startAt);
    }

    /**
     * @param \DateTime $startAt
     * @return Pricing
     */
    public function setStartAt(\DateTime $startAt): Pricing
    {
        $this->startAt = $startAt->format('Y-m-d');
        return $this;
    }

    /**
     * @return array|null
     */
    public function getInventory(): ?array
    {
        return $this->inventory;
    }

    /**
     * @param array|null $inventory
     * @return Pricing
     */
    public function setInventory(?array $inventory): Pricing
    {
        $this->inventory = $inventory;
        return $this;
    }

    /**
     * @return Channel
     */
    public function getChannel(): Channel
    {
        return $this->channel;
    }

    /**
     * @param Channel $channel
     * @return Pricing
     */
    public function setChannel(Channel $channel): Pricing
    {
        $this->channel = $channel;
        return $this;
    }



}

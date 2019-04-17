<?php

namespace App\Entity\Sales;

use Doctrine\ORM\Mapping as ORM;

/**
 * Workarea
 *
 * @ORM\Table(name="workarea", indexes={@ORM\Index(name="fk_workarea_tag1_idx", columns={"tag_id"}), @ORM\Index(name="fk_workarea_user1_idx", columns={"user_id"}), @ORM\Index(name="fk_workarea_channel1_idx", columns={"channel_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\WorkareaRepository")
 */
class Workarea
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
     * @var \DateTime|null
     *
     * @ORM\Column(name="processed_at", type="datetime", nullable=true)
     */
    private $processedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var \App\Entity\Sales\Channel
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Channel")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="channel_id", referencedColumnName="id")
     * })
     */
    private $channel;

    /**
     * @var \App\Entity\Sales\Tag
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Tag")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tag_id", referencedColumnName="id")
     * })
     */
    private $tag;

    /**
     * @var \App\Entity\Sales\User
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTime|null
     */
    public function getProcessedAt(): ?\DateTime
    {
        return $this->processedAt;
    }

    /**
     * @param \DateTime|null $processedAt
     * @return Workarea
     */
    public function setProcessedAt(?\DateTime $processedAt): Workarea
    {
        $this->processedAt = $processedAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return Workarea
     */
    public function setCreatedAt(\DateTime $createdAt): Workarea
    {
        $this->createdAt = $createdAt;
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
     * @return Workarea
     */
    public function setChannel(Channel $channel): Workarea
    {
        $this->channel = $channel;
        return $this;
    }

    /**
     * @return Tag
     */
    public function getTag(): Tag
    {
        return $this->tag;
    }

    /**
     * @param Tag $tag
     * @return Workarea
     */
    public function setTag(Tag $tag): Workarea
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Workarea
     */
    public function setUser(User $user): Workarea
    {
        $this->user = $user;
        return $this;
    }



}

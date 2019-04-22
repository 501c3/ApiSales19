<?php

namespace App\Entity\Sales;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Form
 *
 * @ORM\Table(name="form", indexes={@ORM\Index(name="fk_form_tag1_idx", columns={"tag_id"}), @ORM\Index(name="fk_form_workarea1_idx", columns={"workarea_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Sales\FormRepository")
 */
class Form
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var array
     *
     * @ORM\Column(name="content", type="json", nullable=false)
     */
    private $content;

    /**
     * @var string|null
     *
     * @ORM\Column(name="note", type="text", length=255, nullable=true)
     */
    private $note;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

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
     * @var Workarea
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Workarea")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="workarea_id", referencedColumnName="id")
     * })
     */
    private $workarea;

    /**
     * @var Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Sales\Picture", inversedBy="form")
     * @ORM\JoinTable(name="form_has_picture",
     *   joinColumns={
     *     @ORM\JoinColumn(name="form_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="picture_id", referencedColumnName="id")
     *   }
     * )
     */
    private $picture;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->picture = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }


    /**
     * @return array
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * @param array $content
     * @return Form
     */
    public function setContent(array $content): Form
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNote(): ?string
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     * @return Form
     */
    public function setNote(?string $note): Form
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @param \DateTime|null $updatedAt
     * @return Form
     */
    public function setUpdatedAt(?\DateTime $updatedAt): Form
    {
        $this->updatedAt = $updatedAt;
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
     * @return Form
     */
    public function setTag(Tag $tag): Form
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return Workarea
     */
    public function getWorkarea(): Workarea
    {
        return $this->workarea;
    }

    /**
     * @param Workarea $workarea
     * @return Form
     */
    public function setWorkarea(Workarea $workarea): Form
    {
        $this->workarea = $workarea;
        return $this;
    }

    /**
     * @return Collection
     */
    public function getPicture(): Collection
    {
        return $this->picture;
    }

    /**
     * @param Collection $picture
     * @return Form
     */
    public function setPicture(Collection $picture): Form
    {
        $this->picture = $picture;
        return $this;
    }

}

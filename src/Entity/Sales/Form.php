<?php

namespace App\Entity\Sales;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var json
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
     * @var \App\App\Entity\Sales\Workarea
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Sales\Workarea")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="workarea_id", referencedColumnName="id")
     * })
     */
    private $workarea;

    /**
     * @var \Doctrine\Common\Collections\Collection
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

}

<?php

namespace App\Entity\Model;

use /** @noinspection PhpUnusedAliasInspection */
    Doctrine\ORM\Mapping as ORM;

/**
 * Value
 *
 * @ORM\Table(name="value", indexes={@ORM\Index(name="fk_Value_domain1_idx", columns={"domain_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Model\ValueRepository")
 */
class Value
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="smallint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="abbr", type="string", length=6, nullable=true)
     */
    private $abbr;

    /**
     * @var \App\Entity\Model\Domain
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Model\Domain")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     * })
     */
    private $domain;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Model\Model", mappedBy="value")
     */
    private $model;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Model\Person", mappedBy="value")
     */
    private $person;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->model = new \Doctrine\Common\Collections\ArrayCollection();
        $this->person = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @return Value
     */
    public function setId(int $id): Value
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Value
     */
    public function setName(?string $name): Value
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAbbr(): ?string
    {
        return $this->abbr;
    }

    /**
     * @param string|null $abbr
     * @return Value
     */
    public function setAbbr(?string $abbr): Value
    {
        $this->abbr = $abbr;
        return $this;
    }

    /**
     * @return Domain
     */
    public function getDomain(): Domain
    {
        return $this->domain;
    }

    /**
     * @param Domain $domain
     * @return Value
     */
    public function setDomain(Domain $domain): Value
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getModel(): \Doctrine\Common\Collections\Collection
    {
        return $this->model;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $model
     * @return Value
     */
    public function setModel(\Doctrine\Common\Collections\Collection $model): Value
    {
        $this->model = $model;
        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPerson(): \Doctrine\Common\Collections\Collection
    {
        return $this->person;
    }

    /**
     * @param \Doctrine\Common\Collections\Collection $person
     * @return Value
     */
    public function setPerson(\Doctrine\Common\Collections\Collection $person): Value
    {
        $this->person = $person;
        return $this;
    }


}
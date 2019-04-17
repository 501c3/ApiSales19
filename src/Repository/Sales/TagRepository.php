<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/8/19
 * Time: 11:43 AM
 */

namespace App\Repository\Sales;


use App\Entity\Sales\Tag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TagRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $registry)
  {
      parent::__construct($registry, Tag::class);
  }

    /**
     * @param string $name
     * @return Tag
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
  public function fetch(string $name) : Tag
  {
      $tag = $this->findOneBy(['name'=>$name]);
      if(!$tag) {
          $tag = new Tag();
          $tag->setName($name);
          $em = $this->getEntityManager();
          $em->persist($tag);
          $em->flush();
      }
      return $tag;
  }
}
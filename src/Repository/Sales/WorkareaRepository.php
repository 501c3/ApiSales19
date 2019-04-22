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


use App\Entity\Sales\Channel;
use App\Entity\Sales\Tag;
use App\Entity\Sales\User;
use App\Entity\Sales\Workarea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class WorkareaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Workarea::class);
    }

    /**
     * @param Tag $tag
     * @param Channel $channel
     * @param User $user
     * @return Workarea
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(Tag $tag, Channel $channel, User $user)
    {
        $workarea = new Workarea();
        $workarea->setCreatedAt(new \DateTime('now'))
                ->setTag($tag)
                ->setChannel($channel)
                ->setUser($user)
                ->setCreatedAt(new \DateTime('now'));
        $em = $this->getEntityManager();
        $em->persist($workarea);
        $em->flush();
        return $workarea;
    }
}
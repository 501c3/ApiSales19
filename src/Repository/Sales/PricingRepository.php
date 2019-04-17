<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/8/19
 * Time: 11:42 AM
 */

namespace App\Repository\Sales;


use App\Entity\Sales\Pricing;
use App\Entity\Sales\Channel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class PricingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pricing::class);
    }

    /**
     * @param Channel $channel
     */
    public function clearPricing(Channel $channel)
    {
        $qb=$this->createQueryBuilder('pricing');
        $qb->delete('App\\Entity\\Sales\\Pricing','p')
            ->where('p.channel=:channel');
        $query = $qb->getQuery();
        $query->setParameter(':channel',$channel);
        $query->execute();
    }

    /**
     * @param Channel $channel
     * @param string $date
     * @param array $inventoryPricing
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function addPricing(Channel $channel,string $date, array $inventoryPricing)
    {
        $em = $this->getEntityManager();
        $pricing = new Pricing();
        $pricing->setChannel($channel)
            ->setStartAt(new \DateTime($date))
            ->setInventory($inventoryPricing);
        $em->persist($pricing);
        $em->flush();
    }
}
<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 3/8/19
 * Time: 11:41 AM
 */

namespace App\Repository\Sales;


use App\Entity\Sales\Channel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }


    public function configureChannel(array $heading)
    {
        $em = $this->getEntityManager();
        $channel = $this->findOneBy(['name' => $heading['channel']['name']]);
        $data = ['competition' => $heading['competition']['name'],
                 'city' => $heading['city']['name'],
                 'state' => $heading['state']['name'],
                 'venue' => $heading['venue']['name']];
        if (!$channel) {
            $channel = new Channel();
            $em->persist($channel);
        }

        $channel->setName($heading['channel']['name'])
                ->setLive(false)
                ->setLogo($heading['logo']['image'])
                ->setCreatedAt(new \DateTime('now'))
                ->setHeading($data);
        $em->flush();
        return $channel;
    }


}
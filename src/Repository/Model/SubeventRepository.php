<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/10/18
 * Time: 6:27 PM
 */

namespace App\Repository\Model;


use App\Entity\Model\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\Model\Subevent;

class SubeventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Subevent::class);
    }


    /**
     * @param string $substyle
     * @param Event $event
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function create(string $substyle,Event $event)
    {
        $describe = $event->getDescribe();
        $describe['substyle']=$substyle;
        $subevent = new Subevent();
        $subevent->setEvent($event)
                 ->setDescribe($describe);
        $em=$this->getEntityManager();
        $em->persist($subevent);
        $em->flush();
    }
}
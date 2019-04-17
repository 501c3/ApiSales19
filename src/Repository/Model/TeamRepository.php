<?php
/**
 * Copyright (c) 2019. Mark Garber.  All rights reserved.
 */

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 1/9/19
 * Time: 10:57 AM
 */

namespace App\Repository\Model;


use App\Entity\Model\Person;
use App\Entity\Model\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    private function queryBuilderBase() : QueryBuilder
    {
        $qb=$this->createQueryBuilder('team');
        $qb->select('team','class')
            ->from('team')
            ->innerJoin('team.teamClass','class')
            ->innerJoin('team.person','pA');
        return $qb;
    }


    /**
     * @param Person $a
     * @param Person $b
     * @return Team
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTeamCouple(Person $a,Person $b) : ?Team
    {
        $qb=$this->queryBuilderBase();
        $qb->innerJoin('team.person','pB');
        $qb->where('pA=:A')
            ->andWhere('pB=:B');
        $query=$qb->getQuery();
        $query->setParameters([':A'=>$a,':B'=>$b]);
        $result = $query->getOneOrNullResult();
        return $result;
    }

    /**
     * @param Person $p
     * @return Team|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getTeamSolo(Person $p) : ?Team
    {
        $pDescribe = $p->getDescribe();
        $type = $pDescribe['type'];
        $status = $pDescribe['status'];
        /** @var QueryBuilder $qb */
        $qb = $this->queryBuilderBase();
        $qb->where('pA=:person')
            ->andWhere("JSON_EXTRACT(team.`describe`,'$.type)=:type)")
            ->andWhere("JSON_EXTRACT(team.`describe`,'$.status'=:status");
        /** @var Query $query */
        $query = $qb->getQuery();
        $query->setParameters([':type'=>$type,':status'=>$status,':person'=>$p]);
        $result=$query->getOneOrNullResult();
        return $result;
    }
}
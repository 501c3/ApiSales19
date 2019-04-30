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
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\QueryBuilder;

class TeamRepository extends ServiceEntityRepository
{

    const SQL = <<<'EOD'
# noinspection SqlNoDataSourceInspection
SELECT t.id as t_id, c.`describe` as c_describe FROM team t
INNER JOIN team_class c ON t.team_class_id=c.id
INNER JOIN person_has_team pt ON pt.team_id=t.id
INNER JOIN person p ON pt.person_id=p.id
WHERE JSON_EXTRACT(p.`describe`, "$.designate")='A'
  AND JSON_EXTRACT(c.`describe`, "$.sex") = JSON_EXTRACT(p.`describe`, "$.sex") 
  AND JSON_EXTRACT(c.`describe`, "$.proficiency")=JSON_EXTRACT(p.`describe`,"$.proficiency")
  AND JSON_EXTRACT(c.`describe`, "$.status")=JSON_EXTRACT(p.`describe`,"$.status")
  AND JSON_EXTRACT(c.`describe`, "$.type")=JSON_EXTRACT(p.`describe`,"$.type")
  AND p.id = ?
EOD;


    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }


    private function queryBuilderBase() : QueryBuilder
    {
        $qb=$this->createQueryBuilder('team');
        $qb->select('team','class')
            ->innerJoin('team.teamClass','class')
            ->innerJoin('team.person','pA');
        return $qb;
    }


    /**
     * @param Person $a
     * @param Person $b
     * @return Team
     * @throws NonUniqueResultException
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
     * @throws NonUniqueResultException
     */
    public function getTeamSolo(Person $p) : ?Team
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata('App\Entity\Model\Team','t');
        $rsm->addJoinedEntityFromClassMetadata('App\Entity\Model\TeamClass','c', 't', 'team_class',
            ['id' => 'team_class_id']);
        $rsm->addJoinedEntityFromClassMetadata('App\Entity\Model\Person','p', 't','person_has_team',
            ['id'=>'person_Id']);
        $query = $this->_em->createNativeQuery(self::SQL,$rsm);
        $query->setParameter(1,$p->getId());
        $result = $query->getOneOrNullResult();
        return $result;
    }
}
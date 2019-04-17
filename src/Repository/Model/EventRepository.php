<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/10/18
 * Time: 6:26 PM
 */

namespace App\Repository\Model;

use App\Entity\Model\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\Model\Event;
use Doctrine\ORM\QueryBuilder;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    private function queryBuilder()
    {
        $qb=$this->createQueryBuilder('event');
        $qb->select('model','event','teamClass')
            ->innerJoin('team.teamClass','teamClass')
            ->innerJoin('event.model','model');
    }


    public function getEvents(Team $team,array $models,array $styles) {
        $class = $team->getTeamClass();
        /** @var QueryBuilder $qb */
        $qb = $this->queryBuilder();
        $qb->where('model IN [:models]')
            ->andWhere('teamClass=:class')
            ->andWhere("JSON_EXTRACT(event.`describe`,'$.style') IN (:styles)");
        $query = $qb->getQuery();
        $query->setParameters([':models'=>$models,':class'=>$class,':styles'=>$styles]);

    }

    function fetchQuickSearch()
    {
        $arr = [];
        $results = $this->findAll();
        /** @var Event $result */
        foreach($results as $result) {
            $describe=$result->getDescribe();
            $status = $describe['status'];
            $proficiency = $describe['proficiency'];
            $age = $describe['age'];
            $style = $describe['style'];
            $model = $result->getModel()->getName();
            $dances = $describe['dances'];
            if(!isset($arr[$model])) {
                $arr[$model]=[];
            }
            if(!isset($arr[$model][$style])) {
                $arr[$model][$style]=[];
            }
            foreach(array_keys($dances) as $substyle) {
                if(!isset($arr[$model][$style][$substyle])) {
                    $arr[$model][$style][$substyle]=[];
                }
                if(!isset($arr[$model][$style][$substyle][$proficiency])) {
                    $arr[$model][$style][$substyle][$proficiency]=[];
                }
                $arr[$model][$style][$substyle][$proficiency][$age]=new ArrayCollection();
                /** @var ArrayCollection $collection */
                $collection = $arr[$model][$style][$substyle][$proficiency][$age];
                $collection->set($result->getId(),$result);
            }
        }
        return $arr;
    }

}
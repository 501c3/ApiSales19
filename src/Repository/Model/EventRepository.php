<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/10/18
 * Time: 6:26 PM
 */

namespace App\Repository\Model;

use App\Entity\Model\Model;
use App\Entity\Model\TeamClass;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\Model\Event;
use Doctrine\ORM\Query\ResultSetMappingBuilder;


class EventRepository extends ServiceEntityRepository
{
    const AMERICAN = 'American',
          INTERNATIONAL = 'International',
          SMOOTH = 'Smooth',
          RHYTHM = 'Rhythm',
          STANDARD= 'Standard',
          LATIN='Latin',
          FUN_EVENTS='Fun Events';

    const
        SQL =<<< 'EOD'
# noinspection SqlNoDataSourceInspection
SELECT  e.id as e_id, 
        e.`describe` as e_describe,
        m.id as m_id, 
        m.name as m_name 
FROM model.`event` e
INNER JOIN model.model m ON e.model_id=m.id
INNER JOIN model.event_has_team_class ec ON e.id=ec.event_id
INNER JOIN model.team_class c ON ec.team_class_id=c.id
WHERE c.id = ?
AND m.id = ?
AND e.`describe`->>"$.style" = ?
AND e.`describe`->>"$.dances" LIKE ?
EOD;

    const
        FIELD_CLASS_ID=1,
        FIELD_MODEL_ID=2,
        FIELD_STYLE=3,
        FIELD_SUBSTYLE=4;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }


    private function likeSubstyle(...$substyles)
    {
        switch(count($substyles)){
            case 1:
                return '{"'.$substyles[0].'": %}';
            case 2:
                return '{"'.$substyles[0].'": % , "'.$substyles[1].'": %}';
        }
    }


    public function fetch(TeamClass $class, Model $model, string $genre)
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $eventClass = Event::class;
        $rsm->addRootEntityFromClassMetadata(Event::class,'e');
        $rsm->addFieldResult('e','e_id','id');
        $rsm->addFieldResult('e','e_describe','describe');
        $rsm->addJoinedEntityResult(Model::class , 'm', 'e', 'model');
        $rsm->addFieldResult('m', 'm_id', 'id');
        $rsm->addFieldResult('m', 'm_name', 'name');
        $ss = $this->genreToStyle($genre);
        $likeSubstyle = count($ss['substyle'])>1?
            $this->likeSubstyle($ss['substyle'][0],$ss['substyle'][1]):
            $this->likeSubstyle($ss['substyle'][0]);
        $query = $this->_em->createNativeQuery(self::SQL,$rsm);
        $query->setParameters([
           self::FIELD_CLASS_ID=>$class->getId(),
           self::FIELD_MODEL_ID=>$model->getId(),
           self::FIELD_STYLE=>$ss['style'],
           self::FIELD_SUBSTYLE=>$likeSubstyle
        ]);
        $result = $query->getResult();
        return $result;
    }

    private function genreToStyle(string $genre)
    {
        switch($genre){
            case self::AMERICAN:
                return ['style'=>$genre,
                        'substyle'=>[self::RHYTHM,self::SMOOTH]];
            case self::INTERNATIONAL:
                return ['style'=>$genre,
                        'substyle'=>[self::LATIN,self::STANDARD]];
            case self::SMOOTH:
                return ['style'=>self::AMERICAN,
                        'substyle'=>[self::SMOOTH]];
            case self::RHYTHM:
                return ['style'=>self::AMERICAN,
                        'substyle'=>[self::RHYTHM]];
            case self::STANDARD:
                return ['style'=>self::INTERNATIONAL,
                        'substyle'=>[self::STANDARD]];
            case self::LATIN:
                return ['style'=>self::INTERNATIONAL,
                        'substyle'=>[self::LATIN]];
            case self::FUN_EVENTS:
                return ['style'=>$genre,
                        'substyle'=>[self::RHYTHM,self::SMOOTH]];
        }
    }


    function fetchQuickSearch()
    {
        $arr = [];
        $results = $this->findAll();
        /** @var Event $result */
        foreach($results as $result) {
            $describe=$result->getDescribe();
            // TODO: eliminate following line
            //$status = $describe['status'];
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
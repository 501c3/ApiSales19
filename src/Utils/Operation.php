<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/26/19
 * Time: 9:09 PM
 */

namespace App\Utils;

use App\AppException;
use App\Entity\Model\Event;
use App\Entity\Model\Model;
use App\Entity\Model\Person;
use App\Entity\Model\Team;
use App\Entity\Model\Value;
use App\Entity\TeamEvents;
use App\Repository\Model\EventRepository;
use App\Repository\Model\ModelRepository;
use App\Repository\Model\PersonRepository;
use App\Repository\Model\TeamRepository;
use App\Repository\Model\ValueRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class Operation {

    /** @var ModelRepository */
    private $modelRepository;

    /** @var PersonRepository */
    private $personRepository;

    /** @var TeamRepository  */
    private $teamRepository;

    /** @var EventRepository */
    private $eventRepository;

    /** @var ValueRepository */
    private $valueRepository;

    /** @var array */
    private $valueQuickSearch;

    public function __construct(RegistryInterface $registry)
    {
        $modelManager = $registry->getManager('model');
        $this->modelRepository = $modelManager->getRepository(Model::class);
        $this->personRepository= $modelManager->getRepository(Person::class);
        $this->teamRepository  = $modelManager->getRepository(Team::class);
        $this->eventRepository = $modelManager->getRepository(Event::class);
        $this->valueRepository = $modelManager->getRepository(Value::class);
        $this->valueQuickSearch = $this->valueRepository->fetchQuickCheck();
    }


    /**
     * @param array $infoList
     * @return TeamEvents|void
     * @throws AppException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function teamEvents(array $infoList) : TeamEvents
    {
        switch(count($infoList))
        {
            case 1:
                /** @noinspection PhpInconsistentReturnPointsInspection */
                return $this->soloTeamEvents($infoList[0]);
            case 2:
                /** @noinspection PhpInconsistentReturnPointsInspection */
                return $this->coupleTeamEvents($infoList[0],$infoList[1]);
            default:
                throw new AppException("Only 1 or 2 competitors per team can be handled.");
        }
    }

    /**
     * @param $personInfo
     * @return TeamEvents
     * @throws AppException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function soloTeamEvents($personInfo): TeamEvents
    {
        $teamEvents = new TeamEvents();
        $teamEvents->addPerson($personInfo);
        $stylePerson = $this->mapStylePerson($personInfo);
        $commonModels = $personInfo['model'];
        $this->checkModels($commonModels);
        foreach($commonModels as $modelName) {
            /** @var Model $model */
            $model = $this->modelRepository->findOneBy(['name'=>$modelName]);
            $teamEvents->addModel($model);
        }
        $commonStyles = array_keys($stylePerson);
        foreach($commonStyles as $styleName) {
            /** @var Person $metaPerson */
            $metaPerson = $stylePerson[$styleName];
            /** @var Team $metaTeam */
            $metaTeam = $this->teamRepository->getTeamSolo($metaPerson);
            $teamClass = $metaTeam->getTeamClass();
            foreach($teamEvents->allModels() as $model) {
                $events = $this->eventRepository->fetch($teamClass,$model,$styleName);
                $teamEvents->setModelStyleEvents($model,$styleName,$events);
            }
            $teamEvents->setStyleTeam($styleName, $metaTeam);
        }
        return $teamEvents;
    }


    public function getEvents(array $ids)
    {
        $collection = [];
        foreach($ids as $id) {
            /** @var Event $event */
            $event=$this->eventRepository->find($id);
            $describe = $event->getDescribe();
            $describe['event_id']=$event->getId();
            $describe['model_id']=$event->getModel()->getId();
            $describe['selected']=false;
            $collection[]=$describe;
        }
        return $collection;
    }

    /**
     * @param array $personInfoOne
     * @param array $personInfoTwo
     * @return TeamEvents
     * @throws AppException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function coupleTeamEvents(array $personInfoOne, array $personInfoTwo): TeamEvents
    {
        list($personInfoLeft,$personInfoRight) = $this->setMaleFirst($personInfoOne,$personInfoTwo);
        $teamEvents = new TeamEvents();
        $teamEvents->addPerson($personInfoLeft)
                    ->addPerson($personInfoRight);
        /** @var Person $stylePersonA */
        $stylePersonA = $this->mapStyleMetaPerson($personInfoLeft, 'A');
        /** @var Person $stylePersonB */
        $stylePersonB = $this->mapStyleMetaPerson($personInfoRight, 'B');
        $modelsLeft  = $personInfoLeft['model'];
        $this->checkModels($modelsLeft);
        $modelsRight = $personInfoRight['model'];
        $this->checkModels($modelsRight);
        $commonModels = array_intersect($modelsLeft,$modelsRight);
        foreach($commonModels as $modelName) {
            /** @var Model $model */
            $model = $this->modelRepository->findOneBy(['name'=>$modelName]);
            $teamEvents->addModel($model);
        }
        $stylesA = array_keys($personInfoLeft['proficiency']);
        $stylesB = array_keys($personInfoRight['proficiency']);
        $commonStyles = array_intersect($stylesA,$stylesB);
        foreach($commonStyles as $styleName) {
            $metaPersonA = $stylePersonA[$styleName];
            $metaPersonB = $stylePersonB[$styleName];
            $metaTeam=$this->teamRepository->getTeamCouple($metaPersonA,$metaPersonB);
            $teamEvents->setStyleTeam($styleName,$metaTeam);
            $teamClass = $metaTeam->getTeamClass();
            /** @var Model $model */
            foreach($teamEvents->allModels() as $model){
                /** @var array $events */
                $events = $this->eventRepository->fetch($teamClass,$model,$styleName);
                if(count($events)) {
                    $teamEvents->setModelStyleEvents($model, $styleName, $events);
                }
            }

        }
        return $teamEvents;
    }

    private function setMaleFirst($personInfoLeft,$personInfoRight)
    {
        $personA=$personInfoLeft['sex']=='M'?$personInfoLeft:$personInfoRight['sex']=='M'?$personInfoRight:$personInfoLeft;
        $personB=$personInfoRight['sex']=='F'?$personInfoRight:$personInfoLeft['sex']=='F'?$personInfoLeft:$personInfoRight;
        return [$personA,$personB];
    }


    /**
     * @param $personInfo
     * @param string $designate
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function mapStyleMetaPerson($personInfo, string $designate): array
    {
        $metaStylePerson = [];
        $sex = $personInfo['sex']=='M'?'Male':'Female';
        foreach($personInfo['proficiency'] as $style=>$proficiency) {
           if(!isset($metaStylePerson[$style])) {
               $metaStylePerson[$style]
                   =['designate'=>$designate,
                     'proficiency'=>$proficiency,
                     'sex'=>$sex,
                     'status'=>$personInfo['status'],
                     'type'=>$personInfo['type'],
                     'years'=>$personInfo['years']];
           }
        }

        $metaStylePersonModel = [];
        foreach($metaStylePerson as $style=>$arr) {
            $person=$this->personRepository->fetch($arr);
            $metaStylePersonModel[$style]=$person;
        }
        return $metaStylePersonModel;
    }

    /**
     * @param array $modelNames
     * @throws AppException
     */
    private function checkModels(array $modelNames)
    {
        foreach($modelNames as $name) {
            if(!$this->modelRepository->modelExists($name)){
                throw new AppException($name." does note exist");
            }
        }
    }

}
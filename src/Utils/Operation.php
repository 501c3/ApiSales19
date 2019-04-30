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
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function teamEvents(array $infoList) : TeamEvents
    {
        switch(count($infoList))
        {
            case 1:
                $teamEvents = $this->soloTeamEvents($infoList[0]);
                return $teamEvents;
            case 2:
                $teamEvents = $this->coupleTeamEvents($infoList[0],$infoList[1]);
                return $teamEvents;
            default:
                throw new AppException("Invalid parameter passed to $infoList");
        }
    }

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

    /**
     * @param $personInfoLeft
     * @param $personInfoRight
     * @return TeamEvents
     * @throws AppException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function coupleTeamEvents($personInfoLeft, $personInfoRight): TeamEvents
    {

        $teamEvents = new TeamEvents();
        $teamEvents->addPerson($personInfoLeft)
                    ->addPerson($personInfoRight);
        $stylePersonA = $this->mapStyleMetaPerson($personInfoLeft, 'A');
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

        $commonStyles = array_intersect(array_keys($stylePersonA), array_keys($stylePersonB));
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

    /**
     * @param $personInfo
     * @param string $designate
     * @return array
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function mapStyleMetaPerson($personInfo, string $designate): array
    {
        $metaStylePerson = [];
        $sex = $personInfo['sex']='M'?'Male':'Female';
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
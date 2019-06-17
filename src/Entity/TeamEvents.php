<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/27/19
 * Time: 8:32 PM
 */

namespace App\Entity;


use App\Entity\Model\Event;
use App\Entity\Model\Model;
use App\Entity\Model\Team;

class TeamEvents
{
    /** @var array */
    private $persons=[];

    /** @var array */
    private $styleTeam=[];

    /** @var array */
    private $modelStyleEvents=[];

    /** @var array  */
    private $models=[];

    /**
     * @return array
     */
    public function getPersons(): array
    {
        return $this->persons;
    }


    public function addPerson(array $person)
    {
        $this->persons[]=$person;
        return $this;
    }

    /**
     * @param array $persons
     * @return TeamEvents
     */
    public function setPersons(array $persons): TeamEvents
    {
        $this->persons = $persons;
        return $this;
    }

    /**
     * @param string $Style
     * @return Team
     */
    public function getStyleTeam(string $Style): Team
    {
        return $this->styleTeam[$Style];
    }

    public function allStyleTeam(): array
    {
        return $this->styleTeam;
    }

    /**
     * @param string $Style
     * @param Team $team
     * @return TeamEvents
     */
    public function setStyleTeam(string $Style, Team $team): TeamEvents
    {

        $this->styleTeam[$Style]=$team;
        return $this;
    }

    /**
     * @param string $model
     * @param string $style
     * @return array
     */
    public function getModelStyleEvents(string $model, string $style): array
    {
        return $this->modelStyleEvents[$model][$style];
    }

    public function allModelStyleEvents()
    {
        return $this->modelStyleEvents;
    }

    /**
     * @param Model $model
     * @param string $style
     * @param array $events
     * @return TeamEvents
     */
    public function setModelStyleEvents(Model $model, string $style, array $events): TeamEvents
    {
        $modelName = $model->getName();
        if(!isset($this->modelStyleEvents[$modelName])) {
            $this->modelStyleEvents[$modelName]=[];
        }
        $this->modelStyleEvents[$modelName][$style]=$events ;
        return $this;
    }

    private function personsToArray()
    {
        $data=[];
        foreach($this->persons as $person) {
            $data[]=[
                'name'=>$person['name'],
                'sex'=>$person['sex'],
                'years'=>$person['years'],
                'form_id'=>$person['id']];
        }
        return $data;
    }

    private function selectionsToArray()
    {
        $arr = [];
        foreach($this->modelStyleEvents as $model=>$styleEvents) {
            /**
             * @var string $style
             * @var array $events
             */
            $arr[$model]=[];
            foreach($styleEvents as $style=>$events) {
                /** @var Event $event */
                $arr[$model][$style]=[];
                foreach($events as $event) {
                    $describe = $event->getDescribe();
                    $describe['event_id']=$event->getId();
                    $describe['model_id']=$event->getModel()->getId();
                    $describe['selected']=false;
                    $arr[$model][$style][]=$describe;
                }
            }
        }
        return $arr;
    }


    public function toArray()
    {
       $data = ['team'=>$this->personsToArray(),
                'selections'=> $this->selectionsToArray()];
      return $data;
    }

    /**
     * @param Model $model
     * @return $this
     */
    public function addModel(Model $model)
    {
        $this->models[$model->getName()]=$model;
        return $this;
    }


    /**
     * @return array
     */
    public function allModels()
    {
        return $this->models;
    }

}
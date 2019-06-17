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

use App\AppException;
use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\ORMException;

class FormRepository extends ServiceEntityRepository
{
  const PARTICIPANT = 'participant',
        TEAM='team',
        ENTRIES='entries';

  public function __construct(ManagerRegistry $registry)
  {
      parent::__construct($registry, Form::class);
  }


    /**
     * @param array $content
     * @param Tag $tag
     * @param Workarea $workarea
     * @return Form
     * @throws \Doctrine\ORM\ORMException
     * @throws \Exception
     */
  public function post(array $content, Tag $tag, Workarea $workarea) : Form
  {
      $this->checkPriorTeams($content,$tag,$workarea);
      $note = $this->note($content,$tag);
      $form = new Form();
      $form->setContent($content)
          ->setWorkarea($workarea)
          ->setTag($tag)
          ->setNote($note)
          ->setUpdatedAt(new \DateTime('now'));
      $this->_em->persist($form);
      $this->_em->flush();
      $this->addToPriorForms($form,$tag);
      return $form;
  }

    /**
     * @param array $content
     * @param Tag $tag
     * @param Workarea $workarea
     * @throws AppException
     */
  private function checkPriorTeams(array $content, Tag $tag, Workarea $workarea)
  {
      switch($tag->getName())
      {
          case 'participant':
              return;
          case 'team':
              $newMemberIds = [];
              foreach($content['team'] as $newMembers){
                $newMemberIds[]=$newMembers['form_id'];
              }
              $forms = $this->findBy(['tag'=>$tag, 'workarea'=>$workarea]);
              /** @var Form $form */
              foreach($forms as $form){
                  $_content = $form->getContent();
                  $priorTeam = $_content['team'];
                  $priorMemberIds=[];
                  foreach($priorTeam as $member){
                      $priorMemberIds[]=$member['form_id'];
                  }
                  $result = array_diff($newMemberIds,$priorMemberIds);
                  if(count($result)===0) {
                      $e =  new AppException("Attempt to post redundant team.",5006);
                      $e->priorId=$form->getId();
                      throw $e;
                  }
              }
      }
  }


    /**
     * @param array $ids
     * @param Workarea $workarea
     * @return array
     * @throws ORMException
     * @throws AppException
     */
  public function delete(array $ids, Workarea $workarea): array
  {
      $disposition = [];
      foreach($ids as $id) {
          $disposition[]=$this->deleteSingle($id,$workarea);
      }
      $this->_em->flush();
      return array_merge(...$disposition);
  }


    /**
     * @param $id
     * @param Workarea $workarea
     * @return array
     * @throws AppException
     * @throws ORMException
     */
  private function deleteSingle($id,Workarea $workarea): array
  {

      $form=$this->findOneBy(['id'=>$id, 'workarea'=>$workarea]);
      if(is_null($form)){
          throw new AppException("No form found corresponding to ID=$id", AppException::APP_NO_FORM);
      }
      $id= $form->getId();
      $content = $form->getContent();
      $note = $form->getNote();
      $tag = $form->getTag()->getName();
      $this->_em->remove($form);
      $reply = [];
      switch($tag) {
          case 'participant':
              if(isset($content['team-id-events'])){
                  $teamList  = $content['team-id-events'];
                  foreach($teamList as $teamId) {
                      $teamForm=$this->find($teamId);
                      $teamName = $teamForm->getNote();
                      try{
                        $this->_em->remove($teamForm);
                        $reply[]=['id'=>$teamId, 'status'=>'success','tag'=>'team','message'=>"Removed: $teamName"];
                      } catch (ORMException $e) {
                        $reply[]=['id'=>$teamId, 'status'=>'failure','tag'=>'team','message'=>"Removed: $teamName"];
                      }
                  }
              }
              if(isset($content['team-id-entries'])) {
                  $teamList  = $content['team-id-entries'];
                  foreach($teamList as $teamId) {
                      $teamForm=$this->find($teamId);
                      $teamName = $teamForm->getNote();
                      try{
                          $this->_em->remove($teamForm);
                          $reply[]=['id'=>$teamId, 'status'=>'success','tag'=>'entries','message'=>"Removed: $teamName"];
                      } catch (ORMException $e) {
                          $reply[]=['id'=>$teamId, 'status'=>'failure','tag'=>'entries','message'=>"Removed: $teamName"];
                      }
                  }
              }
              $reply[]=['id'=>$id, 'status'=>'success','tag'=>'participant','message'=>"Removed: $note"];
              break;
          case 'team':
              $reply[]=['id'=>$id, 'status'=>'success','tag'=>'team', 'message'=>"Removed: $note"];
      }
      return $reply;
  }

    /**
     * @param Form $form
     * @param Tag $tag
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
  private function addToPriorForms(Form $form,Tag $tag)
  {
      switch($tag->getName()){
          case self::PARTICIPANT:
              return;
          case self::TEAM:
              $content = $form->getContent();
              foreach($content['team'] as $member) {
                  /** @var Form $personForm */
                  $personForm=$this->find($member['form_id']);
                  $personContent = $personForm->getContent();
                  $teamMembership = isset($personContent['team-id-events'])?$personContent['team-id-events']:[];
                  if(!in_array($form->getId(),$teamMembership)) {
                      $personContent['team-id-events'][]=$form->getId();
                  }
                  $personForm->setContent($personContent);
              }
              $this->_em->flush();
              break;
          case self::ENTRIES:
              $content = $form->getContent();
              foreach($content['team'] as $member) {
                  /** @var Form $personForm */
                  $personForm=$this->find($member['form_id']);
                  $personContent = $personForm->getContent();
                  $teamMembership = isset($personContent['team-id-entries'])?$personContent['team-id-entries']:[];
                  if(!in_array($form->getId(),$teamMembership)) {
                      $personContent['team-id-entries'][]=$form->getId();
                  }
                  $personForm->setContent($personContent);
              }
              $this->_em->flush();
              break;


      }
  }

    /**
     * @param array $content
     * @param Tag $tag
     * @return Form
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
  public function put(array $content, Tag $tag): Form
  {
      /** @var Form $form */
      $form = $this->find($content['id']);
      unset($content['id']);
      unset($content['tag']);
      $form->setContent($content)
            ->setNote($this->note($content, $tag));
      $this->_em->flush();
      return $form;
  }

  private function note(array $content, Tag $tag): ?string
  {
      switch($tag->getName()){
          case 'participant':
              $name = $content['name']['last'].', '.$content['name']['first'];
              return $name;
          case 'team':
          case 'entries':
              $name = count($content['team'])>1?$content['team'][0]['name']['last'].' & '.$content['team'][1]['name']['last']:
                  $content['team'][0]['last'];
              return $name;
          case 'xtras':
              $name = $content['buyer'];
              return $name;
      }
      return null;
  }

    /**
     * @param Tag $tag
     * @param Workarea $workarea
     * @return array
     */
  public function fetchArrayList(Tag $tag, Workarea $workarea)
  {
      $formList = $this->findBy(['tag'=>$tag, 'workarea'=>$workarea]);
      /** @var Form $form */
      $list = [];
      foreach($formList as $form) {
          $content = $form->getContent();
          $tagName = $tag->getName();
          $function= 'get'.ucfirst($tagName);
          $element = $this->$function($form->getId(), $content, $tagName);
          $list[]=$element;
      }
      return $list;
  }

    /**
     * @param $id
     * @param $content
     * @param $tagName
     * @return array
     */
  protected function getParticipant($id,$content,$tagName)
  {
      return ['id'=>$id,
              'name'=> ['first'=>$content['name']['first'], 'last'=>$content['name']['last']],
              'tag'=>$tagName];
  }

    /**
     * @param $id
     * @param $content
     * @param $tagName
     * @return array
     * @throws AppException
     */
  protected function getTeam($id,$content,$tagName)
  {

      switch(count($content['team'])){
          case 1:
              $name = $content['team'][0]['name']['last'];
              return ['id'=>$id,'name'=>$name,'tag'=>$tagName];
          case 2:
              $name = $content['team'][0]['name']['last'].' & '.$content['team'][1]['name']['last'];
              return ['id'=>$id,'name'=>$name,'tag'=>$tagName];
          default:
              throw new AppException('Too many team members');
      }

  }


  protected function getEntries($id,$content,$tagName)
  {
      $content['id']=$id;
      $content['tag']=$tagName;
      return $content;
  }





  public function fetchInfoList(Tag $tag, Workarea $workarea)
  {
      $list = $this->findBy(['tag'=>$tag,'workarea'=>$workarea]);
      $infoList = [];
      /** @var Form $form */
      foreach($list as $form) {
          $id = $form->getId();
          $content = $form->getContent();
          $content['id']=$id;
          $infoList[]=$content;
      }
      return $infoList;
  }

  public function fetchItem(Tag $tag, Workarea $workarea)
  {
      $form = $this->findOneBy(['tag'=>$tag,'workarea'=>$workarea]);
      $content = $form->getContent();
      if($tag->getName()=='xtras') {
          unset($content['buyer']);
      }
      return $content;
  }



  public function fetchInfo(int $id, Workarea $workarea)
  {
      /** @var Form $form */
      $form = $this->findOneBy(['id'=>$id,'workarea'=>$workarea]);
      if($form) {
          $content = $form->getContent();
          $content['id']=$form->getId();
          $content['tag']=$form->getTag()->getName();
          return $content;
      } else {
          return null;
      }
  }

    /**
     * @throws ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
  public function flush()
  {
      $this->_em->flush();
  }

}
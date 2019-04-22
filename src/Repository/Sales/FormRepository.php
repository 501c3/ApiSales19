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

use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class FormRepository extends ServiceEntityRepository
{
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
      $note = $this->note($content,$tag);

      $form = new Form();
      $form->setContent($content)
          ->setWorkarea($workarea)
          ->setTag($tag)
          ->setNote($note)
          ->setUpdatedAt(new \DateTime('now'));
      $this->_em->persist($form);
      $this->_em->flush();
      return $form;
  }

  private function note(array $content, Tag $tag): ?string
  {
      switch($tag->getName()){
          case 'participant':
              $name = $content['name']['last'].', '.$content['name']['first'];
              return $name;
      }
      return null;
  }

  public function fetchArrayList(Tag $tag, Workarea $workarea)
  {
      $formList = $this->findBy(['tag'=>$tag, 'workarea'=>$workarea]);
      /** @var Form $form */
      $list = [];
      foreach($formList as $form) {
          $content = $form->getContent();
          $list[]=['id'=>$form->getId(),
                   'name'=> ['first'=>$content['name']['first'], 'last'=>$content['name']['last']],
                   'tag'=>$tag->getName()];
      }
      return $list;
  }

  public function fetchInfo(int $id, Workarea $workarea)
  {
      $form = $this->findOneBy(['id'=>$id, 'workarea'=>$workarea]);
      if($form) {
          $content = $form->getContent();
          $content['id']=$form->getId();
          return $content;
      } else {
          return null;
      }
  }

}
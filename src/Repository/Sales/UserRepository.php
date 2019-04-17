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

use App\Entity\Sales\User;
use App\AppException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{

  public function __construct(ManagerRegistry $registry)
  {
      parent::__construct($registry, User::class);
  }

    /**
     * @param array $content
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
  public function put(array $content)
  {
      /** @var User $old */
      $old = $this->find($content['id']);
      unset($content['id']);
      $name = $this->nameField($content);
      $old->setName($name)
          ->setUsername($content['email'])
          ->setInfo($content);
      $this->getEntityManager()->flush();
  }


    /**
     * @param array $content
     * @return User|null
     * @throws AppException
     * @throws \Exception
     */
  public function post(array $content)
  {
      /** @var EntityManagerInterface $em */

      $name = $this->nameField($content);
      $email = $content['email'];
      /** @var User $user */
      $user = $this->findOneBy(['username'=>$email]);
      if($user){
          $code = AppException::APP_REDUNDANT_USER;
          $message = AppException::statusText[$code];
          throw new AppException($message, $code);
      }
      $user = new User();
      $user->setName($name)
            ->setUsername($email)
            ->setInfo($content)
            ->setCreatedAt(new \DateTime('now'));
      $this->_em->persist($user);
      $this->_em->flush();
      return $user;
  }

  private function nameField(array $content)
  {
      $name = $content['name']['last'].', '.$content['name']['first'];
      return $name;
  }

    /**
     * Loads the user for the given username.
     *
     * This method must return null if the user is not found.
     *
     * @param string $username The username
     *
     * @return UserInterface|null
     */
    public function loadUserByUsername($username)
    {
        /** @var UserInterface|null $result */
        $result=$this->findOneBy(['username'=>$username]);
        return $result;
    }
}
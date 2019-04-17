<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/11/19
 * Time: 3:44 PM
 */

namespace App\Repository\Sales;


use App\Entity\Sales\Session;
use App\Entity\Sales\Sessions;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class SessionRepository  extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

}
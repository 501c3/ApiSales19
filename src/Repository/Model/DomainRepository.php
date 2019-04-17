<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/10/18
 * Time: 6:25 PM
 */

namespace App\Repository\Model;

use App\Entity\Model\Domain;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;


class DomainRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Domain::class);
    }

    /**
     * @param int $id
     * @return Domain|null
     */
    public function read(int $id) : ?Domain
    {
        /** @var Domain $result */
        $result = $this->find($id);
        return $result;
    }

    /**
     * @return array|null
     */
    public function readMulti() : ?array
    {

        $result = $this->findAll();
        return count($result)>0?$result:null;
    }

    /**
     * @param Domain $new
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(Domain $new)
    {
        /** @var Domain $old */
        $old = $this->find($new->getId());
        $old->setName($new->getName());
        $em = $this->getEntityManager();
        $em->persist($old);
        $em->flush($old);
    }

    /**
     * @param int $id
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function delete(int $id)
    {
        /** @var Domain $domain */
        $domain=$this->find($id);
        /** @var EntityManagerInterface $domain */
        $em=$this->getEntityManager();
        $em->remove($domain);
        $em->flush();
    }

    /**
     * @param Domain $domain
     */
    public function remove(Domain $domain)
    {
        /** @var EntityManagerInterface $em */
        $em=$this->getEntityManager();
        $em->remove($domain);
        $em->flush();
    }

    public function fetchQuickSearch()
    {
        $lookup = [];
        $results = $this->findAll();
        /** @var Domain $domain */
        foreach($results as $domain) {
            $name = $domain->getName();
            $lookup[$name]=$domain;
        }
        return $lookup;
    }
}
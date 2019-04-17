<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/10/18
 * Time: 6:28 PM
 */

namespace App\Repository\Model;



use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\Model\TeamClass;
use Doctrine\ORM\EntityManagerInterface;

class TeamClassRepository extends ServiceEntityRepository
{
    /** @var PersonRepository */

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamClass::class);
    }

    public function create(array $describe) : TeamClass
    {
        $teamClass = new TeamClass();
        $teamClass->setDescribe($describe);
        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        $em->persist($teamClass);
        $em->flush();
        return $teamClass;
    }

    public function read(int $id) : ?TeamClass
    {
        /** @var TeamClass $teamClass */
        $teamClass = $this->find($id);
        return $teamClass;
    }

    public function update(TeamClass $new)
    {
        /** @var TeamClass $old */
        $old = $this->find($new->getId());
        $old->setDescribe($new->getDescribe());

        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        $em->persist($old);
        $em->flush();
    }

    public function delete(int $id)
    {
        /** @var TeamClass $teamClass */
        $teamClass = $this->find($id);
        $this->remove($teamClass);
        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        $em->persist($teamClass);
        $em->flush();
    }

    public function remove(TeamClass $teamClass)
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getEntityManager();
        $em->remove($teamClass);
    }

    public function fetchQuickSearch()
    {
        $results = $this->findAll();

        $lookup = [];
        /** @var TeamClass $teamClass */
        foreach($results as $teamClass) {
            $data=$teamClass->getDescribe();
            $type = $data['type'];
            $status = $data['status'];
            $sex = $data['sex'];
            $age = $data['age'];
            $proficiency = $data['proficiency'];
            if(!isset($lookup[$type])) {
                $lookup[$type] = [];
            }
            if(!isset($lookup[$type][$status])) {
                $lookup[$type][$status]=[];
            }
            if(!isset($lookup[$type][$status][$sex])) {
                $lookup[$type][$status][$sex]=[];
            }
            if(!isset($lookup[$type][$status][$sex][$age])) {
                $lookup[$type][$status][$sex][$age]=[];
            }
            $lookup[$type][$status][$sex][$age][$proficiency] = $teamClass;
        }
        return $lookup;
    }
}
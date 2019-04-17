<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/10/18
 * Time: 6:26 PM
 */

namespace App\Repository\Model;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\Model\Person;

class PersonRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * @param array $person
     * @return Person|null
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function fetch(array $person) : ?Person
    {
        $type = $person['type'];
        $status = $person['status'];
        $sex = $person['sex'];
        $proficiency = $person['proficiency'];
        $years = $person['years'];
        $designate = $person['designate'];
        $qb = $this->createQueryBuilder('person');
        $qb->select('p')
            ->from('person','p')
            ->where("JSON_EXTRACT(person.`describe`,'$.type')=:type")
            ->andWhere("JSON_EXTRACT(p.`describe`,'$.status')=:status")
            ->andWhere("JSON_EXTRACT(p.`describe`,'$.sex')=:sex")
            ->andWhere("JSON_EXTRACT(p.`describe`,'$.proficiency'=:proficiency")
            ->andWhere("JSON_EXTRACT(p.`describe`,'$.years'=:years")
            ->andWhere("JSON_EXTRACT(p.`describe`,'$.designate'=:designate");
        $query = $qb->getQuery();
        $query->setParameters([':type'=>$type,
                               ':status'=>$status,
                               ':sex'=>$sex,
                               ':proficiency'=>$proficiency,
                               ':years'=>$years,
                               ':designate'=>$designate]);
        $result = $query->getSingleResult();
        return $result;
    }
}
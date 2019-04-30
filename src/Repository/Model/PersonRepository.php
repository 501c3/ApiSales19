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
use Doctrine\ORM\Query\ResultSetMappingBuilder;


class PersonRepository extends ServiceEntityRepository
{

    const SQL = <<<'EOD'
# noinspection SqlNoDataSourceInspection
SELECT id, years, `describe` FROM person
WHERE JSON_EXTRACT(`describe`, "$.designate")=?
  AND JSON_EXTRACT(`describe`, "$.proficiency")=?
  AND JSON_EXTRACT(`describe`, "$.sex")=?
  AND JSON_EXTRACT(`describe`, "$.status")=?
  AND JSON_EXTRACT(`describe`, "$.type")=?
  AND years=?
EOD;

// TODO: Return to DQL queries with JSON when there are working JSON functions.
//       Presently native queries are used.


    const   FIELD_DESIGNATE=1,
            FIELD_PROFICIENCY=2,
            FIELD_SEX=3,
            FIELD_STATUS=4,
            FIELD_TYPE=5,
            FIELD_YEARS=6;

    /**
     * PersonRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Person::class);
    }

    /**
     * @param array $person
     * @return Person
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function fetch(array $person) : ?Person
    {
        $rsm = new ResultSetMappingBuilder($this->_em);
        $rsm->addRootEntityFromClassMetadata(Person::class,'p');
        $rsm->addFieldResult('p','id','id');
        $rsm->addFieldResult('p','years','years');
        $rsm->addFieldResult('p','describe', 'describe');
        $query = $this->_em->createNativeQuery(self::SQL,$rsm);
        $query->setParameters([
            self::FIELD_DESIGNATE=>$person['designate'],
            self::FIELD_PROFICIENCY=>$person['proficiency'],
            self::FIELD_SEX=>$person['sex'],
            self::FIELD_STATUS=>$person['status'],
            self::FIELD_TYPE=>$person['type'],
            self::FIELD_YEARS=>$person['years']
            ]);
        return $query->getOneOrNullResult();
    }
}
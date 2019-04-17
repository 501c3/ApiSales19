<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 11/10/18
 * Time: 6:28 PM
 */

namespace App\Repository\Model;


use App\Entity\Model\Model;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use App\Entity\Model\Value;


class ValueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Value::class);
    }

    /**
     * @return array
     */
    public function fetchQuickSearch()
    {
        $arr = [];
        $qb = $this->createQueryBuilder('value');
        $qb->select('value','domain','model')
            ->innerJoin('value.model','model')
            ->innerJoin('value.domain','domain');
        $query=$qb->getQuery();
        $results = $query->getResult();
        /** @var Value $valueObj */
        foreach($results as $valueObj) {
            $domain = $valueObj->getDomain()->getName();
            $models=$valueObj->getModel()->toArray();
            $value = $valueObj->getName();
            /** @var Model $modelObj */
            foreach($models as $modelObj) {
                $model = $modelObj->getName();
                if(!isset($arr[$model])) {
                    $arr[$model]=[];
                }
                if(!isset($arr[$model][$domain])) {
                    $arr[$model][$domain]=[];
                }
                $arr[$model][$domain][$value]=$valueObj;

            }
        }
        return $arr;
    }
}
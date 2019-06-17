<?php

use App\Entity\Sales\Channel;
use App\Entity\Sales\Pricing;
use App\Kernel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/17/19
 * Time: 9:25 AM
 */

trait TraitDatabaseInit {
    /** @var KernelInterface */
   private static $channel = [
        'name' => 'georgia-dancesport',
        'heading'=> ['name'=>'Georgia DanceSport Competition & ISTD Medal Exams',
                     'venue'=>'Ballroom Impact',
                     'city'=>'Sandy Springs',
                     'state'=>'GA',
                     'date'=>['start'=>'2019-09-21','stop'=>'2019-09-21']],
        'logo'=>'dancers-icon.png'
   ];

   private static $inventorySetup = [
       '2019-09-01'=>[
            'participant'=>[
                'comp-dance-adult'=>18,
                'comp-dance-child'=>12,
                'exam-dance'=>30,
                'solo-dance-child'=>10],
            'extra'=>[
                'spectator-adult'=> 10,
                'spectator-child'=> 7,
                'program'=> 7]
            ],
       '2019-08-01'=>[
            'participant'=>[
                'comp-dance-adult'=>12,
                'comp-dance-child'=>7,
                'exam-dance'=>21,
                'solo-dance-child'=>5],
            'extra'=>[
                'spectator-adult'=> 10,
                'spectator-child'=> 7,
                'printed-program'=> 7.00
            ]
        ]
    ];

   private static $parameters=[
       "ISTD Medal Exams-2019"=>[
           "16"=>[
               "Pre Bronze","Bronze","Silver","Gold"
           ],
           "99"=>[
               "Pre Bronze","Bronze","Silver","Gold","Gold Star 1","Gold Star 2"
           ]

       ],
       "Georgia DanceSport Amateur-2019"=>[
           "11"=>[
               "Social",
               "Newcomer",
               "Bronze",
               "Silver"

           ],
           "14"=>[
               "Social",
               "Newcomer",
               "Bronze",
               "Silver",
               "Gold"
           ],
           "18"=>[
               "Social",
               "Newcomer",
               "Bronze",
               "Silver",
               "Gold",
               "Novice",
               "Pre Championship",
               "Championship"
           ],

       ],
       "Georgia DanceSport ProAm-2019"=>[
           "0"=>[
               "Rising Star",
               "Professional"
           ],
           "14"=>[
               "Newcomer",
               "Pre Bronze",
               "Intermediate Bronze",
               "Full Bronze",
               "Open Bronze",
               "Pre Silver",
               "Intermediate Silver",
               "Full Silver",
               "Open Silver",
           ],
           "99"=>[
               "Newcomer",
               "Pre Bronze",
               "Intermediate Bronze",
               "Full Bronze",
               "Open Bronze",
               "Pre Silver",
               "Intermediate Silver",
               "Full Silver",
               "Open Silver",
               "Pre Gold",
               "Intermediate Gold",
               "Full Gold",
               "Open Gold",
               "Gold Star 1",
               "Gold Star 2",
           ]
       ],
   ];


    /**
     * @throws Exception
     */
    protected static function setupChannelInventory()
    {
        $kernel = new Kernel('dev',true);
        $kernel->boot();
        $entityManager= $kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        self::clearInventory($entityManager);
        $channel = self::setChannel($entityManager);
        self::setInventory($entityManager, $channel);
    }


    /**
     * @param EntityManagerInterface $em
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function clearInventory(EntityManagerInterface $em)
    {
        $conn = $em->getConnection();
        $conn->exec('SET FOREIGN_KEY_CHECKS = 0');
        $conn->exec('TRUNCATE TABLE channel');
        $conn->exec('TRUNCATE TABLE pricing');
        $conn->exec('TRUNCATE TABLE user');
        $conn->exec("TRUNCATE TABLE workarea");
        $conn->exec("TRUNCATE TABLE tag");
        $conn->exec('TRUNCATE TABLE form');
        $conn->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * @param EntityManagerInterface $em
     * @return Channel
     * @throws Exception
     */
    protected static function setChannel(EntityManagerInterface $em) : Channel
    {
        $channelName = self::$channel['name'];
        /** @var Channel $channel */
        $channel = $em->getRepository(Channel::class)->findOneBy(['name'=>$channelName]);
        if(!$channel){
            $logo = file_get_contents(__DIR__.'/../../assets/dancers-icon.png');
            /** @var string $parameters */
            $channel = new Channel();
            $channel->setName(self::$channel['name'])
                    ->setHeading(self::$channel['heading'])
                    ->setLogo($logo)
                    ->setParameters(self::$parameters)
                    ->setOnlineAt(new \DateTime('2019-06-01'))
                    ->setOfflineAt(new \DateTime('2019-09-20'))
                    ->setCreatedAt(new \DateTime('now'));
            $em->persist($channel);
            $em->flush();
        }
        return $channel;
    }

    /**
     * @param EntityManagerInterface $em
     * @param Channel $channel
     * @throws Exception
     */
    protected static function setInventory(EntityManagerInterface $em, Channel $channel)
    {

        foreach(self::$inventorySetup as $dateString=>$inventory) {
            $pricing = new Pricing();
            $pricing->setChannel($channel)
                ->setInventory($inventory)
                ->setStartAt(new \DateTime($dateString));
            $em->persist($pricing);
        }
        $em->flush();
    }
}
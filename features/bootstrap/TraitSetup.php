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

trait TraitSetup {
    /** @var KernelInterface */
   private static $channelSetup = [
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
                'exam-dance'=>30],
            'extra'=>[
                'spectator-adult'=> 10,
                'spectator-child'=> 7,
                'program'=> 7]
            ],
       '2019-06-01'=>[
            'participant'=>[
                'comp-dance-adult'=>12,
                'comp-dance-child'=>7,
                'exam-dance'=>21],
            'extra'=>[
                'spectator-adult'=> 10,
                'spectator-child'=> 7,
                'printed-program'=> 7.00
            ]
        ]
    ];

   /** @var EntityManagerInterface */
    private static $em;

    private static $setup;

    /** @var Channel */
    private static $channel;

    /**
     * @throws Exception
     */
    protected static function setupChannelInventory()
    {
        self::$setup = new Kernel('dev',true);
        self::$setup->boot();
        self::$em = self::$setup->getContainer()->get('doctrine.orm.sales_entity_manager');
        self::clearInventory();
        self::$channel = self::setChannel(self::$em);
        self::setInventory(self::$em, self::$channel);
        self::$setup=null;
    }


    /**
     * @throws \Doctrine\DBAL\DBALException
     */
    private static function clearInventory()
    {
        $conn = self::$em->getConnection();
        $conn->exec('SET FOREIGN_KEY_CHECKS = 0');
        $conn->exec('TRUNCATE TABLE channel');
        $conn->exec('TRUNCATE TABLE pricing');
        $conn->exec('TRUNCATE TABLE user');
        $conn->exec("TRUNCATE TABLE workarea");
        $conn->exec('SET FOREIGN_KEY_CHECKS = 1');

    }

    /**
     * @param EntityManagerInterface $em
     * @return Channel
     * @throws Exception
     */
    protected static function setChannel(EntityManagerInterface $em):Channel
    {

        $channel = $em->getRepository(Channel::class)->findOneBy(['name'=>self::$channel['name']]);
        if(!$channel){
            $logo = file_get_contents(__DIR__.'/../../assets/dancers-icon.png');
            $channel = new Channel();
            $channel->setName(self::$channelSetup['name'])
                    ->setHeading(self::$channelSetup['heading'])
                    ->setLogo($logo)
                    ->setOnlineAt(new \DateTime('2019-06-01'))
                    ->setOfflineAt(new \DateTime('2019-09-20'))
                    ->setCreatedAt(new \DateTime('now'));
            $em->persist($channel);
            $em->flush();
        }
        /** @var Channel $channel */
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
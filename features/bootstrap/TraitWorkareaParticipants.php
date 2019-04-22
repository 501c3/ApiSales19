<?php

use App\AppException;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\User;
use App\Entity\Sales\Workarea;
use App\Kernel;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\UserRepository;
use App\Repository\Sales\WorkareaRepository;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/18/19
 * Time: 11:02 AM
 */

trait TraitWorkareaParticipants{


    private static $_user = [
        "name"=> ["title"=>"","first"=>"New","middle"=>"","last"=>"User","suffix"=>""],
        "email"=> "user@email.com",
        "phone"=> "(999) 999-9999",
        "mobile"=> "(678) 999-9999",
        "address"=> ["country"=> "USA",
                     "organization"=> "",
                     "street"=> "123 Street Address",
                     "city"=> "City",
                     "state"=> "GA",
                     "postal"=> "00000"]
    ];

    private static $participants = [
        [
        "name"=>["first"=>"Bronze", "last"=>"Baby"],
        "sex"=>"M",
        "typeA"=>"Student",
        "typeB"=>"Amateur",
        "years"=>5,
        "proficiency"=> [
            "Latin"=>"Pre Bronze",
            "Standard"=>"Pre Bronze",
            "Rhythm"=>"Pre Bronze",
            "Smooth"=>"Pre Bronze"
            ]
        ],
        [
            "name"=>["first"=>"Silver", "last"=>"Baby"],
            "sex"=>"F",
            "typeA"=>"Student",
            "typeB"=>"Amateur",
            "years"=>5,
            "proficiency"=> [
                "Latin"=>"Intermediate Silver",
                "Standard"=>"Intermediate Silver",
                "Rhythm"=>"Intermediate Silver",
                "Smooth"=>"Intermediate Bronze"
            ]
        ],
        [
            "name"=>["first"=>"Gold", "last"=>"Youth"],
            "sex"=>"F",
            "typeA"=>"Student",
            "typeB"=>"Amateur",
            "years"=>16,
            "proficiency"=> [
                "Latin"=>"Full Gold",
                "Standard"=>"Full Gold",
                "Rhythm"=>"Full Gold",
                "Smooth"=>"Full Gold"
            ]
        ],
        [
            "name"=>["first"=>"Novice", "last"=>"Adult"],
            "sex"=>"F",
            "typeA"=>"Student",
            "typeB"=>"Amateur",
            "years"=>30,
            "proficiency"=> [
                "Latin"=>"Novice",
                "Standard"=>"Novice",
                "Rhythm"=>"Novice",
                "Smooth"=>"Novice"
            ]
        ],
        [
            "name"=>["first"=>"Prechamp", "last"=>"Senior1"],
            "sex"=>"M",
            "typeA"=>"Student",
            "typeB"=>"Amateur",
            "years"=>35,
            "proficiency"=> [
                "Latin"=>"Pre Championship",
                "Standard"=>"Pre Championship",
                "Rhythm"=>"Pre Championship",
                "Smooth"=>"Pre Championship"
            ]
        ],
    ];

    /**
     * @param string $channelName
     * @param string $username
     * @throws AppException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws Exception
     */
    protected static function setupUserWorkareaParticipants(string $channelName, string $username)
    {
        /** @var KernelInterface $kernel */
        $kernel = new Kernel('dev',true);
        $kernel->boot();
        self::$em = $kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        $participantTag = self::fetchTag('participant');
        $competitionTag = self::fetchTag('competition');
        $channel = self::fetchChannel($channelName);
        $user = self::fetchUser($username);
        $workarea = self::fetchWorkarea($competitionTag,$channel,$user);
        self::clearPriorParticipants();
        foreach(self::$participants as $participant) {
            self::addParticipantForm($participant, $participantTag, $workarea);
        }
        $kernel=null;
    }

    protected static function clearPriorParticipants()
    {
        $conn = self::$em->getConnection();
        $conn->exec('SET FOREIGN_KEY_CHECKS = 0');
        $conn->exec('TRUNCATE TABLE form');
        $conn->exec('SET FOREIGN_KEY_CHECKS= 1');
    }

    /**
     * @param array $participant
     * @param Tag $participantTag
     * @param Workarea $workarea
     * @throws Exception
     */
    private static function addParticipantForm(array $participant, Tag $participantTag, Workarea $workarea)
    {
        $form = new Form();
        $note = $participant['name']['last'].', '.$participant['name']['first'];
        $form->setContent($participant)
            ->setWorkarea($workarea)
            ->setTag($participantTag)
            ->setNote($note)
            ->setUpdatedAt(new \DateTime('now'));
        self::$em->persist($form);
        self::$em->flush();
    }

    /**
     * @param string $tagName
     * @return Tag
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private static function fetchTag(string $tagName): Tag
    {
        /** @var TagRepository $tagRepository */
        $tagRepository = self::$em->getRepository(Tag::class);
        $tag = $tagRepository->fetch($tagName);
        return $tag;
    }

    /**
     * @param string $channelName
     * @return Channel
     * @throws AppException
     */
    private static function fetchChannel(string $channelName): Channel
    {
        /** @var ChannelRepository $channelRepository */
        $channelRepository = self::$em->getRepository(Channel::class);
        /** @var Channel $channel */
        $channel = $channelRepository->findOneBy(['name'=>$channelName]);
        if(!$channel) {
            throw new AppException("Channel not found in ".__FILE__." at line ".__LINE__);
        }
        return $channel;
    }

    /**
     * @param string $username
     * @return User
     * @throws Exception
     */
    private static function fetchUser(string $username): User
    {
        /** @var UserRepository $userRepository */
        $userRepository = self::$em->getRepository(User::class);
        /** @var User $user */
        $user=$userRepository->findOneBy(['username'=>$username]);
        if(!$user) {
            $user = new User();
            $user->setUsername($username)
                ->setInfo(self::$_user)
                ->setName(self::$_user['name']['last'].', '.self::$_user['name']['first'])
                ->setCreatedAt(new \DateTime('now'));
            self::$em->persist($user);
            self::$em->flush();
        }
        return $user;
    }


    /**
     * @param Tag $tag
     * @param Channel $channel
     * @param User $user
     * @return Workarea
     * @throws Exception
     */
    private static function fetchWorkarea(Tag $tag, Channel $channel, User $user): Workarea
    {
        /** @var WorkareaRepository $workareaRepository */
        $workareaRepository = self::$em->getRepository(Workarea::class);
        $workarea = $workareaRepository->findOneBy(['tag'=>$tag,'channel'=>$channel,'user'=>$user]);
        if(!$workarea) {
            $workarea = new Workarea();
            $workarea->setTag($tag)
                ->setChannel($channel)
                ->setUser($user)
                ->setCreatedAt(new \DateTime('now'));
            self::$em->persist($workarea);
            self::$em->flush();
        }
        return $workarea;
    }

}
<?php

use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use App\Entity\Model\Event;
use App\Repository\Model\EventRepository;
use App\Repository\Sales\FormRepository;
use App\AppException;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\User;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\TagRepository;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Gherkin\Exception\ParserException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Environment\Environment;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/25/19
 * Time: 5:34 PM
 */

class DatabaseContext implements Context
{
    use TraitDatabaseInit;

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
     * @var KernelInterface
     */
    private $kernel;

    /** @var  EntityManagerInterface*/
    private $entityManagerSales;

    /** @var EntityManagerInterface */
    private $entityManagerModel;

    /** @var UserPasswordEncoderInterface */
    private $userPasswordEncoder;

    /** @var Workarea */
    private $workarea;


    /** @var HttpContext */
    private $httpContext;

    /** @var \App\Utils\Operation  */
    private $operation;


    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->entityManagerSales = $kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        $this->entityManagerModel = $kernel->getContainer()->get('doctrine.orm.model_entity_manager');
        $this->userPasswordEncoder = $kernel->getContainer()->get('security.user_password_encoder.generic');
        $this->operation = $kernel->getContainer()->get('app.utils_operation');
    }

    /**
     * @BeforeFeature
     * @param BeforeFeatureScope $scope
     * @throws Exception
     */
    public static function beforeFeature(BeforeFeatureScope $scope)
    {
        self::setupChannelInventory();
    }


    /**
     * @BeforeScenario
     * @param ScenarioScope $scope
     * @throws DBALException
     */

    public function beforeScenario(ScenarioScope $scope)
    {
        /** @var Environment $environment */
        $environment = $scope->getEnvironment();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->httpContext = $environment->getContext('HttpContext');
        $conn = $this->entityManagerSales->getConnection();
        $conn->exec('SET FOREIGN_KEY_CHECKS = 0');
        $conn->exec('TRUNCATE TABLE form');
        $conn->exec('TRUNCATE TABLE workarea');
        $conn->exec('SET FOREIGN_KEY_CHECKS=1');
    }

    /**
     * @Given a registration for :
     * @param PyStringNode $string
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws Exception
     */
    public function aRegistrationFor(PyStringNode $string)
    {
        $content = json_decode($string,true);
        if(!$content) {
            throw new ParserException("Unable to parse PyStringNode.  Syntax error or missing punctuation.");
        }
        $email = $content['email'];
        $repositoryUser = $this->entityManagerSales->getRepository(User::class);
        $user=$repositoryUser->findOneBy(['username'=>$email]);
        if(!$user) {
            $user = new User();
            $user->setName($this->getName($content))
                ->setUsername($email)
                ->setInfo($content)
                ->setCreatedAt(new \DateTime('now'));
        }
        $this->entityManagerSales->persist($user);
        $this->entityManagerSales->flush();
        $repositoryChannel = $this->entityManagerSales->getRepository(Channel::class);
        /** @var TagRepository $repositoryTag */
        $repositoryTag = $this->entityManagerSales->getRepository(Tag::class);
        /** @var Channel $channel */
        $channel = $repositoryChannel->findOneBy(['name'=>'georgia-dancesport']);
        $tag = $repositoryTag->fetch('competition');
        $workarea = new Workarea();
        $workarea->setUser($user)
                    ->setTag($tag)
                    ->setChannel($channel)
                    ->setCreatedAt(new \DateTime('now'));
        $this->entityManagerSales->persist($workarea);
        $this->entityManagerSales->flush();
        $this->workarea = $workarea;
    }

    /**
     * @param $content
     * @return string
     */
    private function getName($content)
    {
        return $content['name']['last'].', '.$content['name']['first'];
    }


    /**
     * @Then encrypted :password is saved for :username
     * @param string $password
     * @param string $username
     * @throws AppException
     */
    public function encryptedIsSavedFor(string $password, string $username)
    {
        $repository = $this->entityManagerSales->getRepository(User::class);
        /** @var User $user */
        $user = $repository->findOneBy(['username'=>$username]);
        $isValid = $this->userPasswordEncoder->isPasswordValid($user,$password);
        if(!$isValid) {
            throw new AppException("Encrypted password is incorrect or missing.");
        }
    }


    /**
     * @Then pin :pin is emailed to :username
     * @param string $pin
     * @param string $username
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function pinIsEmailedTo(string $pin, string $username)
    {
        $repository = $this->entityManagerSales->getRepository(User::class);
        /** @var User $user */
        $user = $repository->findOneBy(['username'=>$username]);
        $encryption = $this->userPasswordEncoder->encodePassword($user,$pin);
        $user->setPassword($encryption);
        $this->entityManagerSales->flush();
    }


    /**
     * @Then pin is cleared for :username
     * @param string $username
     * @throws AppException
     */
    public function pinIsClearedFor(string $username)
    {
        /** @var User $user */
        $repository = $this->entityManagerSales->getRepository(User::class);
        $user=$repository->findOneBy(['username'=>$username]);
        if(!is_null($user->getPassword())) {
            throw new AppException('Pass is not cleared for user.');
        }
    }


    /**
     * @Given A channel is defined:
     * @param PyStringNode $node
     * @return Channel|null
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws Exception
     */
    public function aChannelIsDefined(PyStringNode $node): ?Channel
    {
        $channel = json_decode($node->getRaw(),true);
        /** @var Channel $channel */
        $repository = $this->entityManagerSales->getRepository(Channel::class);
        $channel = $repository->findOneBy(['name'=>$channel['name']]);
        if(!$channel){
            $logo = file_get_contents(__DIR__.'/../../assets/dancers-icon.png');
            /** @var string $parameters */
            $channel = new Channel();
            $channel->setName($channel['name'])
                ->setHeading($channel['heading'])
                ->setLogo($logo)
                ->setParameters(self::$parameters)
                ->setOnlineAt(new \DateTime('2019-06-01'))
                ->setOfflineAt(new \DateTime('2019-09-20'))
                ->setCreatedAt(new \DateTime('now'));
            $this->entityManagerSales->persist($channel);
            $this->entityManagerSales->flush();
        }
        return $channel;
    }


    /**
     * @Given I have entered multiple participants:
     * @param TableNode $table
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws Exception
     */
    public function iHaveEnteredMultipleParticipants(TableNode $table)
    {
        $_table = $table->getColumnsHash();
        /** @var TagRepository $tagRepository */
        $tagRepository = $this->entityManagerSales->getRepository(Tag::class);
        $tag = $tagRepository->fetch('participant');

        foreach($_table as $entry) {
            $arr = [];
            $arr['name']=['first'=>$entry['first'],'last'=>$entry['last']];
            $arr['years']=$entry['years'];
            $arr['status']=$entry['status'];
            $arr['sex']=$entry['sex'];
            $arr['type']=$entry['type'];
            $arr['model']=explode(',',$entry['models']);
            $arr['proficiency']=['Latin'=>$entry['latin'],
                                 'Standard'=>$entry['standard'],
                                 'Rhythm'=>$entry['rhythm'],
                                 'Smooth'=>$entry['smooth']];
            $note=$entry['last'].', '.$entry['first'];
            $form = new Form();
            $form->setNote($note)
                ->setTag($tag)
                ->setContent($arr)
                ->setWorkarea($this->workarea)
                ->setUpdatedAt(new \DateTime('now'));
            $this->entityManagerSales->persist($form);
        }
        $this->entityManagerSales->flush();
    }




    /**
     * @Given participants have ids:
     * @param TableNode $table
     * @throws AppException
     */
    public function participantsHaveIds(TableNode $table)
    {
        $repository = $this->entityManagerSales->getRepository(Form::class);
        $_table = $table->getColumnsHash();
        foreach($_table as $entry) {
           $id = (int) $entry['id'];
           /** @var Form $form */
           $form = $repository->find($id);
           $note=$form->getNote();
           $name = $entry['last'].', '.$entry['first'];
           if($note!=$name) {
               throw new AppException("Expected id:$id for $name but found id for $note");
           }
        }
    }



    public function getEntityManagerSales()
    {
        return $this->entityManagerSales;
    }

    public function getEntityManagerModel()
    {
        return $this->entityManagerModel;
    }

    /**
     * @Given I have added teams:
     * @param TableNode $table
     * @throws AppException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function iHaveAddedTeams(TableNode $table)
    {

       /** @var TagRepository $tagRepository */
       $tagRepository = $this->entityManagerSales->getRepository(Tag::class);
       /** @var FormRepository $formRepository */
       $formRepository = $this->entityManagerSales->getRepository(Form::class);
       $_table = $table->getColumnsHash();
       foreach($_table as $entry) {
           /** @var Form $formLeft */
           $formLeft = $formRepository->find($entry['id-left']);
           /** @var Workarea $workarea */
           $workarea = $formLeft->getWorkarea();
           /** @var Form $formRight */
           $formRight= $formRepository->find($entry['id-right']);
           /** @var array $contentLeft */
           $contentLeft = $formLeft->getContent();
           $contentLeft['id']=$formLeft->getId();
           /** @var array $contentRight */
           $contentRight= $formRight->getContent();
           $contentRight['id']=$formRight->getId();
           $teamEvents = $this->operation->teamEvents([$contentLeft,$contentRight]);
           $tag = $tagRepository->fetch('team');
           $formRepository->post($teamEvents->toArray(),$tag,$workarea);
       }
    }

    /**
     * @Given teams have ids:
     * @param TableNode $table
     * @throws AppException
     */
    public function teamsHaveIds(TableNode $table)
    {
        $formRepository = $this->entityManagerSales->getRepository(Form::class);
        $_table = $table->getColumnsHash();
        foreach($_table as $entry){
            /** @var Form $form */
            $note = $entry['name'];
            $form=$formRepository->findOneBy(['note'=>$note]);
            $formId=$form->getId();
            $content = $form->getContent();
            if($formId!=$entry['id']) {
                throw new AppException("form_id=$formId did not correspond to team record of $note");
            }
            $team = $content['team'];
            $idLeft = intval($entry['id-left']);
            $idRight= intval($entry['id-right']);
            list($participantLeft,$participantRight)=$this->leftRightOnSexOrOldest($team);
            if($team[0]['form_id']!=$participantLeft['form_id']) {
                throw new AppException("id-left=$idLeft did not correspond team record of $note");
            }
            if($team[1]['form_id']!=$participantRight['form_id']) {
                throw new AppException("id-right=$idRight did not correspond team record of $note");
            }
        }
    }

    private function leftRightOnSexOrOldest(array $team)
    {
        if($team[0]['sex']!=$team[1]['sex']) {
            if($team[0]['sex']=='M') {
                return [$team[0],$team[1]];
            } else {
                return [$team[1],$team[0]];
            }
        } else {
            if($team[0]['years']>=$team[1]['years']) {
                return [$team[0],$team[1]];
            } else {
                return [$team[1],$team[0]];
            }

        }
    }


    /**
     * @Given team :formId has the following events for :modelName in :style:
     * @param $formId
     * @param $modelName
     * @param $style
     * @param TableNode $table
     * @throws AppException
     */
    public function teamHasTheFollowingEventsForIn($formId, $modelName, $style, TableNode $table)
    {
        $formRepository = $this->entityManagerSales->getRepository(Form::class);
        /** @var Form $form */
        $form = $formRepository->find($formId);
        $content = $form->getContent();
        $modelContent = $content['selections'][$modelName];
        $_table = $table->getColumnsHash();
        foreach ($_table as $expected) {
            $eventId = intval($expected['event_id']);
            $actual = $this->pickOut($eventId, $style, $modelContent);
            $actualDanceCount =count($actual['dances'][$expected['substyle']]);
            if($expected['age']!=$actual['age']) {
                $expected = $expected['age'];$actual = $actual['age'];
                throw new AppException("For event_id=$eventId expected $expected age but found $actual.");
            }
            if($expected['proficiency']!=$actual['proficiency']) {
                $expected = $expected['proficiency'];$actual = $actual['proficiency'];
                throw new AppException("For event_id=$eventId expected $expected proficiency but found $actual.");
            }
            if($expected['status']!=$actual['status']) {
                $expected = $expected['status'];$actual = $actual['status'];
                throw new AppException("For event_id=$eventId expected $expected status but found $actual.");
            }
            if($expected['type']!=$actual['type']) {
                $expected = $expected['type'];$actual = $actual['type'];
                throw new AppException("For event_id=$eventId expected $expected type but found $actual.");
            }
            if($expected['sex']!=$actual['sex']) {
                $expected = $expected['sex'];$actual = $actual['sex'];
                throw new AppException("For event_id=$eventId expected $expected sex but found $actual.");
            }

            if($actualDanceCount!=intval($expected['dances'])) {
                $expectedCount = $expected['dances'];
                throw new AppException("For event_id=$eventId expected $expectedCount dances but found $actualDanceCount.");
            }



        }
    }

    /**
     * @param int $eventId
     * @param string $style
     * @param array $modelContent
     * @return mixed
     * @throws AppException
     */
    private function pickOut(int $eventId,  string $style, array $modelContent){
        $eventList = $modelContent[$style];
        foreach($eventList as $event) {
            if($event['event_id']==$eventId) {
                return $event;
            }
        }
        throw new AppException("No event was found for event_id=$eventId");
    }

    /**
     * @Then entry form :formId has entry-id :eventId
     * @param int $formId
     * @param int $eventId
     * @throws AppException
     */
    public function entryFormHasEntryId(int $formId, int $eventId)
    {

        $formRepository = $this->entityManagerSales->getRepository(Form::class);
        /** @var Form $form */
        $form=$formRepository->find($formId);
        if(!$form) {
            throw new AppException("No entry form was found with ID=$formId");
        }
        $tagName = $form->getTag()->getName();
        if($tagName!='entries') {
            throw new AppException("Expected form of type 'entries' but found $tagName");
        }
        $content = $form->getContent();
        $entries = $content['entries'];
        $entryIds = array_map(function($entry){return $entry['event_id'];},$entries);
        if(!in_array($eventId,$entryIds)){
            throw new AppException("Event ID=$eventId was not found for this set of entrires.");
        }

    }

    /**
     * @Given I have posted entries:
     * @param TableNode $table
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function iHavePostedEntries(TableNode $table)
    {
       /** @var EventRepository $eventRepository */
       $eventRepository=$this->entityManagerModel->getRepository(Event::class);
       /** @var FormRepository $formRepository */
       $formRepository = $this->entityManagerSales->getRepository(Form::class);
       /** @var TagRepository $tagRepository */
       $tagRepository = $this->entityManagerSales->getRepository(Tag::class);
       /** @var Tag $tagEntries */
       $tagEntries = $tagRepository->fetch('entries');

       foreach($table as $expected) {
           $teamId=$expected['team-id'];
           $eventIds = [$expected['event-id0'],$expected['event-id1'],$expected['event-id2']];
           $entries = [];
           /** @var Form $formEvents */
           $formEvents=$formRepository->find($teamId);
           $teamEvents = $formEvents->getContent();
           $team = $teamEvents['team'];
           foreach($eventIds as $id) {
               /** @var Event $event */
               $event=$eventRepository->find($id);
               $describe = $event->getDescribe();
               $describe['event_id']=$event->getId();
               $describe['model_id']=$event->getModel()->getId();
               $entries[]=$describe;
           }
           $teamEntries=['team'=>$team,'entries'=>$entries,'team-id-events'=>$teamId];
           $workarea = $formEvents->getWorkarea();
           $formEntries=$formRepository->post($teamEntries,$tagEntries,$workarea);
           $teamEvents['team-id-entries']=$formEntries->getId();
           $formEvents->setContent($teamEvents);
           foreach($team as $members) {
               $formId = $members['form_id'];
               $formParticipant = $formRepository->find($formId);
               $participant=$formParticipant->getContent();
               $participant['team-id-entries']=isset($participant['team-id-entries'])?$participant['team-id-entries']:[];
               $participant['team-id-entries'][]=$formEntries->getId();
               $formParticipant->setContent($participant);
           }
       }
       $this->entityManagerSales->flush();
    }

    /**
     * @Then entry form :teamId has no entry-id :eventId
     * @param $teamId
     * @param $eventId
     * @throws AppException
     */
    public function entryFormHasNoEntryId($teamId, $eventId)
    {
        $formRepository = $this->entityManagerSales->getRepository(Form::class);
        /** @var Form $formEntries */
        $formEntries=$formRepository->find($teamId);
        $content = $formEntries->getContent();
        $entries = $content['entries'];
        $eventIds = array_map(function($entry){return $entry['event_id'];},$entries);
        if(in_array($eventId,$eventIds)) {
            throw new AppException("Event ID=$eventId was found but should not exist.");
        }
    }



    /**
     * @Then entry form :id does not exist
     */
    public function entryFormDoesNotExist($id)
    {
        $formRepository = $this->entityManagerSales->getRepository(Form::class);
        $form = $formRepository->find($id);
        if($form) {
            throw new AppException("Form ID=$id is found and should been removed");
        }
    }
}

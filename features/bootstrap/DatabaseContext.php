<?php

use App\AppException;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\User;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\TagRepository;
use Behat\Behat\Hook\Scope\FeatureScope;
use Behat\Behat\Hook\Scope\ScenarioScope;
use Behat\Gherkin\Exception\ParserException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Behat\Testwork\Environment\Environment;
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



    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->entityManagerSales = $kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        $this->entityManagerModel = $kernel->getContainer()->get('doctrine.orm.model_entity_manager');
        $this->userPasswordEncoder = $kernel->getContainer()->get('security.user_password_encoder.generic');
    }

    /**
     * @BeforeFeature
     * @param FeatureScope $scope
     * @throws Exception
     */
    public static function beforeFeature(FeatureScope $scope)
    {
        self::setupChannelInventory();
    }


    /**
     * @BeforeScenario
     * @param ScenarioScope $scope
     */

    public function beforeScenario(ScenarioScope $scope)
    {
        /** @var Environment $environment */
        $environment = $scope->getEnvironment();
        $this->httpContext = $environment->getContext('HttpContext');
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
        self::$channel = $channel;
        return $channel;
    }


    /**
     * @Given I have entered multiple participants:
     * @param TableNode $table
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
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
                ->setWorkarea($this->workarea);
            $this->entityManagerSales->persist($form);
        }
        $this->entityManagerSales->flush();
    }


    /**
     * @Given participants have ids:
     * @param TableNode $table
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
}

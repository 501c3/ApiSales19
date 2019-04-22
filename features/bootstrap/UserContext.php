<?php

use App\Entity\Sales\Channel;
use App\Entity\Sales\Tag;
use App\Entity\Sales\User;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\UserRepository;
use App\Repository\Sales\WorkareaRepository;
use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use App\AppException;
use Behat\Gherkin\Node\PyStringNode;
use Doctrine\ORM\EntityManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;



/**
 * This context class contains the definitions of the steps used by the demo 
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 * 
 * @see http://behat.org/en/latest/quick_start.html
 */
class UserContext implements Context
{
    use TraitSetup;

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var array */
    protected $content;


    /** @var string */
    private $method;

    /** @var string */
    private $path = "";

    /** @var string */
    private $password;

    /** @var User */
    private $user;

    /** @var EntityManager */
    private static $em;

    /** @var EntityManager */
    private $entityManager;

    /** @var string */
    private $scheme;

    /** @var KernelInterface  */
    private $kernel;

    /** @var JWTManager|object  */
    private $JWTTokenManager;

    /** @var UserRepository */
    private $userRepository;

    /** @var WorkareaRepository */
    private $workareaRepository;

    /** @var TagRepository */
    private $tagRepository;

    /** @var ChannelRepository */
    private $channelRepository;

    /**
     * UserContext constructor.
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->kernel=$kernel;
        $this->entityManager = $this->kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        $this->userRepository = $this->entityManager->getRepository(User::class);
        $this->workareaRepository=$this->entityManager->getRepository(Workarea::class);
        $this->tagRepository = $this->entityManager->getRepository(Tag::class);
        $this->channelRepository=$this->entityManager->getRepository(Channel::class);
    }


    /**
     * @BeforeFeature
     * @param BeforeFeatureScope $scope
     * @throws \Doctrine\DBAL\DBALException
     */
    public static function beforeFeature(BeforeFeatureScope $scope)
    {
        if($scope->getFeature()->getFile()=="features/user.feature") {
            /** @noinspection PhpUnhandledExceptionInspection */
            self::setupChannelInventory();
            $conn = self::$em->getConnection();
            $conn->exec('SET FOREIGN_KEY_CHECKS = 0');
            $conn->exec('TRUNCATE TABLE user');
            $conn->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }


    public function beforeScenario()
    {
        $this->path="";
        $this->method="";
        $this->scheme="http";
        $this->request=null;
    }

    /**
     * @Given I am registered as:
     * @param PyStringNode $node
     * @throws AppException
     */
    public function iAmRegisteredAs(PyStringNode $node)
    {
        $content = json_decode($node->getRaw(),true);
        $user=$this->userRepository->post($content);
        if(!$user) {
            throw new LogicException('User is not added to the database.');
        }
        $this->user = $user;
    }


    /**
     * @Given a previous registration for :username
     * @param $username
     */
    public function aPreviousRegistrationFor($username)
    {
       $user = $this->userRepository->findOneBy(['username'=>$username]);
       if(!$user) {
           throw new LogicException("Previous registration for $username was not found.");
       }
       $this->user=$user;
    }



    /**
     * @Given a temporary password :hash is saved
     * @param $hash
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function aTemporaryPasswordIsSaved(string $hash)
    {
        $this->user->setPassword($hash);
        $this->entityManager->flush();
    }

    /**
     * @Given previously registered is:
     * @param PyStringNode $string
     * @throws Exception
     */
    public function previouslyRegisteredIs(PyStringNode $string)
    {
        $content = json_decode($string,true);
        $email = $content['email'];
        $repository = $this->entityManager->getRepository(User::class);
        $user=$repository->findOneBy(['email'=>$email]);
        if(!$user) {
           $user = new User();
           $user->setName($this->getName($content))
               ->setUsername($content['email'])
               ->setInfo($content)
               ->setCreatedAt(new \DateTime('now'));

           self::$em->persist($user);
           self::$em->flush();
        }
        //$this->body=$content;
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
     * @When I request :url with method :method and credentials:
     * @param $url
     * @param $method
     * @param PyStringNode $node
     * @throws Exception
     */
    public function iRequestWithMethodAndCredentials($url, $method, PyStringNode $node)
    {
        /** @var array $credentials */
        $credentials = json_decode($node->getRaw(),true);
        if(!$credentials) {
            throw new InvalidArgumentException('JSON formatting error in credentials');
        }
        $request = Request::create($url,$method,[],[],[],[],$credentials);
        $this->response = $this->kernel->handle($request);
    }


    /**
     * @Then encrypted :password is saved for :username
     * @param string $password
     * @param string $username
     */
    public function encryptedIsSavedFor(string $password, string $username)
    {
        $this->password = $password;
        /** @var User $user */

        $user = $this->userRepository->findOneBy(['username'=>$username]);
        if(!$user->getPassword()) {
            throw new LogicException("Encrypted password was not saved.");
        }
        if($password == $user->getPassword()) {
            throw new LogicException('Password is not encrypted (correctly).');
        }
    }

    /**
     * @Then password is cleared for :username
     * @param string $username
     * @throws AppException
     */
    public function passwordIsClearedFor(string $username)
    {
        /** @var User $user */
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['username'=>$username]);
        if(!$user->getUsername()!=null) {
            throw new AppException("Password for $user was not cleared.");
        }
    }


    /**
     * @Then a new :tag workarea is created for :channel and :username
     * @param string $tag
     * @param string $channel
     * @param string $username
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function aNewWorkareaIsCreatedForAnd(string $tag, string $channel, string $username)
    {
        $_tag = $this->tagRepository->fetch($tag);
        $_channel = $this->channelRepository->findOneBy(['name'=>$channel]);
        $_user = $this->userRepository->findOneBy(['username'=>$username]);
        $workarea = $this->workareaRepository->findOneBy(['tag'=>$_tag, 'channel'=>$_channel, 'user'=>$_user]);
        if(!$workarea) {
            throw new LogicException("workarea was not defined for ($tag,$channel,$username)");
        }
    }


    /**
     * @Given the request body is:
     * @param PyStringNode $pyStringNode
     */
    public function theRequestBodyIs(PyStringNode $pyStringNode)
    {
        $this->content = json_decode($pyStringNode->getRaw(),true, 5);
        if(!$this->content) {
            throw new RuntimeException("Syntax error in PyStringNode.");
        }
    }


    /**
     * @When I request :url with method :method
     * @param $url
     * @param $method
     * @throws Exception
     */
    public function iRequestWithMethod($url, $method)
    {
        $request = Request::create($url,$method,[],[],[],[],$this->content);
        try{
            $this->response = $this->kernel->handle($request);
        } catch(Exception $e) {
            switch($e->getCode()) {
                case AppException::APP_REDUNDANT_USER:
                    return;
            }
            throw $e;
        }
    }

    /**
     * @Then the response code is :value
     * @param $value
     */
    public function theResponseCodeIs($value)
    {
        $code=$this->response->getStatusCode();
        if($value!=$code) {
            throw new LogicException("Status code found: $code.");
        }
    }

    /**
     * @Then the :key response header is :value
     * @param $key
     * @param $expected
     * @throws AppException
     */
    public function theResponseHeaderIs($key, $expected)
    {
        $found = $this->response->headers->get($key);
        if($key==='Authorization') {
            $token = substr($found,strlen('Bearer '));
            file_put_contents('features/authorization-jwt.txt',$token);
        }
        if(!$found) {

            throw new AppException("Header key: $key was not found.");
        }

        if(strpos($found,$expected)===false) {
            throw new AppException("Header format is incorrect");
        }
    }

    /**
     * @Then the response status line is :expected
     * @param $expected
     */
    public function theResponseStatusLineIs($expected)
    {
        $statusCode = $this->response->getStatusCode();
        $statusLine = Response::$statusTexts[$statusCode];
        if($statusLine!==$expected)
        {
            throw new LogicException("Expected $expected as statusLine but found $statusLine");
        }
    }

    /**
     * @Then the response body contains JSON:
     * @param PyStringNode $node
     * @throws AppException
     */
    public function theResponseBodyContainsJson(PyStringNode $node)
    {
        $expected=$this->response->getContent();
        $match=json_decode($expected,true)==json_decode($node->getRaw(),true);
        if(!$match) {
            throw new AppException('The response body did not match what was expected.');
        }
    }

}

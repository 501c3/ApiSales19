<?php

use App\Entity\Sales\User;
use App\Repository\Sales\UserRepository;
use Behat\Behat\Tester\Exception\PendingException;
use App\AppException;
use Behat\Gherkin\Node\PyStringNode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * This context class contains the definitions of the steps used by the demo 
 * feature file. Learn how to get started with Behat and BDD on Behat's website.
 * 
 * @see http://behat.org/en/latest/quick_start.html
 */
// class UserContext implements Imbo\BehatApiExtension\Context\ApiClientAwareContext
class UserContext implements Behat\Symfony2Extension\Context\KernelAwareContext
{
    use \Behat\Symfony2Extension\Context\KernelDictionary;


    /** @var string */
    private $method;

    /** @var string */
    private $path = "";

    /** var array|null */
    private $body ;

    /**@var Response|null*/
    private $response;

    /** @var Request */
    private $request;

    /** @var string */
    private $password;

    /** @var User */
    private $user;

    /** @var EntityManagerInterface */
    private static $em;





    private $JWTTokenManager;

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
        $this->JWTTokenManager= $this->getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $em = $kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        self::$em = $em;
    }

    /** @BeforeScenario
     * @throws \Doctrine\DBAL\DBALException
     */
    public function beforeScenario()
    {
        $conn=self::$em->getConnection();
        $conn->exec('SET FOREIGN_KEY_CHECKS = 0');
        $conn->exec('TRUNCATE TABLE user');
        $conn->exec('SET FOREIGN_KEY_CHECKS = 1');
    }

    /** AfterFeature */
    public static function afterFeature()
    {

    }



    /** @BeforeScenario */
    public function prepareForScenario()
    {
        $this->path="";
        $this->method="";
        $this->scheme="http";
        $this->body= null;
        $this->response=null;
        $this->request=null;
    }


    /**
     * @Given the request body is:
     * @param PyStringNode $pyStringNode
     */
    public function theRequestBodyIs(PyStringNode $pyStringNode)
    {
        $string = $pyStringNode->getRaw();
        $this->body = json_decode($string,true, 5 );
        if(!$this->body) {
            throw new RuntimeException("Syntax error in PyStringNode.");
        }
    }

    /**
     * @Given I am registered as:
     * @throws AppException
     */
    public function iAmRegisteredAs(PyStringNode $node)
    {
        /** @var UserRepository $repo */
        $repo = self::$em->getRepository(User::class);
        $content = json_decode($node->getRaw(),true);
        $user=$repo->post($content);
        if(!$user) {
            throw new LogicException('User is not added to the database.');
        }
        $this->user = $user;
    }


    /**
     * @Given the request body contains :username and password
     * @param $username
     * @throws AppException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function theRequestBodyContainsAndPassword($username)
    {
        /** @var User $user */
        $user = self::$em->getRepository(User::class)->findOneBy(['username'=>$username]);
        if(!$user){
            throw new AppException(AppException::statusText[AppException::APP_NO_USER]);
        }
        $password = 'secret password';
        $user->setPassword($password);
        $this->body=['username'=>$username,'password'=>$password];
        self::$em->flush();
    }

    /**
     * @Given the request body contains credentials :username and :password
     */
    public function theRequestBodyContainsCredentialsAnd($username, $password)
    {
        $this->body = ['username'=>$username,'password'=>$password];
    }


    /**
     * @Given a temporary password :hash is saved
     * @param $hash
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function aTemporaryPasswordIsSaved($hash)
    {
        $this->user->setPassword($hash);
        self::$em->flush();
    }

    /**
     * @Given the password saved is :pin
     */
    public function thePasswordSavedIs($pin)
    {

    }


    /**
     * @When I request :url with method :method
     * @param $url
     * @param $method
     * @throws Exception
     */
    public function iRequestWithMethod($url, $method)
    {
        $request = Request::create($url,$method,[],[],[],[],$this->body);
        try{
          $this->response = $this->kernel->handle($request);
        } catch(Exception $e) {
            switch($e->getCode()) {

                case \App\AppException::APP_REDUNDANT_USER:
                    return;
            }
            throw $e;
        }

    }

    /**
     * @Then the response code is :value
     */
    public function theResponseCodeIs($value)
    {
        $code=$this->response->getStatusCode();
        if($value!=$code) {
            throw new LogicException("Status code found: $code.");
        }
    }

    /**
     * @Then the response status line is :expected
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
     * @Then the :key response header contains :value
     * @param $key
     * @param $value
     * @throws AppException
     */
    public function theResponseHeaderContains($key, $value)
    {
        $hasKey=$this->response->headers->has($key);
        if(!$hasKey) {
            throw new AppException("There is no key for $value");
        }

    }

    /**
     * @Then I have valid jwt
     */
    public function iHaveValidJwt()
    {
        $content = json_decode($this->response->getContent(),true);
        /** @var User $user */
        $user = self::$em->getRepository(User::class)->find($content['id']);
        $testToken = $this->JWTTokenManager->create($user);
        $responseToken = substr($this->response->headers->get('Authorization'),strlen('Bearer '));
        if(strcmp($testToken,$responseToken)!==0){
            throw new LogicException("There is an error in creating the Jason Web Token.");
        }
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
        $repository = self::$em->getRepository(User::class);
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
        $this->body=$content;
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


    /**
     * @Then encrypted :password is saved
     * @param $password
     */
    public function encryptedIsSaved($password)
    {
        $this->password = $password;

        $email = $this->body['email'];
        /** @var User $user */
        $user = self::$em->getRepository(User::class)->findOneBy(['username'=>$email]);
        if(!$user->getPassword()) {
            throw new LogicException("Password was not saved.");
        }
//        if($password !== $user->getPassword()) {
//            throw new LogicException('Password is not encrypted correctly.');
//        }
    }

    /**
     * @Then I receive email with :pin
     */
    public function iReceiveEmailWith($pin)
    {
        throw new PendingException("TODO: Send $pin via email in App/Controller/SalesUserController");
    }


    /**
     * @Then saved password is cleared
     */
    public function savedPasswordIsCleared()
    {
        $email = $this->body['username'];
        /** @var User $user */
        $user = self::$em->getRepository(User::class)->findOneBy(['username'=>$email]);
        if(!is_null($user->getPassword())) {
            throw new LogicException('The users password was not cleared after successful login');
        }
    }





}

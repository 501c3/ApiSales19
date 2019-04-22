<?php

use App\Entity\Sales\Form;
use App\Repository\Sales\FormRepository;
use Behat\Gherkin\Node\PyStringNode;
use App\AppException;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Tag;
use App\Entity\Sales\User;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\UserRepository;
use App\Repository\Sales\WorkareaRepository;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/17/19
 * Time: 10:37 PM
 */

class ParticipantContext implements Context
{
    use TraitSetup;
    use TraitWorkareaParticipants;
    const AUTHORIZATION = 'Authorization';

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var array */
    protected $content;

    /** @var array */
    protected $headers ;

    /** @var array  */
    protected $body;

    /** @var KernelInterface */
    protected $kernel;

    /** @var ChannelRepository  */
    private $channelRepository;

    /** @var JWTManager */
    private $tokenManager;

    /** @var PasswordEncoderInterface */
    private $passwordEncoder;

    /** @var TagRepository */
    private $tagRepository;

    /** @var WorkareaRepository */
    private $workareaRepository;

    /** @var UserRepository */
    private $userRepository;

    /** @var FormRepository */
    private $formRepository;

    /** @var User */
    private $user;

    /** @var Lcobucci\JWT\Token */
    private $token;

    /** @var array */
    private $list ;

    /** @var array */
    private $info;

    public function __construct(KernelInterface $kernel)
    {
        $entityManager = $kernel->getContainer()->get('doctrine.orm.sales_entity_manager');
        $this->tagRepository = $entityManager->getRepository(Tag::class);
        $this->channelRepository = $entityManager->getRepository(Channel::class);
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->workareaRepository = $entityManager->getRepository(Workarea::class);
        $this->formRepository = $entityManager->getRepository(Form::class);
        $container = $kernel->getContainer();
        $this->tokenManager = $container->get('lexik_jwt_authentication.jwt_manager');
        $this->passwordEncoder = $container->get('security.user_password_encoder.generic' );
        $this->kernel = $kernel;
    }

    /**
     * @BeforeFeature
     * @param BeforeFeatureScope $scope
     * @throws AppException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws Exception
     */
    public static function beforeFeature(BeforeFeatureScope $scope)
    {
        if($scope->getFeature()->getFile()=='features/participant.feature'){
            self::setupChannelInventory();
            self::setupUserWorkareaParticipants('georgia-dancesport','user@email.com');
        }
    }


    /**
     * @Given the channel :name exists
     * @param string $name
     * @throws AppException
     */
    public function theChannelExists(string $name)
    {
        $channel=$this->channelRepository->findOneBy(['name'=>$name]);
        if(!$channel) {
            throw new AppException("The channel $channel does not exist");
        }

    }

    /**
     * @Given I am registered as :username
     * @param string $username
     * @throws AppException
     */
    public function iAmRegisteredAs(string $username)
    {
        $user = $this->userRepository->findOneBy(['username'=>$username]);
        if(!$user) {
            throw new AppException("The user $username is not setup");
        }
        $this->user = $user;
    }

    /**
     * @Given JWT is recently generated
     */
    public function jwtIsRecentlyGenerated()
    {
        $this->token = $this->tokenManager->create($this->user);
    }

    /**
     * @Given :username is linked to a :tagged workarea for :channel
     * @param string $username
     * @param string $tagged
     * @param string $_channel
     */
    public function isLinkedToAWorkareaFor(string $username, string $tagged, string $_channel)
    {
        $user = $this->userRepository->findOneBy(['username'=>$username]);
        $tag = $this->tagRepository->findOneBy(['name'=>$tagged]);
        $channel = $this->channelRepository->findOneBy(['name'=>$_channel]);
        $workarea = $this->workareaRepository->findOneBy(['tag'=>$tag,'channel'=>$channel,'user'=>$user]);
        if(!$workarea) {
            throw new LogicException("Unable to find $tagged workarea for $channel and $username");
        }
    }


    /**
     * @Given password :password is encrypted and saved for :username
     * @param $password
     * @param $username
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function passwordIsEncryptedAndSavedFor($password, $username)
    {
        /** @var User $user */
        $user = $this->userRepository->findOneBy(['username'=>$username]);
        $encoded  = $this->passwordEncoder->encodePassword($user,$password);
        $user->setPassword($encoded);
        $em = $this->userRepository->getEntityManager();
        $em->flush();
        $this->user = $user;
    }

    /**
     * @Given the :key request header contains :string
     * @param string $key
     * @param $string
     */
    public function theRequestHeaderContains(string $key, string $string)
    {
        if($key === 'Authorization') {
            $token = file_get_contents('features/authorization-jwt.txt');
            if($string !=='Bearer ') {
                throw new LogicException("Authorization keys must begin with 'Bearer '. !!With space!!");
            }
            $this->headers=['Authorization'=>'Bearer '.$token];
        }

    }

    /**
     * @Given the participant request body is:
     * @param PyStringNode $string
     */
    public function theParticipantRequestBodyIs(PyStringNode $string)
    {
        $body = json_decode($string->getRaw(),true);
        if(!$body) {
            throw new RuntimeException("Error decoding PyStringNode for participant.");
        }

        if(!(isset($body['name'])&&
            isset($body['sex'])&&
            isset($body['typeA'])&&
            isset($body['typeB'])&&
            isset($body['proficiency'])))
        {
            throw new \RuntimeException('Missing field from participant request.');
        }
        $this->body = $body;
    }


    /**
     * @Given I have entered multiple participants
     * @throws AppException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function iHaveEnteredMultipleParticipants()
    {
        $tag = $this->tagRepository->fetch('participant');
        $users = $this->formRepository->findBy(['tag'=>$tag]);
        if(count($users)<2) {
            throw new AppException("Only 1 or 0 competitors in database. Fix test!");
        }
    }



    /**
     * @When I request :url for participants
     */
    public function iRequestForParticipants($url)
    {
        $request = Request::create($url);
        $request->headers->set('Authorization','Bearer '.$this->token);
        $this->response = $this->kernel->handle($request);
    }

    /**
     * @Then I receive participant list
     * @throws AppException
     */
    public function iReceiveParticipantList()
    {
        $content = json_decode($this->response->getContent(),true);
        if (!is_array($content)) {
            throw new AppException("The response should return a JSON list of participants");
        }
        $this->list = $content;
    }


    /**
     * @When I add participant request :arg1 with method :method
     * @param string $url
     * @param string $method
     * @throws Exception
     */
    public function iAddParticipantRequestWithMethod(string $url, string $method)
    {
        $request = Request::create($url,$method,[],[],[],[],$this->body);
        $request->headers->set('Authorization','Bearer '.$this->token);
        $this->response=$this->kernel->handle($request);
    }

    /**
     * @Then participant response code is :expected
     * @param string $expected
     * @throws AppException
     */
    public function participantResponseCodeIs(string $expected)
    {
        $found = $this->response->getStatusCode();
        if($found!=$expected) {
            throw new AppException("A status code of $found was found..");
        }
    }

    /**
     * @Then participant response status line is :expected
     * @param string $expected
     * @throws AppException
     */
    public function participantResponseStatusLineIs(string $expected)
    {
        $statusCode = $this->response->getStatusCode();
        $statusLine = Response::$statusTexts[$statusCode];
        if($statusLine!==$expected)
        {
            throw new AppException("Expected $expected as statusLine but found $statusLine");
        }
    }


    private function retrieveUsername(string $token)
    {
        $tokenParts = explode(".", $token);
        $tokenPayload = base64_decode($tokenParts[1]);
        $jwtPayload = json_decode($tokenPayload,true);
        return $jwtPayload['username'];

    }

    /**
     * @Then participant Authorization header JWT indicates user :expected
     * @param string $expected
     * @throws AppException
     */
    public function participantAuthorizationHeaderJwtIndicatesUser(string $expected)
    {
        $headerString = $this->response->headers->get('Authorization');
        $token = substr($headerString, strlen('Bearer '));
        $found=$this->retrieveUsername($token);
        if($found!==$expected) {
            throw new AppException("Found user $found but expected $expected");
        }
    }

    /**
     * @Then participant response JSON has field :name
     * @param string $name
     * @throws AppException
     */
    public function participantResponseJsonHasField(string $name)
    {
        $content = json_decode($this->response->getContent(),true);
        if(!isset($content[$name])) {
            throw new AppException("Missing field $name from response");
        }
    }

    /**
     * @Then participant response JSON tag field is :expected
     * @param string $expected
     * @throws AppException
     */
    public function participantResponseJsonTagFieldIs(string $expected)
    {
        $content = json_decode($this->response->getContent(),true);
        $found = $content['tag'];
        if($found!=$expected) {
            throw new AppException("Found response tag of $found but expected $expected");
        }
    }



    /**
     * @Then each participant list entry has field :name
     */
    public function eachParticipantListEntryHasField($name)
    {
        foreach($this->list as $element) {
            if(!isset($element[$name])) {
                throw new AppException("The element is missing entry for $name");
            }

        }
    }

    /**
     * @Given I have a participant with id of :id
     * @throws AppException
     */
    public function iHaveAParticipantWithIdOf($id)
    {
        /** @var Form $form */
        $form=$this->formRepository->find($id);
        if(!$form) {
            throw new AppException("Participant with id=$id is not available.");
        }
        if($form->getTag()->getName()<>'participant') {
            throw new AppException("The form was not tagged as 'particpant' for form.id=$id" );
        }
    }

    /**
     * @When I request :url for info
     * @param string $url
     * @throws Exception
     */
    public function iRequestForInfo(string $url)
    {
        $request = Request::create($url);
        $request->headers->set('Authorization','Bearer '.$this->token);
        $response = $this->kernel->handle($request);
        $found = $response->getStatusCode();
        $expected = Response::HTTP_ACCEPTED;
        if($found<>$expected){
            throw new AppException("Expected status code $expected but found $found");
        }
        $this->info = json_decode($response->getContent(),true);
    }

    /**
     * @Then info has field :name
     */
    public function infoHasField($name)
    {
        if(!isset($this->info[$name])) {
            throw new AppException("Info did not contain field $name");
        }
    }

    /**
     * @Then info has multiple fields for :name
     * @throws AppException
     */
    public function infoHasMultipleFieldsFor($name)
    {
        if(!isset($this->info[$name])) {
            throw new AppException("Missing field $name in info");
        }
        if(!is_array($this->info['name'])) {
            throw new AppException("Info field should be an array");
        }
    }

    /**
     * @Then info has field :name of :option1 or :option2
     * @throws AppException
     */
    public function infoHasFieldOfOr($name, $option1, $option2)
    {
        if(!isset($this->info['name'])) {
            throw new AppException("Missing field $name in info");
        }
        if(!in_array($this->info[$name],[$option1,$option2])) {
            throw new AppException("Expected $option1 or $option2 for $name");
        }
    }
}

<?php

use App\AppException;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\User;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\UserRepository;
use App\Repository\Sales\WorkareaRepository;
use Behat\Behat\Context\Context;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/23/19
 * Time: 10:07 PM
 */

class TeamContext implements Context
{
    use TraitDatabaseInit;

    const AUTHORIZATION = 'Authorization';

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var array */
    protected $content;

    /** @var array */
    protected $headers;

    /** @var array */
    protected $body;

    /** @var KernelInterface */
    protected $kernel;

    /** @var ChannelRepository */
    private $channelRepository;

    /** @var JWTManager */
    private $tokenManager;

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

    /** @var Context */
    private $participantContext;

    /** @var Context */
    private $userContext;


    public function __construct(KernelInterface $kernel)
    {
        $container = $kernel->getContainer();
        $entityManagerSales = $container->get('doctrine.orm.sales_entity_manager');
        $entityManagerModel = $container->get('doctrine.orm.model_entity_manager');
        $this->tagRepository = $entityManagerSales->getRepository(Tag::class);
        $this->channelRepository = $entityManagerSales->getRepository(Channel::class);
        $this->userRepository = $entityManagerSales->getRepository(User::class);
        $this->workareaRepository = $entityManagerSales->getRepository(Workarea::class);
        $this->formRepository = $entityManagerSales->getRepository(Form::class);
        $this->tokenManager = $kernel->getContainer()->get('lexik_jwt_authentication.jwt_manager');
        $this->kernel = $kernel;
    }

    /**
     * @BeforeFeature
     * @param \Behat\Behat\Hook\Scope\BeforeFeatureScope $scope
     * @throws Exception
     */
    public static function beforeFeature(\Behat\Behat\Hook\Scope\BeforeFeatureScope $scope)
    {
        if ($scope->getFeature()->getFile() == "features/team.feature") {
            self::setupChannelInventory();
            self::setupUserWorkareaParticipants('georgia-dancesport','user@email.com');
        }
    }

    /** @BeforeScenario */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        $this->participantContext = $environment->getContext('ParticipantContext');
        $this->userContext = $environment->getContext('UserContext');
    }

    /**
     * @Given I have saved teams for all substyles:
     */
    public function iHaveSavedTeamsForAllSubstyles(TableNode $table)
    {

        foreach($table as $row) {
          $leftName = $row['left-name'];
          $leftPerson = $this->formRepository->findOneBy(['note'=>$leftName]);
          if(!$leftPerson) {
              throw new AppException("Form for $leftName was not found.");
          }
          $rightName = $row['right-name'];
          $rightPerson = $this->formRepository->findOneBy(['note'=>$rightName]);
          if(!$rightPerson) {
              throw new AppException("Form for $rightName was not found.");
          }
        }
    }

    /**
     * @Given the request body for team contains :personLeft and :personRight
     */
    public function theRequestBodyForTeamContainsAnd($personLeft, $personRight)
    {
        $personLeft = $this->formRepository->findOneBy(['note'=>$personLeft]);
        $personRight = $this->formRepository->findOneBy(['note'=>$personRight]);


    }

    /**
     * @When I request for team :arg1 with method :arg2
     */
    public function iRequestForTeamWithMethod($arg1, $arg2)
    {
        throw new PendingException();
    }

    /**
     * @Then the response body for team contains JSON:
     */
    public function theResponseBodyForTeamContainsJson(PyStringNode $string)
    {
        throw new PendingException();
    }

    /**
     * @Then the response body for team contains field for :arg1
     */
    public function theResponseBodyForTeamContainsFieldFor($arg1)
    {
        throw new PendingException();
    }
}

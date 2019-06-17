<?php
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use App\AppException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\Context;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/25/19
 * Time: 4:18 PM
 */

class HttpContext implements Context
{
    use TraitHttpBase;
    use TraitDatabaseInit;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /** @var DatabaseContext */
    private $databaseContext;

    private $header=[];

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }


    /** @BeforeScenario
     * @param BeforeScenarioScope $scope
     */
    public function beforeScenario(BeforeScenarioScope $scope)
    {
        $environment = $scope->getEnvironment();
        /** @noinspection PhpUndefinedMethodInspection */
        $this->databaseContext = $environment->getContext('DatabaseContext');
        #$entityManager = $this->databaseContext->getEntityManagerSales();
    }

    /**
     * @Given the request body is:
     * @param PyStringNode $node
     */
    public function theRequestBodyIs(PyStringNode $node)
    {
        $content = json_decode($node->getRaw(),true, 5);
        $this->requestBody = $content;
        if(!$this->requestBody) {
            throw new RuntimeException("Syntax error in PyStringNode.");
        }

    }

    /**
     * @When I request :url with method :method
     * @param string $url
     * @param string $method
     * @throws Exception
     */
    public function iRequestWithMethod(string $url, string $method)
    {
        $request = Request::create($url,$method,[],[],[],[],$this->requestBody);
        if($this->response && $this->response->headers->has('Authorization')){
            $request->headers->set('Authorization',$this->response->headers->get('Authorization'));
        }
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
     * @Then the response code is :expected
     * @param string $expected
     */
    public function theResponseCodeIs(string $expected)
    {
        $found=$this->response->getStatusCode();
        if($found!=$expected) {
            throw new LogicException("Status code found: $found.");
        }
    }

    /**
     * @Then the response status line is :expected
     * @param string $expected
     */
    public function theResponseStatusLineIs(string $expected)
    {
        $statusCode = $this->response->getStatusCode();
        $statusLine = Response::$statusTexts[$statusCode];
        if($statusLine!==$expected)
        {
            throw new LogicException("Expected $expected as statusLine but found $statusLine");
        }
    }


    /**
     * @Then the :key response header contains :expected
     * @param string $key
     * @param string $expected
     * @throws AppException
     */
    public function theResponseHeaderContains(string $key, string $expected)
    {
        $found = $this->response->headers->get($key);
        if($key==='Authorization') {
            $this->token = substr($found,strlen('Bearer '));
        }
        if(!$found) {
            throw new AppException("Header key: $key was not found.");
        }

        if(strpos($found,$expected)===false) {
            throw new AppException("Header format is incorrect");
        }
    }


    /**
     * @Then the response body contains JSON:
     * @param PyStringNode $node
     * @throws AppException
     */
    public function theResponseBodyContainsJson(PyStringNode $node)
    {
        $found=json_decode($this->response->getContent(),true);
        $expected =json_decode($node->getRaw(),true);
        $error = error_get_last();
        foreach($expected as $key=>$expectedValue) {
            if(!isset($found[$key])) {
                throw new AppException("No value was found for $key");
            }
            $foundValue = $found[$key];
            if($foundValue!=$expectedValue) {
                throw new AppException("Mismatch for $key for response content");
            }
        }
    }


    /**
     * @Then the response body contains JSON Array:
     * @param PyStringNode $node
     * @throws AppException
     */
    public function theResponseBodyContainsJsonArray(PyStringNode $node)
    {
        $found=json_decode($this->response->getContent(),true);
        $expected = json_decode($node->getRaw(),true);
        if($found!=$expected) {
            throw new AppException("Found JSON response does not match expected.");
        }

    }


    /**
     * @Then the response body contains :field
     * @param string $field
     * @throws AppException
     */
    public function theResponseBodyContains(string $field)
    {
        $content = json_decode($this->response->getContent(),true);
        if(!isset($content[$field])) {
            throw new AppException("The response body does not contain field $field");
        }
    }

    /**
     * @Then the response body is a JSON array of length :expectedCount
     * @param int $expectedCount
     * @throws AppException
     */
    public function theResponseBodyIsAJsonArrayOfLength(int $expectedCount)
    {
        $content = json_decode($this->response->getContent(),true);
        $foundCount = count($content);
        if($foundCount!=$expectedCount) {
            throw new AppException("Expected a list with $expectedCount elements but found $foundCount");
        }
    }


    /**
     * @Then response list entry has field :name
     * @param $name
     * @throws AppException
     */
    public function responseListEntryHasField($name)
    {
        $content = json_decode($this->response->getContent(),true);
        foreach($content as $entry) {
            if(!isset($entry[$name])) {
                throw new AppException("Expected field $name was not found in list entry");
            }
        }
    }


    /**
     * @Then the response body contains field for :name
     * @param string $name
     * @throws AppException
     */
    public function theResponseBodyContainsFieldFor(string $name)
    {
        $content = json_decode($this->response->getContent(),true);
        if(!isset($content[$name])) {
            throw new AppException("Field for $name was not found");
        }
    }


    /**
     * @Given the participant request body is:
     * @param PyStringNode $node
     */
    public function theParticipantRequestBodyIs(PyStringNode $node)
    {
        $content = json_decode($node->getRaw(),true, 5);
        $models = $content['model'];
        $content['model']=explode(',',$models);
        $this->requestBody = $content;
        if(!$this->requestBody) {
            throw new RuntimeException("Syntax error in PyStringNode.");
        }

    }





    public function getHttpRequestBody()
    {
        return $this->requestBody;
    }


    /**
     * @Then available :field in :model for :level
     * @param $field
     * @param $model
     * @param $level
     * @throws AppException
     */
    public function availableInFor($field, $model, $level)
    {
        $content = json_decode($this->response->getContent(),true);
        $selections = $content['selections'][$model];
        $missingEvents =  $this->hasFieldAtLevel($field,$selections,$level);
        if(is_array($missingEvents)){
            foreach($missingEvents as $substyle=>$found) {
                throw new AppException("Substyle $substyle has no events with $field $level");
            }
        }
    }

    private function hasFieldAtLevel(string $field, $selections, $level){
        $eventFound=[];
        foreach($selections as $substyle=>$events) {
           $eventFound[$substyle]=false;
           foreach($events as $event) {
               if($event[$field]==$level){
                   $eventFound[$substyle]=true;
               }
           }
        }
        foreach($eventFound as $substyle=>$found) {
            if(!$found) {
                return $eventFound;
            }
        }
        return true;
    }


    /**
     * @Then not available :field in :model for :level
     * @param string $field
     * @param string $model
     * @param string $level
     * @throws AppException
     */
    public function notAvailableInFor(string $field, string $model, string $level)
    {
        $content = json_decode($this->response->getContent(),true);
        $selections = $content['selections'][$model];
        $badSelections =  $this->hasNotFieldAtLevel($field,$selections,$level);
        if(is_string($badSelections)){
            throw new AppException("Substyles $badSelections should not have events with $field $level");
        }

    }

    private function hasNotFieldAtLevel(string $field, $selections, $level)
    {
        $eventFound=[];
        foreach($selections as $substyle=>$events) {
            $eventFound[$substyle]=false;
            foreach($events as $event) {
                if($event[$field]==$level){
                    $eventFound[$substyle]=true;
                }
            }
        }
        $badSelections = [];
        foreach($eventFound as $substyle=>$found) {
            if($found) {
                $badSelections[]=$substyle;
            }
        }
        return count($badSelections)?implode(',', $badSelections):false;
    }

}

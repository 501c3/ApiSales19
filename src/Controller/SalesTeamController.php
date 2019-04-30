<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/26/19
 * Time: 7:39 PM
 */

namespace App\Controller;


use App\Utils\Operation;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class SalesTeamController
 * @package App\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class SalesTeamController extends SalesBaseController
{
    const PARTICIPANT = 'participant';
    const TEAM = 'team';

    /**
     * @var Operation
     */
    private $operation;

    public function __construct(Operation $operation,
                                EntityManagerInterface $entityManager,
                                JWTTokenManagerInterface $JWTTokenManager,
                                LoggerInterface $logger)
  {

      parent::__construct($entityManager,$JWTTokenManager,$logger);
      $this->operation = $operation;
  }


    /**
     * @Route("/api/sales/team",
     *     name="api_sales_team_post",
     *     schemes={"https","http"},
     *     methods={"POST"},
     *     host="localhost")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
  public function apiPostTeam(Request $request)
  {
      list($authorization,$content,$workarea) = $this->fetchAuthorizationContentWorkarea($request);
      $teamEvents = $this->teamEvents($content['team-request']);
      $tag = $this->tagRepository->fetch('team');
      $form = $this->formRepository->post($teamEvents->toArray(),$tag,$workarea);
      $response = $this->json(['id'=>$form->getId(),
          'tag'=>'team', 'name'=>$teamEvents->toArray()],
          Response::HTTP_CREATED, ['Authorization'=>$authorization]);
      $response->prepare($request);
      return $response;
  }

  private function teamEvents(array $ids)
  {
      $infoList = [];
      foreach($ids as $id) {
          $info = $this->formRepository->fetchInfo($id);
          $infoList[]=$info;
      }
      return $this->operation->teamEvents($infoList);
  }



  public function apiDeleteTeam(Request $request)
  {

  }

  public function apiListTeam(Request $request)
  {

  }

}
<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/26/19
 * Time: 7:39 PM
 */

namespace App\Controller;


use App\AppException;
use App\Entity\Sales\Workarea;
use App\Utils\Operation;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @throws \App\AppException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
  public function apiPostTeam(Request $request) : JsonResponse
  {
      list($authorization,$content,$workarea) = $this->fetchAuthorizationContentWorkarea($request);
      $teamEvents = $this->teamEvents($content['team-request'],$workarea);
      $tag = $this->tagRepository->fetch('team');
      try{
        $form = $this->formRepository->post($teamEvents->toArray(),$tag,$workarea);
      } catch (AppException $e) {
          $_content = ['id'=>$e->priorId, 'status'=>'fail', 'tag'=>'team', 'message'=>'This team was previously defined.'];
          $response = $this->json($_content,
              Response::HTTP_FORBIDDEN, ['Authorization'=>$authorization]);
          $response->prepare($request);
          return $response;
      }
      $content = $form->getContent();
      $content['id']=$form->getId();
      $response = $this->json($content,
          Response::HTTP_CREATED, ['Authorization'=>$authorization]);
      $response->prepare($request);
      return $response;
  }

    /**
     * @param array $ids
     * @param Workarea $workarea
     * @return \App\Entity\TeamEvents|void
     * @throws \App\AppException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function teamEvents(array $ids, Workarea $workarea)
  {
      $infoList = [];
      foreach($ids as $id) {
          $info = $this->formRepository->fetchInfo($id, $workarea);
          $infoList[]=$info;
      }
      return $this->operation->teamEvents($infoList);
  }


    /**
     * @Route("/api/sales/team",
     *     name="api_sales_team_delete",
     *     schemes={"https","http"},
     *     methods={"DELETE"},
     *     host="localhost")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \App\AppException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

  public function apiDeleteTeam(Request $request): JsonResponse
  {
      list($authorization,$content,$workarea) = $this->fetchAuthorizationContentWorkarea($request);
      $status = $this->formRepository->delete($content['team-delete'],$workarea);
      $response = $this->json($status,
          Response::HTTP_OK, ['Authorization'=>$authorization]);
      $response->prepare($request);
      return $response;
  }

    /**
     * @Route("/api/sales/team",
     *     name="api_sales_team_list",
     *     schemes={"https","http"},
     *     methods={"GET"},
     *     host="localhost")*
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
  public function apiListTeam(Request $request): JsonResponse
  {
      list($authorization,,$workarea) = $this->fetchAuthorizationContentWorkarea($request);
      $tag = $this->tagRepository->fetch('team');
      $infoList=$this->formRepository->fetchArrayList($tag,$workarea);
      $response = $this->json($infoList,
          Response::HTTP_ACCEPTED,['Authorization'=>$authorization]);
      $response->prepare($request);
      return $response;
  }

}
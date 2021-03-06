<?php

namespace App\Controller;

use App\AppException;
use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SalesParticipantController
 * @package App\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class SalesParticipantController extends SalesBaseController
{

    public function __construct(EntityManagerInterface $entityManager,
                                JWTTokenManagerInterface $JWTTokenManager,
                                LoggerInterface $logger)
    {
        parent::__construct($entityManager, $JWTTokenManager, $logger);
    }


    /**
     * @Route("/api/sales/participant",
     *     name="api_sales_participant_list",
     *     schemes = {"https","http"},
     *     methods={"GET"},
     *     host="localhost")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function apiListParticipants(Request $request)
    {
        list($authorization,,$workarea) = $this->fetchAuthorizationContentWorkarea($request);
        $tag = $this->tagRepository->fetch('participant');
        $list = $this->formRepository->fetchArrayList($tag,$workarea);
        $response = $this->json($list, Response::HTTP_ACCEPTED,['Authorization'=>$authorization]);
        $response->prepare($request);
        return $response;
    }

    /**
     * @Route("/api/sales/participant",
     *     name="api_sales_participant_post",
     *     schemes={"https","http"},
     *     methods={"POST"},
     *     host="localhost")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    public function apiPostParticipant(Request $request): JsonResponse
    {
        list($authorization,$content,$workarea) = $this->fetchAuthorizationContentWorkarea($request);
        $tag = $this->tagRepository->fetch('participant');
        $form = $this->formRepository->post($content,$tag,$workarea);
        $response = $this->json(['id'=>$form->getId(),
            'tag'=>'participant', 'name'=>$content['name']],
            Response::HTTP_CREATED, ['Authorization'=>$authorization]);
        $response->prepare($request);
        return $response;
    }

    /**
     * @Route("/api/sales/participant",
     *     name="api_sales_participant_put",
     *     schemes={"https","http"},
     *     methods={"PUT"},
     *     host="localhost")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function apiPutParticipant(Request $request) : JsonResponse
    {
        list($authorization,$content) = $this->fetchAuthorizationContentWorkarea($request);
        $tag = $this->tagRepository->fetch('participant');
        $form = $this->formRepository->put($content,$tag);
        $response = $this->json([
                'id'=>$form->getId(),'tag'=>'participant','name'=>$content['name'],'message'=>'Updated'],
            Response::HTTP_CREATED, ['Authorization'=>$authorization]);
        return $response;
    }

    /**
     * @Route("/api/sales/participant/{id}",
     *     name="api_sales_participant_get",
     *     schemes={"https","http"},
     *     methods={"GET"},
     *     host="localhost")
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function apiGetParticipant(Request $request, int $id): JsonResponse
    {
        list($authorization,,$workarea)=$this->fetchAuthorizationContentWorkarea($request);
        /** @var Form $form */
        $info = $this->formRepository->fetchInfo($id,$workarea);
        $header = ['Authorization'=>$authorization];
        return $info?
            $this->json($info,Response::HTTP_ACCEPTED,$header):
            $this->json(['id'=>$id,'status'=>'fail','message'=>'Participant not found.'],Response::HTTP_NOT_FOUND, $header);
    }

    /**
     *
     * @Route("/api/sales/participant",
     *     name="api_sales_participant_get_list",
     *     schemes={"https","http"},
     *     methods={"GET"},
     *     host="localhost")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */

    public function apiGetParticipantList(Request $request): JsonResponse
    {
       list($authorization,,$workarea) = $this->fetchAuthorizationContentWorkarea($request);
       /** @var Tag $participantTag */
       $participantTag = $this->tagRepository->findOneBy(['name'=>'participant']);
       $list = $this->formRepository->fetchInfoList($participantTag,$workarea);
       /** @var JsonResponse $response */
       $response = $this->json($list,Response::HTTP_ACCEPTED,['Authorization'=>$authorization]);
       $response->prepare($request);
       return $response;
    }

    /**
     * @Route("/api/sales/participant",
     *     name="api_sales_participant_delete",
     *     schemes={"https","http"},
     *     methods={"DELETE"},
     *     host="localhost")
     *
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function apiDeleteParticipant(Request $request): JsonResponse
    {
        list($authorization,$content,$workarea)=$this->fetchAuthorizationContentWorkarea($request);
        try {

            $status = $this->formRepository->delete($content['participant-delete'], $workarea);
            $response=$this->json($status, Response::HTTP_ACCEPTED,['Authorization'=>$authorization]);
            $response->prepare($request);
            return $response;
        } catch(AppException $e){
            $status = ['status'=>'fail','message'=>$e->getMessage()];
            $response = $this->json($status,Response::HTTP_BAD_REQUEST,['Authorization'=>$authorization]);
            $response->prepare($request);
            return $response;
        }

    }
}

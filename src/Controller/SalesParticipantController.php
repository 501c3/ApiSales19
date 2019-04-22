<?php

namespace App\Controller;

use App\Entity\Sales\Form;
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

    const COMPETITION = 'competition';

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
        $authorization = $request->headers->get('Authorization');
        $channel = $this->fetchChannelFromUrl($request->getHttpHost());
        $workarea = $this->getWorkarea($authorization,self::COMPETITION,$channel);
        $tag = $this->tagRepository->fetch('participant');
        $list = $this->formRepository->fetchArrayList($tag,$workarea);
        $response = $this->json($list, Response::HTTP_ACCEPTED,['Authorization'=>$authorization]);
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
        $authorization = $request->headers->get('Authorization');
        $channel = $this->fetchChannelFromUrl($request->getHttpHost());
        /** @var array $content */
        $content = $request->getContent();
        $tag = $this->tagRepository->fetch('participant');
        $workarea = $this->getWorkarea($authorization, self::COMPETITION, $channel);
        $participant = $this->formRepository->post($content,$tag,$workarea);
        $response = $this->json(['id'=>$participant->getId(),
            'tag'=>'participant', 'name'=>[$participant->getNote()]],
            Response::HTTP_CREATED, ['Authorization'=>$authorization]);
        $response->prepare($request);
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
        $authorization = $request->headers->get('Authorization');
        $channel = $this->fetchChannelFromUrl($request->getHttpHost());
        /** @var array $content */
        $workarea = $this->getWorkarea($authorization, self::COMPETITION, $channel);
        /** @var Form $form */
        $info = $this->formRepository->fetchInfo($id,$workarea);
        $header = ['Authorization'=>$authorization];
        return $info?
            $this->json($info,Response::HTTP_ACCEPTED,$header):
            $this->json(['message'=>"information was not found for id=$id"],Response::HTTP_NOT_FOUND, $header);
    }


}

<?php

namespace App\Controller;


use App\Entity\Sales\Form;
use App\Utils\Operation;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Class SalesEntryController
 * @package App\Controller
 * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
 */
class SalesEntryController extends SalesBaseController
{
    /** @var Operation  */
    private $operation;

    public function __construct(Operation $operation,
                                EntityManagerInterface $entityManager,
                                JWTTokenManagerInterface $JWTTokenManager,
                                LoggerInterface $logger)
    {
        parent::__construct($entityManager, $JWTTokenManager, $logger);
        $this->operation = $operation;
    }


    /**
     * @Route("/api/sales/entries",
     *     name="api_sales_entries_post",
     *     schemes={"https","http"},
     *     methods={"POST"},
     *     host="localhost")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function apiPostEntries(Request $request)
    {
        list($authorization,$requestContent,$workarea) = $this->fetchAuthorizationContentWorkarea($request);
        $entries = $this->operation->getEvents($requestContent['entry-ids']);
        $formEventSelections = $this->formRepository->find($requestContent['team-id']);
        $content = $formEventSelections->getContent();
        $team = $content['team'];
        $modelEvents = $content['selections'];
        $this->markSelected($modelEvents,$requestContent['entry-ids']);
        $teamEntriesContent=['team'=>$team,'entries'=>$entries,'team-id-events'=>$formEventSelections->getId()];
        $tagEntries = $this->tagRepository->fetch('entries');
        $formEntries=$this->formRepository->post($teamEntriesContent,$tagEntries, $workarea);
        $formEventSelections->setContent(['team'=>$team,'selections'=>$modelEvents,'team-id-entries'=>$formEntries->getId()]);
        $this->formRepository->flush();
        $content['id']=$formEntries->getId();
        $status = ['id'=>$formEntries->getId(), 'status'=>'success', 'message'=>'Entries created: '.$formEntries->getNote()];
        $response = $this->json($status,
            Response::HTTP_CREATED, ['Authorization'=>$authorization]);
        $response->prepare($request);
        return $response;
    }


    /**
     * @Route("/api/sales/entries",
     *     name="api_sales_entries_put",
     *     schemes={"https","http"},
     *     methods={"PUT"},
     *     host="localhost")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function apiPutEntries(Request $request)
    {
        list($authorization, $requestContent) = $this->fetchAuthorizationContentWorkarea($request);
        /** @var Form $formEntries */
        $formEntries = $this->formRepository->find($requestContent['team-id-entries']);
        $entriesContent=$formEntries->getContent();
        $formEventSelections = $this->formRepository->find($entriesContent['team-id-events']);
        $contentEventSelections = $formEventSelections->getContent();
        $modelEvents = $contentEventSelections['selections'];
        $this->markSelected($modelEvents, $requestContent['entry-ids']);
        $contentEventSelections['selections'] = $modelEvents;
        $formEventSelections->setContent($contentEventSelections);
        $contentEntries = $formEntries->getContent();
        $entries = $this->operation->getEvents($requestContent['entry-ids']);
        $contentEntries['entries'] = $entries;
        $formEntries->setContent($contentEntries);
        $this->formRepository->flush();
        $content['id']=$formEntries->getId();
        $status = ['id'=>$formEntries->getId(), 'status'=>'success', 'message'=>'Entries revised: '.$formEntries->getNote()];
        $response = $this->json($status,
            Response::HTTP_ACCEPTED, ['Authorization'=>$authorization]);
        $response->prepare($request);
        return $response;
    }


    /**
     * @param array $modelEvents
     * @param array $idsSelected
     */
    private function markSelected(array &$modelEvents, array $idsSelected) {
        foreach($modelEvents as $model=>&$styleEvents) {
            foreach($styleEvents as $style=>&$eventList) {
                foreach($eventList as &$event) {
                    if(in_array($event['event_id'],$idsSelected)) {
                        $event['selected']=true;
                    } else {
                        $event['selected']=false;
                    }
                }
            }
        }
    }


    /**
     * @Route("/api/sales/entries",
     *     name="api_sales_entries_delete",
     *     schemes={"https","http"},
     *     methods={"DELETE"},
     *     host="localhost")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function apiDeleteEntries(Request $request): JsonResponse
    {
        list($authorization, $requestContent) = $this->fetchAuthorizationContentWorkarea($request);
        /** @var Form $formEntries */
        $teamIdEntries = $requestContent['team-id-entries'];
        $formEntries = $this->formRepository->find($teamIdEntries);
        $entriesNote = $formEntries->getNote();
        $contentEntries = $formEntries->getContent();
        $formEvents = $this->formRepository->find($contentEntries['team-id-events']);
        $contentEvents = $formEvents->getContent();
        $eventSelections = &$contentEvents['selections'];
        $this->markSelected($eventSelections,[]);
        $formEvents->setContent($contentEvents);
        $this->entityManager->remove($formEntries);
        $this->entityManager->flush();
        $status = ['id'=>$teamIdEntries, 'status'=>'success', 'message'=>'Entries removed: '.$entriesNote];
        $response = $this->json($status,
            Response::HTTP_ACCEPTED, ['Authorization'=>$authorization]);
        $response->prepare($request);
        return $response;
    }

}

<?php

namespace App\Controller;

use App\Entity\Sales\Channel;
use App\Entity\Sales\Form;
use App\Entity\Sales\Pricing;
use App\Entity\Sales\Tag;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\PricingRepository;
use App\Repository\Sales\TagRepository;
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

class SalesInvoiceController extends SalesBaseController
{
    /** @var Tag */
    private $xtrasTag;

    /** @var Tag */
    private $entriesTag;



    public function __construct(EntityManagerInterface $entityManager,
                                JWTTokenManagerInterface $JWTTokenManager,
                                LoggerInterface $logger)
    {
        parent::__construct($entityManager, $JWTTokenManager, $logger);
        /** @var TagRepository $tagRepository */
        $tagRepository = $entityManager->getRepository(Tag::class);
        $this->xtrasTag = $tagRepository->fetch('xtras');
        $this->entriesTag = $tagRepository->fetch('entries');
    }


    /**
     * @Route("/api/sales/inventory",
     *     name="api_sales_xtras_get",
     *     schemes={"https","http"},
     *     methods={"GET"},
     *     host="localhost")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function apiSalesInventory(Request $request):JsonResponse
    {
        /** @var Workarea $workarea */
        list($authorization,,$workarea) = $this->fetchAuthorizationContentWorkarea($request);
        $channel = $workarea->getChannel();
        /** @var Form $form */
        $form = $this->formRepository->findOneBy(['tag'=>$this->xtrasTag, 'workarea'=>$workarea]);
        $order = $this->orderForm($form,$channel);
        $response = $this->json($order,
            Response::HTTP_ACCEPTED, ['Authorization'=>$authorization]);
        $response->prepare($request);
        return $response;
    }

    /**
     * @param Form|null $form
     * @param Channel $channel
     * @return array
     * @throws \Exception
     */
    private function orderForm(?Form $form, Channel $channel):array
    {
        /** @var PricingRepository $pricingRepository */
        $pricingRepository = $this->entityManager->getRepository(Pricing::class);
        $pricing=$pricingRepository->getXtraPricing(new \DateTime('now'),$channel);

        $order = [];
        foreach($pricing as $name=>$price) {
            $order[$name]=['qty'=>0,'price'=>$price];
        }
        if($form) {
            $previousOrder = $form->getContent();
            foreach($previousOrder as $name=>$qty) {
                $order[$name][$qty]=$qty;
            }
        }
        return $order;
    }

    /**
     * @Route("/api/sales/xtras",
     *     name="api_sales_xtras_post",
     *     schemes={"https","http"},
     *     methods={"POST","PUT"},
     *     host="localhost")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function apiSalesXtras(Request $request): JsonResponse
    {
        list($authorization,$content,$workarea) = $this->fetchAuthorizationContentWorkarea($request);
        /** @var TagRepository $tagRepository */
        $formRepository = $this->entityManager->getRepository(Form::class);
        $user = $this->getUser();
        $content['buyer']=$user->getName();
        switch($request->getMethod()){
            case "PUT":
                /** @var Form $form */
                $form=$formRepository->put($content,$this->xtrasTag);
                $status=['id'=>$form->getId(), 'tag'=>$this->xtrasTag->getName(), 'status'=>'success','message'=>'Xtras revised'];
                /** @var JsonResponse $response */
                $response = $this->json($status,
                    Response::HTTP_CREATED, ['Authorization'=>$authorization]);
                $response->prepare($request);
                return $response;
            case "POST":
                /** @var Form $form */
                $form=$formRepository->post($content,$this->xtrasTag,$workarea);
                $status=['id'=>$form->getId(), 'tag'=>$this->xtrasTag->getName(), 'status'=>'success','message'=>'Posted xtras purchase'];
                /** @var JsonResponse $response */
                $response = $this->json($status,
                    Response::HTTP_CREATED, ['Authorization'=>$authorization]);
                $response->prepare($request);
                return $response;
        }
    }

    /**
     * @Route("/api/sales/summary",
     *    name="api_sales_summary_get",
     *    schemes={"https","http"},
     *    methods={"GET"},
     *    host="localhost")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Exception
     */
    public function apiSalesSummary(Request $request): JsonResponse
    {
        /** @var Workarea $workarea */
        list($authorization,,$workarea) = $this->fetchAuthorizationContentWorkarea($request);
        /** @var TagRepository $tagRepository */
        $formRepository = $this->entityManager->getRepository(Form::class);
        $allEntries = $formRepository->fetchArrayList($this->entriesTag,$workarea);
        $xtras = $formRepository->fetchItem($this->xtrasTag,$workarea);
        /** @var array $summary */
        $summary =  $this->organizeAndPricing($allEntries,$xtras,$workarea->getChannel());
        $response = $this->json($summary,
            Response::HTTP_ACCEPTED, ['Authorization'=>$authorization]);
        $response->prepare($request);
        return $response;
    }

    /**
     * @param $allEntries
     * @param $xtras
     * @param Channel $channel
     * @return array
     * @throws \Exception
     */
    private function organizeAndPricing($allEntries,$xtras,Channel $channel)
    {
       $date = new \DateTime('now');
       $pricingRepository = $this->entityManager->getRepository(Pricing::class);
       $participantPricing = $pricingRepository->getParticipantPricing($date,$channel);
       $xtrasPricing = $pricingRepository->getXtraPricing($date,$channel);
       $_allEntries=[]; $assessmentTotal = 0;
       foreach($allEntries as $entries) {
           list($compPrice,$examPrice) = $this->pricingFor($entries['team'],$participantPricing);
           $assessment = $this->assessEntries($entries['entries'],$compPrice,$examPrice);
           $teamName = $this->pullNameTeam($entries['team']);
           $_allEntries[$teamName] =
               ['id'=>$entries['id'],'team'=>$teamName,'events'=>$entries['entries'],'assessment'=>$assessment];
           $assessmentTotal+=$assessment;
       }
       ksort($_allEntries);
       $manifest = [];
       foreach($xtras as $item=>$qty) {
           $unitPrice=$xtrasPricing[$item];
           $ext = $unitPrice*$qty;
           $manifest[]=['item'=>$item,'qty'=>$qty,'unit-price'=>$unitPrice,'ext'=>$ext];
           $assessmentTotal+=$ext;
       }
       $summary = ['entries'=>$_allEntries, 'xtras'=>$manifest, 'total'=>$assessmentTotal];
       return $summary;
    }

    private function pricingFor($team,$pricing)
    {
        $yrs0 = intval($team[0]['years']);
        $examPrice = $pricing['exam-dance'];
        switch(count($team)){
            case 1:
                return $yrs0 & $yrs0<19??[$pricing['solo-dance-child'],$examPrice];
            case 2:
                $yrs1 = intval($team[1]['years']);
                $yrs = $yrs0?($yrs1?min($yrs0,$yrs1):$yrs0):$yrs1;

                return $yrs && $yrs<19?[$pricing['comp-dance-child'],$examPrice]:[$pricing['comp-dance-adult'],$examPrice];
        }
    }

    private function pullNameTeam(array $team)
    {
        $name0 = $this->pullNameParticipant($team[0]);
        $name1 = (count($team)==2)?' & '.$this->pullNameParticipant($team[1]):'';
        return $name0.$name1;
    }

    private function pullNameParticipant(array $participant)
    {
        return $participant['name']['last'].' '.$participant['name']['last'].'-'.$participant['form_id'];
    }

    private function assessEntries(array $entries,int $compPrice,int $examPrice): int
    {
        $compDances=0;
        $examDances=0;
        foreach($entries as $entry){
           switch($entry['model_id']){
               case 1:
                   foreach($entry['dances'] as $substyle=>$dances) {
                       $examDances+=count($dances);
                   }
                   break;
               case 2:
               case 3:
                   foreach($entry['dances'] as $substyle=>$dances) {
                      $compDances+=count($dances);
                   }
           }
        }

        $assessment = $compPrice*$compDances + $examPrice*$examDances;
        return $assessment;
    }
}

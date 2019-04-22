<?php

namespace App\Controller;

use App\AppException;
use App\Entity\Sales\Channel;
use App\Entity\Sales\User;
use App\Repository\Sales\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


/**
 * Class SalesUserController
 * @package App\Controller
 */
class SalesUserController extends SalesBaseController
{


    /** @var int */
    private $pin;

    public function __construct(
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $tokenManager,
        LoggerInterface $logger)
    {
        parent::__construct($entityManager, $tokenManager, $logger);
    }

    /**
     * @Route("/api/sales/continue",
     *         name="api_sales_login_request",
     *         methods={"POST"},
     *         host="localhost")
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     * @param Request $request
     * @return JsonResponse
     */

    public function apiContinue(Request $request) : JsonResponse
    {
        $content = json_decode($request->getContent(),true);
        $email = $content['email'];
        $user = $this->userRepository->loadUserByUsername($email);
        if(!$user) {
            return $this->json(
                ['message'=>'User not found.  Did you enter the correct email?',
                 'route'=>'/api/sales/contact'],
                Response::HTTP_NOT_FOUND);
        }
        $token = $this->JWTTokenManager->create($user);
        return $this->json(
            ['message'=>'Found your registration.  Check email for pin to access your registration.'],
            Response::HTTP_CONTINUE,
            ['Authorization'=>'Bearer '.$token]);
    }


    /**
     * @Route("/api/sales/login",
     *         name="api_sales_login",
     *         methods={"POST"},
     *         host="localhost")
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     * @param Request $request
     * @return JsonResponse
     */
    public function apiLogin(Request $request)
    {

        $content=$request->getContent();
        /** @var User $user */
        $user=$this->userRepository->loadUserByUsername($content['username']);
        if(!$user) {
            return $this->json(['message'=>'Bad username.',
                                'route'=>'/api/sales/login'],Response::HTTP_UNAUTHORIZED);
        }
        $user->eraseCredentials();
        $this->entityManager->flush();
        $token=$this->JWTTokenManager->create($user);
        return $this->json(['token'=>$token, 'id'=>$user->getId()],
            Response::HTTP_OK,['Authorization'=>'Bearer '.$token]);
    }


    // TODO: replace localhost by an environment variable.

    /**
     * @Route("/api/sales/contact",
     *        name="api_sales_contact_secure",
     *        schemes={"https","http"},
     *        methods={"PUT"},
     *        host="localhost")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return JsonResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function update(Request $request): JsonResponse
    {
        /** @var JsonResponse $response */
        $content = json_decode($request->getContent(),true);
        $this->userRepository->put($content);
        $response = $this->json(['message'=>'User information updated.'],Response::HTTP_OK);
        $response->prepare($request);
        return $response;
    }

    /**
     * User starts the registration process and a JWT is generated
     * @Route("/api/sales/register",
     *          name="api_sales_register",
     *          schemes={"https","http"},
     *          methods={"POST"},
     *          host="localhost"),
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return JsonResponse
     * @throws \Exception
     */
    public function apiRegister(Request $request, UserPasswordEncoderInterface $encoder): JsonResponse
    {
        /** @var array $content */
        $content = $request->getContent();
        /** @var Channel $channel */
        $channel = $this->channelRepository->findOneBy(['name'=>'georgia-dancesport']);
        try{
            /** @var User $user */
            $user = $this->userRepository->post($content);
        } catch(AppException $e) {
            if($e->getCode()==AppException::APP_REDUNDANT_USER){
                return $this->responseLoginSetup($encoder, $content, $this->userRepository);
            }
        }
        $token=$this->JWTTokenManager->create($user);
        $headers = ['Authorization'=> 'Bearer '.$token];
        $request->getSession()->set('jwt',$token);
        $tag=$this->tagRepository->fetch('competition');
        $this->workareaRepository->create($tag,$channel,$user);
        $result = $this->json(['id'=>$user->getId()],Response::HTTP_CREATED,$headers);
        return $result;
    }

    /**
     * @param UserPasswordEncoderInterface $encoder
     * @param array $content
     * @param UserRepository $repository
     * @return JsonResponse
     */
    private function responseLoginSetup(
        UserPasswordEncoderInterface $encoder,
        array $content,
        UserRepository $repository)
    {
        $result = $this->json([
            'message'=>"Redundant contact. Check email for security code.",
            'route'=>'/api/sales/login'],
            Response::HTTP_PERMANENTLY_REDIRECT);
        /** @var User $user */
        $user=$repository->findOneBy(['username'=>$content['email']]);
        $pin = $this->emailPin();
        $password=$encoder->encodePassword($user, $pin);
        $user->setPassword($password);
        $this->entityManager->flush();
        return $result;
    }


    private function emailPin() {
        // TODO: Email pin
        $pin = rand(1000,9999);
        $this->pin = $pin;
        return $pin;
    }
}
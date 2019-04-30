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
    /**
     * @var UserPasswordEncoderInterface
     */
    private $userPasswordEncoder;

    public function __construct(
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $tokenManager,
        UserPasswordEncoderInterface $userPasswordEncoder,
        LoggerInterface $logger)
    {
        parent::__construct($entityManager, $tokenManager, $logger);
        $this->userPasswordEncoder = $userPasswordEncoder;
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

        $pre=$request->getContent();
        $content = is_string($pre)?json_decode($pre,true):$pre;
        /** @var User $user */
        $user=$this->userRepository->loadUserByUsername($content['username']);
        if(!$user) {
            return $this->json(['message'=>'Bad username or pin',
                                'route'=>'/api/sales/login'],Response::HTTP_UNAUTHORIZED);
        }

        if (! $this->userPasswordEncoder->isPasswordValid($user,$content['password'])){
            return $this->json(['message'=>'Bad username or pin.',
                                'route'=>'/api/sales/login'], Response::HTTP_UNAUTHORIZED);
        }
        $this->entityManager->flush();
        $token=$this->JWTTokenManager->create($user);
        list($last,$first) = explode(',',$user->getName());
        $user->eraseCredentials();
        $this->entityManager->flush();
        return $this->json(['id'=>$user->getId(), 'name'=>"$first $last"],
            Response::HTTP_OK,['Authorization'=>'Bearer '.$token]);
    }


    // TODO: replace localhost by an environment variable.

    /**
     * @Route("/api/sales/contact",
     *        name="api_sales_contact",
     *        schemes={"https","http"},
     *        methods={"POST"},
     *        host="localhost")
     * @Security("is_granted('IS_AUTHENTICATED_ANONYMOUSLY')")
     * @param Request $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function apiContact(Request $request): JsonResponse
    {
        /** @var JsonResponse $response */
        $pre = $request->getContent();
        $content = is_string($pre)?json_decode($pre,true):$pre;
        /** @var User $user */
        $user=$this->userRepository->findOneBy(['username'=>$content['email']]);
        if(!$user) {
            $response = $this->json([
                'message'=>$user->getUsername().' was not found in system.  Have you registered?',
                'route'=>'/api/sales/register'
            ],Response::HTTP_NOT_FOUND);
            return $response;
        }
        // TODO: replace this pin by random number.
        // $pin = rand(1234,9876);
        $pin = 1234;
        $encryption = $this->userPasswordEncoder->encodePassword($user,$pin);
        $user->setPassword($encryption)
            ->setUpdatedAt(new \DateTime('now'));
        $this->entityManager->flush();
        // TODO: Add mailer service to send pin
        // TODO: Add text service to send pin
        $response = $this->json([
            'message'=>'A pin has been emailed to '.$user->getUsername(),
            'route'=>'/api/sales/login'
            ],Response::HTTP_OK);
        return $response;
    }

    /**
     * @Route("/api/sales/user",
     *          name="api_sales_user",
     *          schemes={"https","http"},
     *          methods={"PUT"},
     *          host="localhost"),
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param Request $request
     * @return JsonResponse
     * @throws AppException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function apiUser(Request $request)
    {
       $pre = $request->getContent();
       $content = is_string($pre)?json_decode($pre,true):$pre;
       /** @var User $user */
       $user = $this->userRepository->put($content);
       $token=$this->JWTTokenManager->create($user);
       $headers = ['Authorization'=> 'Bearer '.$token];
       $response = $this->json([
           'id'=>$user->getId(),
           'message'=>'Contact information updated',
           'route'=>'/api/sales/contact'],Response::HTTP_ACCEPTED,$headers);
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
        $pre = $request->getContent();
        $content=is_string($pre)?json_decode($pre,true):$pre;
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
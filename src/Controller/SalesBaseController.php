<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/20/19
 * Time: 7:58 PM
 */

namespace App\Controller;
use App\Entity\Sales\Channel;
use App\Entity\Sales\Form;
use App\Entity\Sales\Tag;
use App\Entity\Sales\User;
use App\Entity\Sales\Workarea;
use App\Repository\Sales\ChannelRepository;
use App\Repository\Sales\FormRepository;
use App\Repository\Sales\TagRepository;
use App\Repository\Sales\WorkareaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class SalesBaseController extends AbstractController
{
    const AUTHORIZATION = 'Authorization';

    /** @var WorkareaRepository */
    protected $workareaRepository;

    /** @var ChannelRepository  */
    protected $channelRepository;

    /** @var TagRepository */
    protected $tagRepository;

    /** @var FormRepository */
    protected $formRepository;

    /** @var \App\Repository\Sales\UserRepository */
    protected $userRepository;

    /** @var EntityManagerInterface*/
    protected $entityManager;



    /** @var LoggerInterface */
    protected $logger;
    /** @var JWTTokenManagerInterface */
    protected $JWTTokenManager;

    protected function __construct(EntityManagerInterface $entityManager,
                                   JWTTokenManagerInterface $JWTTokenManager,
                                   LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->workareaRepository = $entityManager->getRepository(Workarea::class);
        $this->channelRepository = $entityManager->getRepository(Channel::class);
        $this->tagRepository = $entityManager->getRepository(Tag::class);
        $this->formRepository = $entityManager->getRepository(Form::class);
        $this->userRepository = $entityManager->getRepository(User::class);
        $this->JWTTokenManager = $JWTTokenManager;
        $this->logger = $logger;

    }

    protected function fetchChannelFromUrl(string $hostname): ?Channel
    {
        $inDevelopment = $hostname==='localhost';
        list($channelName) = $inDevelopment?['georgia-dancesport']:explode('.',$hostname);
        return $this->channelRepository->findOneBy(['name'=>$channelName]);
    }

    /**
     * @param string $authorization
     * @param string $workareaTagName
     * @param Channel $channel
     * @return Workarea
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    protected function getWorkarea(string $authorization, string $workareaTagName, Channel $channel): Workarea
    {
        $token = substr($authorization, strlen('Bearer '));
        $tokenParts = explode(".", $token);
        $tokenPayload = base64_decode($tokenParts[1]);
        $payload = json_decode($tokenPayload,true);
        $username = $payload['username'];
        $tag = $this->tagRepository->fetch($workareaTagName);
        $user = $this->userRepository->findOneBy(['username'=>$username]);
        $workarea = $this->workareaRepository->findOneBy(['tag'=>$tag, 'user'=>$user, 'channel'=>$channel]);
        return $workarea;
    }
}
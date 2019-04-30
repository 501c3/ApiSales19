<?php
/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/11/19
 * Time: 11:36 PM
 */

namespace App\Security\Guard;


use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator as BaseAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;


class JWTTokenAuthenticator extends BaseAuthenticator
{
//    public function supports(Request $request)
//    {
//        parent::supports($request);
//    }
//
//    public function getCredentials(Request $request)
//    {
//        parent::getCredentials($request);
//    }
//
//    public function getUser($credentials, UserProviderInterface $userProvider)
//    {
//
//
//    }
//
//    public function checkCredentials($credentials, UserInterface $user)
//    {
//        return true;
//    }

//    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
//    {
//
//    }
//
//    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
//    {
//       // do nothing
//    }
//
//    public function start(Request $request, AuthenticationException $authException = null)
//    {
//
//    }
}
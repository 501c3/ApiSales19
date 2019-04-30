<?php

use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Created by PhpStorm.
 * User: mgarber
 * Date: 4/25/19
 * Time: 2:35 PM
 */

trait TraitHttpBase
{

    /** @var EntityManagerInterface */
    private $entityManagerSales;

    /** @var EntityManagerInterface */
    private $entityManagerModel;

    /** @var string */
    private $path;

    /** @var string  */
    private $method;

    /** @var Request */
    private $request;

    /** @var Response */
    private $response;

    /** @var array */
    private $requestBody;

    /** @var array */
    private $responseBody;

    /** @var string */
    private $scheme;

    /** @var JWTTokenManagerInterface */
    private $JWTTokenManager;

    /** @var string */
    private $token;

}
<?php
/**
 * cloudxxx-api (http://www.cloud.xxx)
 *
 * Copyright (C) 2014 Really Useful Limited.
 * Proprietary code. Usage restrictions apply.
 *
 * @copyright  Copyright (C) 2014 Really Useful Limited
 * @license    Proprietary
 */

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Generate session response payload
 */
$sessionData = function () use ($app)
{
    $data = [
        'endpoints' => [
            //'default' => $app['config']['baseurl'],
        ],
        'config' => [
            'env' => $app['env'],
        ],
    ];

    $token = $app['security']->getToken();

    if ($token && $app['security']->isGranted('ROLE_USER')) {
        $user = $token->getUser();

        $data['user'] = $user;
        $data['company'] = $user->getCompany();

        $data['config']['sites'] =
            $app['converter.site']->convertAll();

        $data['config']['categories'] =
            $app['converter.category']->convertAll();

        $data['config']['tags'] =
            $app['converter.tag']->convertAll();
    }

    return $app['serializer']($data, ['details', 'details.session']);
};

/**
 * Get state of session and global config
 */
$app->get('/session', function () use ($app, $sessionData)
{
    $json = $sessionData();

    if ($app['security']->isGranted('ROLE_USER')) {
        return $app->json($json);
    } else {
        return $app->json($json, 401);
    }
});

/**
 * Log In
 */
$app->post('/session', function (Request $request) use ($app, $sessionData)
{
    $httpUtils = $app['security.http_utils'];

    // check credentials

    $checkRequest = $httpUtils->createRequest($request, '/_session_check');
    $checkRequest->setMethod('POST');
    $checkRequest->request->replace([
        '_username' => $request->get('email'),
        '_password' => $request->get('password'),
    ]);

    $response = $app->handle($checkRequest, HttpKernelInterface::MASTER_REQUEST);

    // error

    if ($response->isClientError()) {
        return $response;
    }

    // success

    $json = $sessionData();
    return $app->json($json);
});

/**
 * Log In: Failure Response
 */
$app->get('/session/failure', function (Request $request) use ($app)
{
    return $app->json([
        'error' => 'invalid_grant',
        'error_description' => $app['security.last_error']($request),
    ], 400);
});

/**
 * Log Out
 */
$app->delete('/session', function (Request $request) use ($app, $sessionData)
{
    $httpUtils = $app['security.http_utils'];

    // logout

    $logoutRequest = $httpUtils->createRequest($request, '/_session_logout');

    $response = $app->handle($logoutRequest, HttpKernelInterface::MASTER_REQUEST);

    // error

    if (!$response->isRedirect()) {
        return $response;
    }

    // success

    $json = $sessionData();

    return $app->json($json);
});

/**
 * Allow OPTIONS request for CORS
 */
$app->match('/session', function () use ($app)
{
    return '';
})
->method('OPTIONS');

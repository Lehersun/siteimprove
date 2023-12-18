<?php

namespace Pixelant\PxaSiteimprove\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class StoreGoToRequestInUserSession implements MiddlewareInterface
{
    /**
     *
     *
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($GLOBALS['BE_USER'] !== null && isset($_REQUEST['tx_siteimprove_goto'])) {
            $GLOBALS['BE_USER']->setAndSaveSessionData('tx_siteimprove_goto', $_REQUEST['tx_siteimprove_goto']);
        }

        return $handler->handle($request);
    }
}

<?php
namespace FreeFW\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \Psr\Http\Message\ResponseFactoryInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \FreeFW\Http\Response;
use \FreeFW\ResourceDi;

/**
 *
 * @author jerome.klam
 *
 */
class Auth extends \FreeFW\Middleware\Base implements MiddlewareInterface
{

    /**
     * Before process
     *
     * @param ServerRequestInterface $request
     */
    protected function beforeProcess(ServerRequestInterface $request)
    {
        $di = ResourceDi::getInstance();
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface  $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        // response must be converted to correct type
        return $response;
    }
}

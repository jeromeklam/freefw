<?php
namespace FreeFW\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \Psr\Http\Message\ResponseFactoryInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
 *
 * @author jerome.klam
 *
 */
class Router implements
    MiddlewareInterface,
    \Psr\Log\LoggerAwareInterface,
    \FreeFW\Interfaces\ConfigAwareTraitInterface
{

    /**
     * comportements
     */
    use \Psr\Log\LoggerAwareTrait;
    use \FreeFW\Behaviour\EventManagerAwareTrait;
    use \FreeFW\Behaviour\ConfigAwareTrait;

    /**
     * Object
     * @var Object
     */
    protected $object = null;

    /**
     * Function
     * @var string
     */
    protected $function = null;

    /**
     * default model
     * @var string
     */
    protected $model = null;

    /**
     * Constructor
     *
     * @param object $p_object
     * @param string $p_function
     */
    public function __construct($p_object, $p_function, $p_model = null)
    {
        $this->object   = $p_object;
        $this->function = $p_function;
        $this->model    = $p_model;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * to the next middleware component to create the response.
     *
     * @param ServerRequestInterface  $p_request
     * @param RequestHandlerInterface $p_handler
     *
     * @return ResponseInterface
     */
    public function process(
        ServerRequestInterface $p_request,
        RequestHandlerInterface $p_handler
    ): ResponseInterface {
        $p_request->default_model = $this->model;
        return call_user_func_array([$this->object, $this->function], [$p_request]);
    }
}

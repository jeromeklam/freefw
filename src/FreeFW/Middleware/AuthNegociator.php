<?php
namespace FreeFW\Middleware;

use \Psr\Http\Server\MiddlewareInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

/**
 * Auth negociator
 *
 * @author jeromeklam
 */
class AuthNegociator implements
    MiddlewareInterface,
    \Psr\Log\LoggerAwareInterface,
    \FreeFW\Interfaces\ConfigAwareTraitInterface,
    \FreeFW\Interfaces\AuthAdapterInterface
{

    /**
     * Behaviour
     */
    use \Psr\Log\LoggerAwareTrait;
    use \FreeFW\Behaviour\EventManagerAwareTrait;
    use \FreeFW\Behaviour\ConfigAwareTrait;
    use \FreeFW\Behaviour\HttpFactoryTrait;

    /**
     * Formats
     * @var array
     */
    protected $formats = [
        'JWT' => [
            'class'   => 'FreeFW::Middleware::JwtAuth',
            'default' => false
        ]
    ];

    /**
     * Secured ?
     * @var bool
     */
    protected $secured = false;

    /**
     * Generate identity
     * @var boolean
     */
    protected $identity = false;

    /**
     * Constructor
     *
     * @param array $p_formats
     */
    public function __construct(array $p_formats = [])
    {
        if (count($p_formats) > 0) {
            $this->formats = $p_formats;
        }
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
        $allowed = true;
        if ($this->secured || $this->requestIdentity()) {
            $authString = trim($p_request->getHeaderLine('Authorization'));
            $class      = false;
            if ($authString != '') {
                $parts      = explode(' ', $authString);
                $authType   = strtoupper($parts[0]);
                if (array_key_exists($authType, $this->formats)) {
                    $class = $this->formats[$authType]['class'];
                } else {
                    foreach ($this->formats as $name => $format) {
                        if ($format['default']) {
                            $class = $format['class'];
                        }
                    }
                }
            }
            if ($class) {
                // Ok, encode, decode, ...
                $this->logger->debug(sprintf('FreeFW.Middleware.AuthNegociator %s', $class));
                $mid = \FreeFW\DI\DI::get($class);
                // vérify interface, ...
            } else {
                return $this->createResponse(500, "No auth class found !");
            }
        }
        return $p_handler->handle($p_request);
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\AuthAdapterInterface::isSecured()
     */
    public function isSecured(): bool
    {
        return $this->secured;
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\AuthAdapterInterface::setSecured()
     */
    public function setSecured(bool $p_secured = true)
    {
        $this->secured = $p_secured;
        return $this;
    }

    /**
     * Force identity generation
     *
     * @param bool $p_identity
     */
    public function setIdentityGeneration(bool $p_identity = true)
    {
        $this->identity = $p_identity;
        return $this;
    }

    /**
     * Get identity generation
     *
     * @return bool
     */
    public function getIndentityGeneration() : bool
    {
        return $this->identity;
    }
    
    /**
     * Request identity ?
     * 
     * @return bool
     */
    public function requestIdentity() : bool
    {
        return $this->identity;
    }
}

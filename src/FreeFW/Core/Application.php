<?php
namespace FreeFW\Core;

/**
 * Base application
 *
 * @author jeromeklam
 */
class Application implements
    \Psr\Log\LoggerAwareInterface,
    \FreeFW\Interfaces\ConfigAwareTraitInterface
{

    /**
     * comportements
     */
    use \Psr\Log\LoggerAwareTrait;
    use \FreeFW\Behaviour\EventManagerAwareTrait;
    use \FreeFW\Behaviour\ConfigAwareTrait;
    use \FreeFW\Behaviour\RequestAwareTrait;

    /**
     * Router
     * @var \FreeFW\Http\Router
     */
    protected $router = null;

    /**
     * Route
     * @var \FreeFW\Router\Route
     */
    protected $route = null;

    /**
     * Rendered ?
     * @var boolean
     */
    protected $rendered = false;

    /**
     * Constructor
     *
     * @param \FreeFW\Application\Config $p_config
     */
    protected function __construct(
        \FreeFW\Application\Config $p_config,
        \Psr\Log\LoggerInterface $p_logger
    ) {
        $this->setAppConfig($p_config);
        $this->setLogger($p_logger);
        $this->router = new \FreeFW\Http\Router();
        $this->router->setLogger($this->logger);
        $bp = $p_config->get('basepath', false);
        if ($bp !== false) {
            $this->router->setBasePath($bp);
        }
        \FreeFW\DI\DI::setShared('router', $this->router);
    }

    /**
     * Event de fin
     *
     * @return void
     */
    protected function afterRender()
    {
        if (!$this->rendered) {
            $this->logger->debug('application.afterRender.start');
            $manager = $this->getEventManager();
            $manager->notify(\FreeFW\Constants::EVENT_AFTER_RENDER);
            $this->logger->debug('application.afterRender.end');
            $this->rendered = true;
        }
        return $this;
    }

    /**
     * Return logger
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     *
     * @param \FreeFW\Router\RouteCollection $p_collection
     */
    public function addRoutes(\FreeFW\Router\RouteCollection $p_collection)
    {
        $this->router->addRoutes($p_collection);
        return $this;
    }
}

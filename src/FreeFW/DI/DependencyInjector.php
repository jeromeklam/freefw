<?php
namespace FreeFW\DI;

/**
 * Dependency injector
 *
 * @author jeromeklam
 */
class DependencyInjector extends \FreeFW\Core\DI implements \FreeFW\Interfaces\DependencyInjectorInterface
{

    /**
     * Instance
     * @var \FreeFW\DI\DependencyInjector
     */
    protected static $instances = [];

    /**
     * Base namespace
     * @var string
     */
    protected $base_ns = 'FreeFW';

    /**
     * Default storage
     * @var string
     */
    protected $default_storage = 'default';

    /**
     * Constructor
     */
    protected function __construct(
        string $p_base_ns,
        \FreeFW\Application\Config $p_config,
        \Psr\Log\LoggerInterface $p_logger,
        string $p_default_storage = 'default'
    ) {
        $this->base_ns = $p_base_ns;
        $this->setConfig($p_config);
        $this->setLogger($p_logger);
    }

    /**
     * Get instance
     *
     * @return \FreeFW\DI\DependencyInjector
     */
    public static function getFactory(
        string $p_base_ns,
        \FreeFW\Application\Config $p_config,
        \Psr\Log\LoggerInterface $p_logger,
        string $p_default_storage = 'default'
    ) {
        if (!array_key_exists($p_base_ns, self::$instances)) {
            self::$instances[$p_base_ns] = new static($p_base_ns, $p_config, $p_logger, $p_default_storage);
        }
        return self::$instances[$p_base_ns];
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\DependencyInjectorInterface::getController()
     */
    public function getController($p_name)
    {
        $class_name = '\\' . $this->base_ns . '\Controller\\' .
            \FreeFW\Tools\PBXString::toCamelCase($p_name, true);
        if (class_exists($class_name)) {
            $cls = new $class_name();
            if ($cls instanceof \Psr\Log\LoggerAwareInterface) {
                $cls->setLogger($this->logger);
            }
            if ($cls instanceof \FreeFW\Interfaces\ConfigAwareTraitInterface) {
                $cls->setConfig($this->config);
            }
            return $cls;
        }
        throw new \FreeFW\Core\FreeFWException(sprintf('Class %s not found !', $class_name));
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\DependencyInjectorInterface::getService()
     */
    public function getService($p_name)
    {
        $class_name = '\\' . $this->base_ns . '\Service\\' .
            \FreeFW\Tools\PBXString::toCamelCase($p_name, true);
        if (class_exists($class_name)) {
            $cls = new $class_name();
            if ($cls instanceof \Psr\Log\LoggerAwareInterface) {
                $cls->setLogger($this->logger);
            }
            if ($cls instanceof \FreeFW\Interfaces\ConfigAwareTraitInterface) {
                $cls->setConfig($this->config);
            }
            return $cls;
        }
        throw new \FreeFW\Core\FreeFWException(sprintf('Class %s not found !', $class_name));
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\DependencyInjectorInterface::getModel()
     */
    public function getModel($p_name)
    {
        $class_name = '\\' . $this->base_ns . '\Model\\' .
            \FreeFW\Tools\PBXString::toCamelCase($p_name, true);
        if (class_exists($class_name)) {
            $cls = new $class_name();
            $cls->init();
            if ($cls instanceof \Psr\Log\LoggerAwareInterface) {
                $cls->setLogger($this->logger);
            }
            if ($cls instanceof \FreeFW\Interfaces\StorageStrategyInterface) {
                $storage = \FreeFW\DI\DI::getShared('Storage::' . $this->default_storage);
                $cls->setStrategy($storage);
            }
            if ($cls instanceof \FreeFW\Interfaces\ConfigAwareTraitInterface) {
                $cls->setConfig($this->config);
            }
            return $cls;
        }
        throw new \FreeFW\Core\FreeFWException(sprintf('Class %s not found !', $class_name));
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\DependencyInjectorInterface::getManager()
     */
    public function getManager($p_name)
    {
        $class_name = '\\' . $this->base_ns . '\Manager\\' .
            \FreeFW\Tools\PBXString::toCamelCase($p_name, true);
        if (class_exists($class_name)) {
            $cls = new $class_name();
            if ($cls instanceof \Psr\Log\LoggerAwareInterface) {
                $cls->setLogger($this->logger);
            }
            if ($cls instanceof \FreeFW\Interfaces\ConfigAwareTraitInterface) {
                $cls->setConfig($this->config);
            }
            return $cls;
        }
        throw new \FreeFW\Core\FreeFWException(sprintf('Class %s not found !', $class_name));
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\DependencyInjectorInterface::getMiddleware()
     */
    public function getMiddleware($p_name)
    {
        $class_name = '\\' . $this->base_ns . '\Middleware\\' .
            \FreeFW\Tools\PBXString::toCamelCase($p_name, true);
        if (class_exists($class_name)) {
            $cls = new $class_name();
            if ($cls instanceof \Psr\Log\LoggerAwareInterface) {
                $cls->setLogger($this->logger);
            }
            if ($cls instanceof \FreeFW\Interfaces\ConfigAwareTraitInterface) {
                $cls->setConfig($this->config);
            }
            return $cls;
        }
        throw new \FreeFW\Core\FreeFWException(sprintf('Class %s not found !', $class_name));
    }
}

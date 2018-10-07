<?php
namespace FreeFW\Core;

/**
 * Storage model
 *
 * @author jeromeklam
 */
abstract class StorageModel extends \FreeFW\Core\Model implements
    \FreeFW\Interfaces\StorageStrategyInterface,
    \FreeFW\Interfaces\DirectStorageInterface
{

    /**
     * Storage strategy
     * @var \FreeFW\Interfaces\StorageInterface
     */
    protected $stategy = null;

    /**
     * Constructor
     *
     * @param \FreeFW\Interfaces\StorageInterface $p_strategy
     */
    public function __construct(\FreeFW\Interfaces\StorageInterface $p_strategy = null)
    {
        $this->strategy = $p_strategy;
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\StorageStrategyInterface::setStrategy()
     */
    public function setStrategy(\FreeFW\Interfaces\StorageInterface $p_strategy)
    {
        $this->strategy = $p_strategy;
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\StorageStrategyInterface::create()
     */
    public function create()
    {
        if ($this->isValid()) {
            $this->strategy->create($this);
            return $this->isValid();
        }
        return false;
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\StorageStrategyInterface::save()
     */
    public function save()
    {
        if ($this->isValid()) {
            $this->strategy->save($this);
            return $this->isValid();
        }
        return false;
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\StorageStrategyInterface::findFirst()
     */
    public static function findFirst(array $p_filters = [])
    {
        $cls   = get_called_class();
        $cls   = rtrim(ltrim($cls, '\\'), '\\');
        $query = \FreeFW\DI\DI::get('FreeFW::Model::Query');
        $query
            ->setType(\FreeFW\Model\Query::QUERY_SELECT)
            ->setMainModel(str_replace('\\', '::', $cls))
            ->setConditions($p_filters)
        ;
        $model = false;
        if ($query->execute()) {
            /**
             * @var \FreeFW\Model\ResultSet $result
             */
            $result = $query->getResult();
            if (!$result->isEmpty()) {
                $model = $result[0];
            }
        }
        return $model;
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\StorageStrategyInterface::remove()
     */
    public function remove()
    {
        return $this->strategy->remove($this);
    }

    /**
     * Set from array
     *
     * @param array $p_array
     *
     * @return \FreeFW\Core\Model
     */
    public function setFromArray($p_array)
    {
        if ($p_array instanceof \stdClass) {
            $p_array = (array)$p_array;
        }
        if (is_array($p_array)) {
            $properties = $this->getProperties();
            $fields     = [];
            foreach ($properties as $key => $prop) {
                $fields[$prop['destination']] = $key;
            }
            foreach ($p_array as $field => $value) {
                if (array_key_exists($field, $fields)) {
                    $property = $fields[$field];
                    $setter   = 'set' . \FreeFW\Tools\PBXString::toCamelCase($property, true);
                    $this->$setter($value);
                }
            }
        }
    }

    /**
     * Get new Query Model
     *
     * @param string $p_type
     *
     * @return \FreeFW\Model\Query
     */
    public static function getQuery(string $p_type = \FreeFW\Model\Query::QUERY_SELECT)
    {
        $cls   = get_called_class();
        $cls   = rtrim(ltrim($cls, '\\'), '\\');
        $query = \FreeFW\DI\DI::get('FreeFW::Model::Query');
        $query
            ->setType($p_type)
            ->setMainModel(str_replace('\\', '::', $cls))
        ;
        return $query;
    }

    /**
     *
     * {@inheritDoc}
     * @see \Serializable::serialize()
     */
    public function serialize()
    {
        $serializable = get_object_vars($this);
        unset($serializable['strategy']);
        return serialize($serializable);
    }

    /**
     *
     * {@inheritDoc}
     * @see \Serializable::unserialize()
     */
    public function unserialize($data)
    {
        $unserialized = unserialize($serialized);
        if (is_array($unserialized) === true) {
            // @todo : add strategy... from DI ?
            foreach ($unserialized as $property => $value) {
                $this->{$property} = $value;
            }
        }
    }

    /**
     * Return object source
     *
     * @return string
     */
    abstract public static function getSource();

    /**
     * Return object properties
     *
     * @return array
     */
    abstract public static function getProperties();
}

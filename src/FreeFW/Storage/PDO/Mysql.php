<?php
namespace FreeFW\Storage\PDO;

use \freeFW\Constants as FFCST;

/**
 * ...
 * @author jeromeklam
 */
class Mysql extends \PDO implements \FreeFW\Interfaces\StorageProviderInterface
{

    /**
     * comportements
     */
    use \Psr\Log\LoggerAwareTrait;
    use \FreeFW\Behaviour\EventManagerAwareTrait;

    /**
     * Transaction
     * @var boolean
     */
    protected $transaction = false;

    /**
     * Level
     * @var integer
     */
    protected $levels = 0;

    /**
     * Constructeur
     */
    public function __construct($p_dsn, $p_user, $p_password)
    {
        parent::__construct(
            $p_dsn,
            $p_user,
            $p_password,
            array(\PDO::MYSQL_ATTR_FOUND_ROWS => true)
        );
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\StorageProviderInterface::startTransaction()
     */
    public function startTransaction()
    {
        if (!$this->transaction) {
            if ($this->levels <= 0) {
                $this->transaction = $this->beginTransaction();
                $this->forwardRawEvent(FFCST::EVENT_STORAGE_BEGIN);
            }
            if ($this->transaction) {
                $this->levels = 1;
            }
        } else {
            $this->levels = $this->levels + 1;
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\StorageProviderInterface::commitTransaction()
     */
    public function commitTransaction()
    {
        if ($this->transaction) {
            $this->levels = $this->levels - 1;
            if ($this->levels <= 0) {
                $this->commit();
                if (self::inTransaction()) {
                    // No data modified or error... not normal...
                    $this->rollBack();
                    $this->forwardRawEvent(FFCST::EVENT_STORAGE_ROLLBACK);
                } else {
                    $this->forwardRawEvent(FFCST::EVENT_STORAGE_COMMIT);
                }
                $this->transaction = false;
            }
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\StorageProviderInterface::rollbackTransaction()
     */
    public function rollbackTransaction()
    {
        if ($this->transaction) {
            $this->levels = $this->levels - 1;
            if ($this->levels <= 0) {
                $this->rollBack();
                $this->forwardRawEvent(FFCST::EVENT_STORAGE_ROLLBACK);
                $this->transaction = false;
            }
        }
        return $this;
    }

    /**
     *
     * {@inheritDoc}
     * @see \FreeFW\Interfaces\StorageProviderInterface::hasSqlCalcFoundRows()
     */
    public function hasSqlCalcFoundRows()
    {
        return true;
    }
}

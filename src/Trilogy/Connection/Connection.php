<?php

namespace Trilogy\Connection;
use InvalidArgumentException;
use LogicException;
use Trilogy\Statement;

/**
 * Connection to a data source.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
class Connection implements ConnectionInterface
{
    /**
     * The default configuration.
     * 
     * @var array
     */
    public static $defaults = [
        'driver'   => 'mysql',
        'host'     => 'localhost',
        'port'     => 3306,
        'database' => 'default',
        'username' => 'root',
        'password' => '',
        'options'  => []
    ];
    
    /**
     * List of available driver classes.
     * 
     * @var array
     */
    private static $drivers = [
        'mysql' => 'Trilogy\Driver\Mysql\Mysql',
        'pgsql' => 'Trilogy\Driver\Pgsql\Pgsql',
        'sql'   => 'Trilogy\Driver\Sql\Sql'
    ];
    
    /**
     * List of avaialble statement classes.
     * 
     * @var array
     */
    private static $statements = [
        'find'   => 'Trilogy\Statement\Find',
        'save'   => 'Trilogy\Statement\Save',
        'remove' => 'Trilogy\Statement\Remove'
    ];
    
    /**
     * The instance configuration.
     * 
     * @var array
     */
    private $config = [];
    
    /**
     * The driver instance.
     *
     * @var Trilogy\Driver\DriverInterface
     */
    private $driver;
    
    /**
     * Constructs a new connection.
     * 
     * @param array $config The connection configuration.
     * 
     * @return ConnectionInterface
     */
    public function __construct(array $config = [])
    {
        // Merge the specific config with the default config.
        $this->config = array_merge(self::$defaults, $config);
        
        // Get the driver class name.
        if (isset(self::$drivers[$this->config['driver']])) {
            $this->driver = self::$drivers[$this->config['driver']];
        } else {
            throw new InvalidArgumentException(sprintf(
                'The driver "%s" does not exist.',
                $this->config['driver']
            ));
        }
    }
    
    /**
     * Returns a statement object.
     * 
     * @return StatementInterface
     */
    public function __get($stmt)
    {
        if (isset(self::$statements[$stmt])) {
            $stmt = self::$statements[$stmt];
            $stmt = new $stmt($this);
            return $stmt;
        }
        
        throw new LogicException(sprintf('The statement "%s" does not exist.', $stmt));
    }
    
    /**
     * Returns the driver.
     * 
     * @return Driver\DriverInterface
     */
    public function driver()
    {
        if (is_string($this->driver)) {
            $driver = $this->driver;
            $this->driver = new $driver($this->config);
        }
        return $this->driver;
    }

    /**
     * Begins a transaction. returns true if successful, false otherwise 
     * 
     * @return bool 
     */
    public function beginTransaction()
    {
        $this->driver()->beginTransaction();
    }
    
    /**
     * Commits the active transaction. return true if successful, false otherwise 
     * 
     * @return bool 
     */
    public function commitTransaction()
    {
        $this->driver()->commitTransaction();
    }
    
    /**
     * Perform a rollback on the active transaction. returns true if successful, false otherwise 
     *  
     * @return bool 
     */
    public function rollbackTransaction()
    {
        $this->driver()->rollbackTransaction();
    }
    
    /**
     * Gets the current status of the transaction. true = in transaction, false = no active transaction
     * 
     * @return bool
     */
    public function inTransaction() 
    {
        $this->driver()->inTransaction();
    }

    /**
     * Returns the last insert's unique ID.
     *
     * @param string $sequenceName The PostgreSQL sequence name to get the last ID from.
     *
     * @return string
     */
    public function lastInsertId($sequenceName = null)
    {
        return $this->driver()->lastInsertId($sequenceName);
    }
    
    /**
     * Registers a new driver class.
     * 
     * @param string $driver The driver name.
     * @param string $class  The driver class.
     * 
     * @return void
     */
    public static function register($driver, $class)
    {
        self::$drivers[$driver] = $class;
    }
}
<?php

namespace Trilogy\Connection;
use InvalidArgumentException;
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
        'mysql' => 'Trilogy\Driver\Mysql',
        'pgsql' => 'Trilogy\Driver\Pgsql'
    ];
    
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
            $driver = self::$drivers[$this->config['driver']];
        } else {
            throw new InvalidArgumentException(sprintf(
                'The driver "%s" does not exist.',
                $this->config['driver']
            ));
        }
        
        // Instantiate the driver class.
        $this->driver = new $driver($this->config);
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
        return $this->driver;
    }
    
    /**
     * Prepares and executes the statement and returns the result.
     * 
     * @param mixed $statement The statement to execute.
     * @param array $params    The parameters to execute the statement with.
     * 
     * @return mixed
     */
    public function execute($statement, array $params = [])
    {
        return $this->driver->execute($statement, $params);
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
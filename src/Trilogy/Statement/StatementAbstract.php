<?php

namespace Trilogy\Statement;
use ArrayObject;
use InvalidArgumentException;
use LogicException;
use PDO;
use Trilogy\Connection\ConnectionInterface;

/**
 * Handles common statement functionality.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
abstract class StatementAbstract implements StatementInterface
{
    /**
     * The connection.
     * 
     * @var ConnectionInterface
     */
    private $connection;

    /**
     * The tables associated to the statement.
     * 
     * @var array
     */
    private $tables = [];
    
    /**
     * List of conditions.
     * 
     * @var array
     */
    private $wheres = [];
    
    /**
     * The joins on the statement.
     * 
     * @var array
     */
    private $joins = [];

    /**
     * The parameters bound to the conditions.
     * 
     * @var array
     */
    private $params = [];

    /**
     * The limit associated to the statement.
     * 
     * @var int
     */
    private $limit = 0;
    
    /**
     * The offset associated to the statement.
     * 
     * @var int
     */
    private $offset = 0;

    /**
     * The number of open brackets.
     * 
     * @var int
     */
    private $open = 0;
    
    /**
     * The current mode. Value is either "where" or "join".
     * 
     * @var string
     */
    private $mode;
    
    /**
     * The current concatenator used for conditions.
     * 
     * @var string
     */
    private $concatenator = 'and';

    /**
     * Constructs a new statement.
     * 
     * @param ConnectionInterface $connection The connection to use.
     * 
     * @return StatementAbstract
     */
    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Sets the current concatenator.
     * 
     * @param string $name The name of the concatenator.
     * 
     * @return StatementInterface
     */
    public function __get($expression)
    {
        // change of concatenator to "and" or "or"
        if ($expression === 'and' || $expression === 'or') {
            $this->concatenator = $expression;
            return $this;
        }
        
        return $this;
    }
    
    /**
     * Renders the statement as a string.
     * 
     * @return string
     */
    public function __toString()
    {
        $method = get_class($this);
        $method = explode('\\', $method);
        $method = 'compile' . end($method);
        return $this->connection->driver()->$method($this);
    }
    
    /**
     * Executes the statement.
     * 
     * @return mixed
     */
    public function execute()
    {
        return $this->connection()->execute($this, $this->params);
    }
    
    /**
     * Applies a where condition to the current mode ("where" or "join").
     * 
     * @return StatementAbstract
     */
    public function where($expression, $value = null)
    {
        $expr = new Expression($expression);
        
        $where = [
            'concatenator' => $this->concatenator,
            'expression'   => $expr,
            'open'         => $this->open,
            'close'        => 0
        ];
        
        if ($this->mode === 'join') {
            $this->joins[count($this->joins) - 1]['wheres'][] = $where;
        } else {
            $this->wheres[] = $where;
        }
        
        if ($expr->bindable()) {
            $this->params[] = $value;
        }
        
        if ($this->open) {
            $this->open = 0;
        }
        
        return $this;
    }
    
    /**
     * Adds a join to the statement.
     * 
     * @param string $table The table to join.
     * @param string $type  The type of join.
     * 
     * @return StatementAbstract
     */
    public function join($table, $type = 'inner')
    {
        $this->mode = 'join';
        
        $this->joins[] = [
            'table'  => $table,
            'type'   => $type,
            'wheres' => []
        ];
        
        return $this;
    }

    /**
     * Sets which tables are associated to the statement.
     * 
     * @return StatementInterface
     */
    public function in($tables)
    {
        if (is_string($tables)) {
            $tables = [$tables];
        }

        $this->tables = array_merge($this->tables, $tables);
        $this->tables = array_unique($this->tables);

        return $this;
    }

    /**
     * Opens one or more brackets.
     * 
     * @param int $amt The number of brackets to open.
     * 
     * @return StatementInterface
     */
    public function open($amt = 1)
    {
        $this->open += $amt;
        return $this;
    }

    /**
     * Closes one or more brackets.
     * 
     * @param int $amt The number of brackets to close.
     * 
     * @return StatementInterface
     */
    public function close($amt = 1)
    {
        $this->wheres[count($this->wheres) - 1]['close'] += $amt;
        return $this;
    }
    
    /**
     * Limits the number of items the statement affects.
     * 
     * @param int $limit  The limit.
     * @param int $offset The offset.
     * 
     * @return StatementAbstract
     */
    public function limit($limit, $offset = 0)
    {
        $this->limit  = $limit;
        $this->offset = $offset;
        return $this;
    }
    
    /**
     * Limits using pagination, the number of items the statement affects.
     * 
     * @param int $limit The limit.
     * @param int $page  The page.
     * 
     * @return StatementAbstract
     */
    public function page($limit, $page = 1)
    {
        $this->limit  = $limit;
        $this->offset = ($limit * $page) - $limit;
        return $this;
    }
    
    /**
     * Returns the affected tables.
     * 
     * @return array
     */
    public function getTables()
    {
        return $this->tables;
    }
    
    /**
     * Returns the applied conditions.
     * 
     * @return array
     */
    public function getWheres()
    {
        return $this->wheres;
    }
    
    /**
     * Returns the applied joins.
     * 
     * @return array
     */
    public function getJoins()
    {
        return $this->joins;
    }
    
    /**
     * Returns the parameter values bound to the condition expressions.
     * 
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
    
    /**
     * Sets the parameters to use.
     * 
     * @param array $params The parameters to set.
     * 
     * @return StatementAbstract
     */
    public function setParams(array $params)
    {
        $this->params = $params;
        return $this;
    }
    
    /**
     * Returns the applied limit.
     * 
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }
    
    /**
     * Returns the applied offset.
     * 
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }
    
    /**
     * Returns the associated connection.
     * 
     * @return ConnectionInterface
     */
    public function connection()
    {
        return $this->connection;
    }
}
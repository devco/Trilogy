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
     * The "AND" concatenator.
     * 
     * @var string
     */
    const CONCAT_AND = 'AND';
    
    /**
     * The "OR" concatenator.
     * 
     * @var string
     */
    const CONCAT_OR = 'OR';
    
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
        return $this->connection()->execute($this, $this->getParams());
    }
    
    /**
     * Applies a where condition to the current mode ("where" or "join").
     * 
     * @param string $expression The expression to use.
     * @param mixed  $value      The value to bind to the expression.
     * @param string $concat     The concatenator to use: "and" or "or".
     * 
     * @return StatementAbstract
     */
    public function where($expression, $value = null, $concat = self::CONCAT_AND)
    {
        // Handle an array of expressions.
        if (is_array($expression)) {
            foreach ($expression as $name => $value) {
                $this->where($name, $value);
            }
            return $this;
        }
        
        // Parse out the expression.
        $expr = new Expression($expression);
        $expr->setBoundValue($value);
        
        // Build clause information.
        $where = [
            'concatenator' => $concat,
            'expression'   => $expr,
            'open'         => $this->open,
            'close'        => 0,
        ];
        
        // If we are in "join" mode, add a join, otherwise just add a "where".
        if ($this->mode === 'join') {
            $this->joins[count($this->joins) - 1]['wheres'][] = $where;
        } else {
            $this->wheres[] = $where;
        }
        
        // If we are in brackets, reset.
        if ($this->open) {
            $this->open = 0;
        }
        
        return $this;
    }
    
    /**
     * Applies a where condition to the current mode ("where" or "join").
     * 
     * @param string $expression The expression to use.
     * @param mixed  $value      The value to bind to the expression.
     * 
     * @return StatementAbstract
     */
    public function andWhere($expression, $value = null)
    {
        return $this->where($expression, $value, self::CONCAT_AND);
    }
    
    /**
     * Applies a where condition to the current mode ("where" or "join").
     * 
     * @param string $expression The expression to use.
     * @param mixed  $value      The value to bind to the expression.
     * 
     * @return StatementAbstract
     */
    public function orWhere($expression, $value = null)
    {
        return $this->where($expression, $value, self::CONCAT_OR);
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
        $params = [];
        
        foreach (['wheres', 'joins'] as $type) {
            foreach ($this->$type as $part) {
                if ($part['expression']->isBindable()) {
                    $params[] = $part['expression']->getBoundValue();
                }
            }
        }
        
        return $params;
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
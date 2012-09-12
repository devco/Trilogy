<?php

namespace Trilogy\Statement;
use ArrayObject;
use BadMethodCallException;
use InvalidArgumentException;
use LogicException;
use PDO;
use Trilogy\Connection\ConnectionInterface;
use Trilogy\Statement\Expression\Table;
use Trilogy\Statement\Part\Join;
use Trilogy\Statement\Part\Where;

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
     * When the statement is in "join" mode.
     * 
     * @var string
     */
    const MODE_JOIN = 'join';
    
    /**
     * The method called when proxying andWhere().
     * 
     * @var string
     */
    const METHOD_AND = 'and';
    
    /**
     * The method called when proxying orWhere().
     * 
     * @var string
     */
    const METHOD_OR = 'or';
    
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
     * Calls "and" or "or" and proxies throught "where".
     * 
     * @param string $name The method name.
     * @param array  $args The method args.
     * 
     * @return StatementAbstract
     */
    public function __call($name, array $args)
    {
        if ($name === self::METHOD_AND || $name === self::METHOD_OR) {
            return $this->where($args[0], isset($args[1]) ? $args[1] : null, $name);
        }
        
        throw new BadMethodCallException(sprintf('Call to undefined method "%s".', $name));
    }
    
    /**
     * Renders the statement as a string.
     * 
     * @return mixed
     */
    public function compile()
    {
        return $this->connection->driver()->compile($this);
    }
    
    /**
     * Executes the statement.
     * 
     * @return mixed
     */
    public function execute()
    {
        return $this->connection->driver()->execute($this);
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
    public function where($where, $value = null, $concat = self::CONCAT_AND)
    {
        // Handle an array of expressions.
        if (is_array($where)) {
            foreach ($where as $name => $value) {
                $this->where($name, $value);
            }
            return $this;
        }
        
        // Ensure an instance of the Where part.
        if (!$where instanceof Where) {
            $where = new Where($where, $value, $concat, $this->open);
        }
        
        // If we are in "join" mode, add a join, otherwise just add a "where".
        if ($this->mode === 'join') {
            $this->joins[count($this->joins) - 1]->addWhere($where);
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
    public function join($table, $type = Join::INNER)
    {
        $this->mode    = self::MODE_JOIN;
        $this->joins[] = new Join($table, $type);
        return $this;
    }

    /**
     * Sets which tables are associated to the statement.
     * 
     * @param array | string $tables The table or tables to affect.
     * 
     * @return StatementInterface
     */
    public function in($tables)
    {
        foreach ((array) $tables as $table) {
            $this->tables[] = $table instanceof Table ? $table : new Table($table);
        }
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
        if ($this->mode === self::MODE_JOIN) {
            $where = $this->joins[count($this->joins) - 1]->getLastWhere();
        } else {
            $where = $this->wheres[count($this->wheres) - 1];
        }
        
        $where->setCloseBrackets($where->getCloseBrackets() + $amt);
        
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
        
        foreach ($this->getWheres() as $where) {
            if ($where->getClause()->isBindable()) {
                $params[] = $where->getValue();
            }
        }
        
        foreach ($this->getJoins() as $join) {
            foreach ($join->getWheres() as $where) {
                if ($where->getClause()->isBindable()) {
                    $params[] = $where->getValue();
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
}

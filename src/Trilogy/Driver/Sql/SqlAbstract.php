<?php

namespace Trilogy\Driver\Sql;
use Exception;
use LogicException;
use PDO;
use Trilogy\Statement\Expression;
use Trilogy\Statement\Part;
use Trilogy\Statement;

/**
 * Common driver behavior.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
abstract class SqlAbstract implements SqlInterface
{
    /**
     * The PDO instance.
     * 
     * @var PDO
     */
    private $pdo;
    
    /**
     * List of custom operators.
     * 
     * @var array
     */
    private $operators = [
        '~'  => 'LIKE',
        '!~' => 'NOT LIKE',
        '*'  => 'IN',
        '!*' => 'NOT IN'
    ];
    
    /**
     * Makes a connection to the database.
     * 
     * @return SqlDriverAbstract
     */
    public function __construct(array $config)
    {
        $dsn = $config['driver']
            . ':dbname=' . $config['database']
            . ';host=' . $config['host']
            . ';port=' . $config['port'];
        
        $this->pdo = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            $config['options']
        );

        // Always throw exceptions on failed queries.
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        return $this;
    }
    
    /**
     * Returns the raw PDO connection.
     * 
     * @return PDO
     */
    public function raw()
    {
        return $this->pdo;
    }
    
    /**
     * Executes the statement using PDO.
     * 
     * @param Statement\StatementInterface $statement The statement to execute.
     * 
     * @return mixed
     */
    public function execute(Statement\StatementInterface $stmt)
    {
        // Return true if not a select statement and the statement does not fail.
        $return = true;
        
        // Prepare statement.
        $pdoStmt = $this->pdo->prepare($this->compile($stmt));

        // Compile all parameters from the statement.
        $params = $this->getParametersFromStatement($stmt);

        //flag to record whether or not we're in a transaction for an insert statement
        $inInsertTransaction = false;
        
        if (! $this->pdo->inTransaction() && $stmt instanceof Statement\Save) {
            $inInsertTransaction = $this->pdo->beginTransaction();
        }
        
        // Get the PDO result to read.
        try {
            $pdoResult = $pdoStmt->execute($params);
        } catch (Exception $e) {
            throw new LogicException(sprintf(
                'Could not execute query "%s" with params "%s". Exception Message: %s',
                $pdoStmt->queryString,
                var_export($params, true),
                $e->getMessage()
            ));
        }
        
        // Return an associative array if it is a find statement.
        if ($stmt instanceof Statement\Find) {
            $pdoResult = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        //if we're in an insert transaction, then return the new id
        if ($inInssertTransaction) {
            $pdoResult = $this->pdo->lastInsertId();
            $this->pdo->commit();            
        }
        
        // Ensure the cursor is closed (some drivers require this)
        $pdoStmt->closeCursor();
        
        // Return either a result set or true.
        return $pdoResult;
    }
    
    /**
     * Compiles the passed in statement.
     * 
     * @param Statement\StatementInterface $stmt The statement to compile.
     * 
     * @return string
     */
    public function compile(Statement\StatementInterface $stmt)
    {
        if ($stmt instanceof Statement\Find) {
            return $this->compileFind($stmt);
        }
        
        if ($stmt instanceof Statement\Save) {
            return $this->compileSave($stmt);
        }
        
        if ($stmt instanceof Statement\Remove) {
            return $this->compileRemove($stmt);
        }
        
        return '';
    }
    
    /**
     * Quotes the specified identifier.
     * 
     * @param string $identifier The identifier to quote.
     * 
     * @return string
     */
    public function quote($identifier)
    {
        if ($this->isReservedWord($identifier)) {
            return $identifier;
        }
        
        $identifiers = explode('.', (string) $identifier);
        
        foreach ($identifiers as &$identifier) {
            if ($identifier !== '*') {
                $identifier = '"' . $identifier . '"';
            }
        }
        
        return implode('.', $identifiers);
    }
    
    /**
     * Quotes all identifiers in the array and returns them.
     * 
     * @param array $identifiers The identifiers to quote.
     * 
     * @return array
     */
    public function quoteAll(array $identifiers)
    {
        $arr = [];
        foreach ($identifiers as $id) {
            $arr[] = $this->quote($id);
        }
        return $arr;
    }

    /**
     * Orders and returns an array of parameters for a given statement.
     * 
     * @param Statement\StatementInterface $stmt The statement to get the bound parameters for.
     * 
     * @return array
     */
    public function getParametersFromStatement(Statement\StatementInterface $stmt)
    {
        $params = [];

        // Save parameter parts.
        if ($stmt instanceof Statement\Save) {
            $params = array_merge($params, $stmt->getData());
        }

        // Where clause parameters. Array values are merged
        // because they are part of an * operator.
        foreach ($stmt->getWheres() as $where) {
            $value = $where->getValue();

            if (is_array($value)) {
                $params = array_merge($params, $value);
            } else {
                $params[] = $value;
            }
        }

        // Only get certain parameters for certain types of statements.
        if ($stmt instanceof Statement\Find) {
            if ($limit = $stmt->getLimit()) {
                $params = array_merge($params, [$stmt->getLimit(), $stmt->getOffset()]);
            }
        }

        // We need to remove all null values since they are
        // transformed into "IS NULL" or "IS NOT NULL" tokens.
        $params = array_filter($params, function($value) {
            return !is_null($value);
        });

        // Return a re-indexed array so that
        // positions are not out of order.
        return array_values($params);
    }
    
    /**
     * Compiles a find statement.
     * 
     * @param Statement\Find $find The find statement.
     * 
     * @return string
     */
    public function compileFind(Statement\Find $find)
    {
        $sqls = [];
        
        $sqls[] = $this->compileSelect($find);
        $sqls[] = $this->compileFrom($find);
        
        if ($sql = $this->compileJoin($find)) {
            $sqls[] = $sql;
        }        
        
        if ($sql = $this->compileWhere($find)) {
            $sqls[] = $sql;
        }
        
        if ($sql = $this->compileOrderBy($find)) {
            $sqls[] = $sql;
        }
        
        if ($sql = $this->compileLimit($find)) {
            $sqls[] = $sql;
        }
        
        return implode(' ', $sqls);
    }
    
    /**
     * Compiles a save statement.
     * 
     * @param Statement\Save $save The save statement.
     * 
     * @return string
     */
    public function compileSave(Statement\Save $save)
    {
        if ($save->getWheres()) {
            return $this->compileUpdate($save);
        }
        return $this->compileInsert($save);
    }
    
    /**
     * Compiles a remove statement.
     * 
     * @param Statement\Remove $remove The remove statement.
     * 
     * @return string
     */
    public function compileRemove(Statement\Remove $remove)
    {
        return sprintf(
            'DELETE FROM %s %s',
            $this->compileSourceDefinitions($remove),
            $this->compileWhere($remove)
        );
    }
    
    /**
     * Compiles an insert statement.
     * 
     * @param Statement\Save $save The save statement.
     * 
     * @return string
     */
    public function compileInsert(Statement\Save $save)
    {
        // Compile field definition.
        $fields = array_keys($save->getData());
        $fields = $this->quoteAll($fields);
        
        // Compile value definition.
        $values = str_repeat('?', count($fields));
        $values = str_split($values);
        
        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->compileSourceDefinitions($save),
            implode(', ', $fields),
            implode(', ', $values)
        );
    }
    
    /**
     * Compiles an update statement.
     * 
     * @param Statement\ave $save The save statement.
     * 
     * @return string
     */
    public function compileUpdate(Statement\Save $save)
    {
        $fields = array_keys($save->getData());
        
        foreach ($fields as &$field) {
            $field = $this->quote($field) . ' = ?';
        }
        
        $values = str_repeat('?', count($fields));
        $values = str_split($values);
        
        return sprintf(
            'UPDATE %s SET %s %s',
            $this->compileSourceDefinitions($save),
            implode(', ', $fields),
            $this->compileWhere($save)
        );
    }
    
    /**
     * Compiles the SELECT part of a find statement.
     * 
     * @param Statement\Find $find The find statement to compile the select part for.
     * 
     * @return string
     */
    public function compileSelect(Statement\Find $find)
    {
        if ($find->getFields()) {
            $fields = $this->compileFieldDefinitions($find);
        } else {
            $fields = '*';
        }
        
        return 'SELECT ' . $fields;
    }
    
    /**
     * Compiles the FROM part of a find statement.
     * 
     * @param Statement\StatementInterface $stmt The statement to compile.
     * 
     * @return string
     */
    public function compileFrom(Statement\StatementInterface $stmt)
    {
        return 'FROM ' . $this->compileSourceDefinitions($stmt);
    }
    
    /**
     * Compiles the WHERE part of a statement.
     * 
     * @param Statement\StatementInterface $$stmt The statement to compile.
     * 
     * @return string
     */
    public function compileWhere(Statement\StatementInterface $stmt)
    {
        if ($sql = $this->compileWhereParts($stmt->getWheres())) {
            return 'WHERE ' . $sql;
        }
    }
    
    /**
     * Compiles the JOIN part of a find statement.
     * 
     * @param Statement\Find $find The find statement to compile the joins for.
     * 
     * @return string
     */
    public function compileJoin(Statement\Find $find)
    {
        $sql = '';
        
        foreach ($find->getJoins() as $join) {
            $source = $this->compileSourceDefinition($join->getSource());
            
            $sql .= sprintf(
                '%s JOIN %s ON %s',
                strtoupper($join->getType()),
                $source,
                $this->compileWhereParts($join->getWheres())
            );
        }
        
        return $sql;
    }
    
    /**
     * Compiles an array of expressions into a WHERE or JOIN clause.
     * 
     * @param array $wheres The where parts to compile.
     * 
     * @return string
     */
    public function compileWhereParts(array $wheres)
    {
        $sql = '';
        
        foreach ($wheres as $where) {
            $sql .= $this->compileWherePart($where);
        }
        
        $sql = preg_replace('/^([(]*)\s*(AND|OR)\s*/', '$1', $sql);
        $sql = trim($sql);
        
        return $sql;
    }
    
    /**
     * Compiles a single expression.
     * 
     * @param Part\Where $where The where to compile.
     * 
     * @return string
     */
    public function compileWherePart(Part\Where $where)
    {
        // The actual clause expression in the where part.
        $clause = $where->getClause();
        
        // Operator translation.
        $op = $clause->getOperator();
        $op = isset($this->operators[$op]) ? $this->operators[$op] : $op;
        
        // Concatenator: "AND" or "OR".
        $concat = $where->getConcatenator();
        
        // Brackets.
        $open  = str_repeat('(', $where->getOpenBrackets());
        $close = str_repeat(')', $where->getCloseBrackets());
        
        // Field definition.
        $field = $this->quote($clause->getField());
        
        // Value definition.
        $value = $clause->getValue();
        $value = $this->quote($value);
        
        // If the value is bindable, and null is passed, we convert to IS NULL, or IS NOT NULL
        // depending on what operator is passed in.
        if ($clause->isBindable() && $where->getValue() === null) {
            $value = $op === '=' ? 'IS NULL' : 'IS NOT NULL';
            $op    = null;
        // Handle "IN" operators.
        } elseif (($op === 'NOT IN' || $op === 'IN') && $value === '?') {
            $where->setValue((array) $where->getValue());
            $value = $where->getValue();
            $value = str_repeat('?', count($value));
            $value = str_split($value);
            $value = implode(', ', $value);
            $value = '(' . $value . ')';
        }
        
        // Build the expression.
        return $concat 
            . ' '
            . $open
            . $field
            . ' '
            . $op
            . ($op ? ' ' : '')
            . $value
            . $close
            . ' ';
    }
    
    /**
     * Compiles the ORDER BY part of a find statement.
     * 
     * @param array  $fields    The fields to order by.
     * @param string $direction The sort direction.
     * 
     * @return string
     */
    public function compileOrderBy(Statement\Find $stmt)
    {
        $fields = $stmt->getSorts();
        
        if (!$fields) {
            return;
        }
        
        $parts = [];
        
        foreach ($fields as $field => $direction) {
            $parts[] = $this->quote($field) .' '. $direction;
        }
        
        return 'ORDER BY ' . implode(', ', $parts);
    }

    /**
     * All DBs handle limiting differently.
     * 
     * @param int $limit  The limit.
     * @param int $offset The offset.
     * 
     * @return string
     */
    public function compileLimit(Statement\StatementInterface $stmt)
    {
        if (!$stmt->getLimit()) {
            return;
        }

        return 'LIMIT ?, ?';
    }
    
    /**
     * Compiles and returns multiple table definitions.
     * 
     * @param Statement\StatementInterface $stmt The statement to compile.
     * 
     * @return string
     */
    public function compileSourceDefinitions(Statement\StatementInterface $stmt)
    {
        $compiled = [];
        
        foreach ($stmt->getSources() as $source) {
            $compiled[] = $this->compileSourceDefinition($source);
        }
        
        return implode(', ', $compiled);
    }
    
    /**
     * Compiles and returns a table definition.
     * 
     * @param Expression\Source $source The source expression.
     * 
     * @return string
     */
    public function compileSourceDefinition(Expression\Source $source)
    {
        $def = $this->quote($source->getSource());
        
        if ($alias = $source->getAlias()) {
            $def .= ' ' . $this->quote($alias);
        }
        
        return $def;
    }
    
    /**
     * Compiles and returns multiple field definitions.
     * 
     * @param Statement\StatementInterface $$statement The statement to compile.
     * 
     * @return string
     */
    public function compileFieldDefinitions(Statement\Find $find)
    {
        $compiled = [];
        
        foreach ($find->getFields() as $field) {
            $compiled[] = $this->compileFieldDefinition($field);
        }
        
        return implode(', ', $compiled);
    }
    
    /**
     * Compiles and returns a field definition.
     * 
     * @param Expression\Field $field The field expression.
     * 
     * @return string
     */
    public function compileFieldDefinition(Expression\Field $field)
    {
        $def = $this->quote($field->getField());
        
        if ($alias = $field->getAlias()) {
            $def .= ' AS ' . $this->quote($alias);
        }
        
        return $def;
    }
    
    /**
     * Returns whether or not the word is reserved.
     * 
     * @param string $word The word to check.
     * 
     * @return bool
     */
    public function isReservedWord($word)
    {
        $words = ['?', 'true', 'false', 'null'];
        return in_array(strtolower($word), $words);
    }
}

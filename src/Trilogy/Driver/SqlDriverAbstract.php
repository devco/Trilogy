<?php

namespace Trilogy\Driver;
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
abstract class SqlDriverAbstract implements SqlDriverInterface
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
        '~' => 'LIKE'
    ];
    
    /**
     * All DBs handle limiting differently.
     * 
     * @param Statement\StatementInterface $stmt The statement to compile the limit for.
     * 
     * @return string
     */
    protected abstract function compileLimit(Statement\StatementInterface $stmt);
    
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

        // Get the PDO result to read.
        $pdoResult = $result = $pdoStmt->execute($this->getParametersFromStatement($stmt));

        // Ensure the statement can be executed.
        if (!$pdoResult) {
            $error = $pdoStmt->errorInfo();
            throw new LogicException(sprintf('Query failed - %s:%s - %s - %s', $error[0], $error[1], $error[2], $pdoStmt->queryString));
        }
        
        // Return an associative array if it is a find statement.
        if ($stmt instanceof Statement\Find) {
            $return = $pdoStmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        // Ensure the cursor is closed (some drivers require this)
        $pdoStmt->closeCursor();
        
        // Return either a result set or true.
        return $return;
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
    private function getParametersFromStatement(Statement\StatementInterface $stmt)
    {
        if ($stmt instanceof Statement\Find) {
            return array_merge($stmt->getWhereParams(), $stmt->getJoinParams());
        }

        if ($stmt instanceof Statement\Save) {
            return array_merge($stmt->getData(), $stmt->getWhereParams());
        }

        if ($stmt instanceof Statement\Remove) {
            return $stmt->getWhereParams();
        }

        return [];
    }
    
    /**
     * Compiles a find statement.
     * 
     * @param Statement\Find $find The find statement.
     * 
     * @return string
     */
    private function compileFind(Statement\Find $find)
    {
        $sqls = [];
        
        $sqls[] = $this->compileSelect($find);
        $sqls[] = $this->compileFrom($find);
        
        if ($sql = $this->compileWhere($find)) {
            $sqls[] = $sql;
        }
        
        if ($sql = $this->compileJoin($find)) {
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
    private function compileSave(Statement\Save $save)
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
    private function compileRemove(Statement\Remove $remove)
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
    private function compileInsert(Statement\Save $save)
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
    private function compileUpdate(Statement\Save $save)
    {
        $fields = array_keys($save->getData());
        
        foreach ($fields as &$field) {
            $field = 'SET ' . $this->quote($field) . ' = ?';
        }
        
        $values = str_repeat('?', count($fields));
        $values = str_split($values);
        
        return sprintf(
            'UPDATE %s %s %s',
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
    private function compileSelect(Statement\Find $find)
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
    private function compileFrom(Statement\StatementInterface $stmt)
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
    private function compileWhere(Statement\StatementInterface $stmt)
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
    private function compileJoin(Statement\Find $find)
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
    private function compileWhereParts(array $wheres)
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
    private function compileWherePart(Part\Where $where)
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
    private function compileOrderBy(Statement\Find $stmt)
    {
        $fields = $stmt->getSorts();
        
        if (!$fields) {
            return;
        }
        
        $parts = [];
        
        foreach ($fields as $field => $direction) {
            $parts[] = $this->quote($field) . ' ' . $direction;
        }
        
        return 'ORDER BY ' . implode(', ', $parts);
    }
    
    /**
     * Compiles and returns multiple table definitions.
     * 
     * @param Statement\StatementInterface $stmt The statement to compile.
     * 
     * @return string
     */
    private function compileSourceDefinitions(Statement\StatementInterface $stmt)
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
    private function compileSourceDefinition(Expression\Source $source)
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
    private function compileFieldDefinitions(Statement\Find $find)
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
    private function compileFieldDefinition(Expression\Field $field)
    {
        $def = $this->quote($field->getField());
        
        if ($alias = $field->getAlias()) {
            $def .= ' ' . $this->quote($alias);
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
    private function isReservedWord($word)
    {
        $words = ['?', 'true', 'false', 'null'];
        return in_array(strtolower($word), $words);
    }
}
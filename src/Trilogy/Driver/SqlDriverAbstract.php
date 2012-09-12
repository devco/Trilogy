<?php

namespace Trilogy\Driver;
use LogicException;
use PDO;
use Trilogy\Statement\Expression\Clause;
use Trilogy\Statement\Expression\Field;
use Trilogy\Statement\Expression\Table;
use Trilogy\Statement\Find;
use Trilogy\Statement\Part\Join;
use Trilogy\Statement\Part\Where;
use Trilogy\Statement\Remove;
use Trilogy\Statement\Save;
use Trilogy\Statement\StatementInterface;

/**
 * Common driver behavior.
 * 
 * @category Iterators
 * @package  Trilogy
 * @author   Trey Shugart <treshugart@gmail.com>
 * @license  MIT http://opensource.org/licenses/mit-license.php
 */
abstract class SqlDriverAbstract implements DriverInterface
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
     * @param int $limit  The limit.
     * @param int $offset The offset.
     * 
     * @return string
     */
    protected abstract function compileLimit($limit, $offset);

    /**
     * Creates a DSN from the configuration.
     * 
     * @return string
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
     * @param mixed $statement The statement to prepare, execute and return the result of.
     * @param array $params    The parameters to execute the statement with.
     * 
     * @return mixed
     */
    public function execute($statement, array $params = [], $style = PDO::FETCH_ASSOC)
    {
        // Return true if not a select statement and the statement does not fail.
        $return = true;
        
        // Prepare statement.
        $statement = $this->pdo->prepare((string) $statement);
        
        // Ensure the statement can be executed.
        if (!$statement->execute($params)) {
            $error = $statement->errorInfo();
            throw new LogicException(sprintf('Query failed - %s:%s - %s', $error[0], $error[1], $error[2]));
        }
        
        // Return an associative array if it is a SELECT statement.
        if (strpos($statement->queryString, 'SELECT ') === 0) {
            $return = $statement->fetchAll($style);
        }
        
        // Ensure the cursor is closed (some drivers require this)
        $statement->closeCursor();
        
        // Return either a result set or true.
        return $return;
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
    
    public function quoteAll(array $identifiers)
    {
        $arr = [];
        foreach ($identifiers as $id) {
            $arr[] = $this->quote($id);
        }
        return $arr;
    }
    
    /**
     * Compiles the passed in statement.
     * 
     * @param StatementInterface $stmt The statement to compile.
     * 
     * @return string
     */
    public function compile(StatementInterface $stmt)
    {
        if ($stmt instanceof Find) {
            return $this->compileFind($stmt);
        }
        
        if ($stmt instanceof Save) {
            return $this->compileSave($stmt);
        }
        
        if ($stmt instanceof Remove) {
            return $this->compileRemove($stmt);
        }
        
        throw new LogicException(sprintf('Unrecognized statement instance "%s".', get_class($stmt)));
    }
    
    /**
     * Compiles a find statement.
     * 
     * @param Find $find The find statement.
     * 
     * @return string
     */
    public function compileFind(Find $find)
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
        
        if ($sql = $this->compileOrderBy($find->getSortFields(), $find->getSortDirection())) {
            $sqls[] = $sql;
        }
        
        if ($sql = $this->compileLimit($find->getLimit(), $find->getOffset())) {
            $sqls[] = $sql;
        }
        
        return implode(' ', $sqls);
    }
    
    /**
     * Compiles a save statement.
     * 
     * @param Save $save The save statement.
     * 
     * @return string
     */
    public function compileSave(Save $save)
    {
        if ($save->getWheres()) {
            return $this->compileUpdate($save);
        }
        return $this->compileInsert($save);
    }
    
    /**
     * Compiles a remove statement.
     * 
     * @param Remove $remove The remove statement.
     * 
     * @return string
     */
    public function compileRemove(Remove $remove)
    {
        return sprintf(
            'DELETE FROM %s %s',
            $this->compileTableDefinitions($remove),
            $this->compileWhere($remove)
        );
    }
    
    /**
     * Compiles an insert statement.
     * 
     * @param Save $save The save statement.
     * 
     * @return string
     */
    protected function compileInsert(Save $save)
    {
        // Compile field definition.
        $fields = $save->getFields();
        $fields = $this->quoteAll($fields);
        
        // Compile value definition.
        $values = str_repeat('?', count($fields));
        $values = str_split($values);
        
        return sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->compileTableDefinitions($save),
            implode(', ', $fields),
            implode(', ', $values)
        );
    }
    
    /**
     * Compiles an update statement.
     * 
     * @param Save $save The save statement.
     * 
     * @return string
     */
    protected function compileUpdate(Save $save)
    {
        $fields = array_keys($save->getData());
        
        foreach ($fields as &$field) {
            $field = 'SET ' . $this->quote($field) . ' = ?';
        }
        
        $values = str_repeat('?', count($fields));
        $values = str_split($values);
        
        return sprintf(
            'UPDATE %s %s %s',
            $this->compileTableDefinitions($save),
            implode(', ', $fields),
            $this->compileWhere($save)
        );
    }
    
    /**
     * Compiles the SELECT part of a find statement.
     * 
     * @param array $fields The fields to select.
     * 
     * @return string
     */
    protected function compileSelect(Find $find)
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
     * @param StatementInterface $stmt The statement to compile.
     * 
     * @return string
     */
    protected function compileFrom(StatementInterface $stmt)
    {
        return 'FROM ' . $this->compileTableDefinitions($stmt);
    }
    
    /**
     * Compiles the WHERE part of a statement.
     * 
     * @param StatementInterface $$stmt The statement to compile.
     * 
     * @return string
     */
    protected function compileWhere(StatementInterface $stmt)
    {
        if ($sql = $this->compileWhereParts($stmt->getWheres())) {
            return 'WHERE ' . $sql;
        }
    }
    
    /**
     * Compiles the JOIN part of a query.
     * 
     * @param array $joins The joins to compile.
     * 
     * @return string
     */
    protected function compileJoin(Find $find)
    {
        $sql = '';
        
        foreach ($find->getJoins() as $join) {
            $table = $this->compileTableDefinition($join->getTable());
            
            $sql .= sprintf(
                '%s JOIN %s ON %s',
                strtoupper($join->getType()),
                $table,
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
    protected function compileWhereParts(array $wheres)
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
     * @param Where $where The where to compile.
     * 
     * @return string
     */
    protected function compileWherePart(Where $where)
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
    protected function compileOrderBy(array $fields, $direction)
    {
        if (!$fields) {
            return;
        }
        
        $fields = $this->quoteAll($fields);
        $fields = implode(', ', $fields);
        
        return 'ORDER BY ' . $fields . ' ' . $direction;
    }
    
    /**
     * Compiles and returns multiple table definitions.
     * 
     * @param StatementInterface $$stmt The statement to compile.
     * 
     * @return string
     */
    private function compileTableDefinitions(StatementInterface $stmt)
    {
        $compiled = [];
        
        foreach ($stmt->getTables() as $table) {
            $compiled[] = $this->compileTableDefinition($table);
        }
        
        return implode(', ', $compiled);
    }
    
    /**
     * Compiles and returns a table definition.
     * 
     * @param Table $table The table expression.
     * 
     * @return string
     */
    private function compileTableDefinition(Table $table)
    {
        $def = $this->quote($table->getTable());
        
        if ($alias = $table->getAlias()) {
            $def .= ' ' . $this->quote($alias);
        }
        
        return $def;
    }
    
    /**
     * Compiles and returns multiple field definitions.
     * 
     * @param StatementInterface $$statement The statement to compile.
     * 
     * @return string
     */
    public function compileFieldDefinitions(Find $find)
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
     * @param Field $field The field expression.
     * 
     * @return string
     */
    private function compileFieldDefinition(Field $field)
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
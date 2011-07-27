<?php
/**
 * Orion abstract model class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
abstract class OrionModel
{
    const DEBUG = false;
	
	/**
	 * Slug used to alias joined fields in JOIN queries to ease object conversion in SELECT queries.<br />
	 * ie: A valid alias should look like : L_FIELD_SLUG . $fieldlink . L_FIELD_SEP . $fieldname<br />
	 * With $fieldlink, the name of the field usef for linkage and $fieldname the joined field.
	 */
	const L_FIELD_SLUG = 'linkedfield_';
	/**
	 * Alias separator for JOIN queries.
	 * See L_FIELD_SLUG
	 */
	const L_FIELD_SEP = '__';
    /**
     * Ascending order
     */
    const ASCENDING = 'ASC';
    /**
     * Descending order
     */
    const DESCENDING = 'DESC';

    /**
     * Class name
     * @var string
     */
    protected $CLASS_NAME = 'OrionModel';

    /**
     * Columns placeholder for select, update queries.
     * @var array<string>
     */
    private $_COLUMNS = array();
    /**
     * Keys for save
     * @var array<string>
     */
    private $_KEYS=null;
    /**
     * Values for save
     * @var array<string>
     */
    private $_VALUES=null;
    /**
     * Array of key/values to update
     * @var array<array<key, value>>
     */
    private $_SETS=null;
    /**
     * Where clause placeholder
     * @var array<string>[3] Example: array('field', 'LIKE', '%token%');
     */
    private $_WHERE = array();
    /**
     * Manual where clause placeholder. Used for complex where clauses.
     * <p><b>Be careful when using manual where clause because the string is not parsed, nor escaped. It is ut in the query 'AS IS'.</b></p>
     * @var string string the complete where clause, without the WHERE keyword
     */
    private $_MWHERE=null;
    /**
     * AND where clause placeholder
     * @var string AND where clause
     */
    private $_AWHERE=array();
    /**
     * OR where clause placeholder
     * @var string OR where clause
     */
    private $_OWHERE=array();
    /**
     * Order clause placeholder
     * @var array[2]
     */
    private $_ORDER = array();
    /**
     * Limit clause placeholder
     * @var int
     */
    private $_LIMIT=null;
    /**
     * Offset clause placeholder
     * @var int
     */
    private $_OFFSET=null;
    /**
     * Bounded table name
     * @var string
     */
    private $_TABLE=null;
    /**
     * The type of the query
     * @var string
     */
    private $_TYPE=null;
    /**
     * Joined links
     * @var array<string> Array of linked fields' name
     */
    private $_JOIN=array();
	/**
	 * Joined fields data. <br />
	 * <b>The field alias must be internally defined using L_FIELD_SLUG and L_FIELD_SEP</b>
	 * @var array<array[3]<string>> Each row correspond to a linked field, consisting in an array with [0] being the field name, [1] the field alias and [2] the linked object's class name for object convertion.
	 */	 
	private $_JOIN_COLUMNS=array();
    /**
     * Bounded fields placeholder
     * @var OrionModelField[]
     */
    private $_FIELDS = array();
    /**
     * Array of primary keys
     * @var array<string>
     */
    private $_PRIMARY = array();
    /**
     * Bounded class placeholder
     * @var <type>
     */
    private $_CLASS;
    /**
     * PDO query result
     * @var PDOStatement
     */
    private $_QUERY=null;
    /**
     * SQL query string for backtracing
     * @var string
     */
    private $_QUERY_STRING=null;
    /**
     * Internal query result keeper
     * @var mixed
     */
    private $_RESULT=null;
    /**
     * PDO instance from OrionSql class
     * @var Object(PDO)
     */
    private $_PDO=null;
    /**
     * Last joined table name
     * @var string
     */
    private $_LAST_JOINED_TABLE=null;

    public function  __construct($bindOnLoad=true)
    {
        if($bindOnLoad) $this->bindAll();
    }

    /**
     * Abstract function to be overridden in child class
     * @abstract
     */
    abstract protected function bindAll();

    /**
     * Bind a class object to use with PDO->fetch();
     * @param string $classname
     */
    protected function bindClass($classname)
    {
        $this->_CLASS = $classname;
    }

    /**
     * Bind a SQL table to current model
     * @param string table name
     */
    protected function bindTable($tablename)
    {
        $this->_TABLE = $tablename;
    }

    /**
     * Bind an object field to a SQL field
     * @param OrionModelField $field
     */
    protected function bind($field)
    {
        $this->_FIELDS[$field->getBinding()] = $field;
        if($field->isPrimary()) array_push($this->_PRIMARY, $field->getBinding());
    }

    /**
     * Start a select query chain
     * @param mixed Either select('f1','f2', ...) or select(array('f1','f2',...))
     */
    public function &select($data=null)
    {
        $this->_TYPE = 'select';

        if(func_num_args() == 0 || $data == null)
            $this->_COLUMNS = array('*');
        else
        {
            if(is_array($data))
                $cols = $data;
            else
                $cols = func_get_args();
            $this->_COLUMNS = $this->tablePrefixArray($this->escapeArray($cols), $this->_TABLE);
        }
        
        return $this;
    }

    /**
     * Ends a query chain and returns the resulting rows
     * @return array<BoundedClass>
     */
    public function fetchAll()
    {
        try {
            if($this->_PDO == null)
                $this->_PDO = OrionSql::getConnection();

            $this->_QUERY = $this->_PDO->query($this->getQuery());
            $this->_RESULT = $this->_QUERY->fetchAll(PDO::FETCH_CLASS, $this->_CLASS);
			
			if(!empty($this->_JOIN)) $this->parseJoinFieldsArray($this->_RESULT);
        }
        catch(OrionException $e)
        {
            throw $e;
        }
        catch(PDOException $e)
        {
            throw new OrionException($e->getMessage(), $e->getCode(), get_class($this));
        }


        return $this->_RESULT;
    }

    /**
     * Ends a query chain and returns the resulting row
     * @return BoundedClass
     */
    public function fetch()
    {
        try {
            if($this->_PDO == null)
                $this->_PDO = OrionSql::getConnection();

            $this->_QUERY = $this->_PDO->query($this->getQuery());
            $this->_RESULT = $this->_QUERY->fetchObject($this->_CLASS);
			
			if(!empty($this->_JOIN)) $this->parseJoinFields($this->_RESULT);
        }
        catch(OrionException $e)
        {
            throw $e;
        }
        catch(PDOException $e)
        {
            throw new OrionException($e->getMessage(), $e->getCode(), get_class($this));
        }


        return $this->_RESULT;
    }

    /**
     * Retreive model object from POST var
     * @return Object
     */
    public function fetchPostData()
    {
        $class = $this->_CLASS;
        $object = new $class();

        foreach(array_keys($this->_FIELDS) as $field)
            if(isset($_POST[$field])) $object->{$field} = $_POST[$field];

        return $object;
    }

    /**
     * Query chain element, limiting the query to a given $size
     * @param int $size
     */
    public function &limit($size)
    {
        if(empty($size) || $size == 0)
            throw new OrionException('Empty or null size limit.', E_USER_WARNING, get_class($this));

        $this->_LIMIT = $size;

        return $this;
    }

    /**
     * Query chain element, setting the starting offset
     * @param int $start
     */
    public function &offset($start)
    {
        if(is_null($start))
            throw new OrionException('Missing parameter in offset.', E_USER_WARNING, get_class($this));

        $this->_OFFSET = $start;

        return $this;
    }

    /**
     * Query chain element, setting ordering clause
     * @param mixed $fields either an array of fields or a single field
     * @param string $mode 'ASC'|'DESC'
     */
    public function &order($fields, $mode)
    {
        if(empty($fields) || empty($mode))
            throw new OrionException('Missing parameter in order clause.', E_USER_WARNING, get_class($this));

        if(is_array($fields))
            $fields = $fields;
        else
            $fields = $fields;

        $this->_ORDER = array($fields, $mode);

        return $this;
    }

    /**
     * Query chain element, defining where clause
     * @param string $field
     * @param string $comparator
     * @param mixed $value <b>If no wildcard should be used, pass this parameter using OrionTools::escapeSql($value).</b> Other standard quotes escapes are done internally.
     */
    public function &where($field, $comparator=null, $value=null)
    {
        if($comparator == null && $value == null)
            return $this->manualWhere($field);

        if(empty($field) || empty($comparator) || $value == null)
            throw new OrionException('Missing argument in where clause', E_USER_WARNING, get_class($this));

        /*if(!array_key_exists($field, $this->_FIELDS))
            throw new OrionException('Where clause field ['.$field.'] is not a valid bounded field.', E_USER_WARNING, get_class($this));
        */
        if(array_key_exists($field, $this->_FIELDS))
            $this->_WHERE = array($field, $comparator, $this->_FIELDS[$field]->prepare($this->escape($value)));
        else
            $this->_WHERE = array($field, $comparator, "'".$this->escape($value)."'");
        
        return $this;
    }

    /**
     * Query chain element, defining AND where clause
     * @param string $field
     * @param string $comparator
     * @param mixed $value <b>If no wildcard should be used, pass this parameter using OrionTools::escapeSql($value).</b> Other standard quotes escapes are done internally.
     */
    public function &andWhere($field, $comparator=null, $value=null)
    {
        if(is_null($field) || is_null($comparator) || is_null($value))
            throw new OrionException('Missing argument in AND where clause', E_USER_WARNING, get_class($this));

        if(array_key_exists($field, $this->_FIELDS))
            $this->_AWHERE[] = array($field, $comparator, $this->_FIELDS[$field]->prepare($this->escape($value)));
        else
            $this->_AWHERE[] = array($field, $comparator, "'".$this->escape($value)."'");

        return $this;
    }

    /**
     * Query chain element, defining AND where clause
     * @param string $field
     * @param string $comparator
     * @param mixed $value <b>If no wildcard should be used, pass this parameter using OrionTools::escapeSql($value).</b> Other standard quotes escapes are done internally.
     */
    public function &orWhere($field, $comparator=null, $value=null)
    {
        if(is_null($field) || is_null($comparator) || is_null($value))
            throw new OrionException('Missing argument in OR where clause', E_USER_WARNING, get_class($this));

        if(array_key_exists($field, $this->_FIELDS))
            $this->_OWHERE[] = array($field, $comparator, $this->_FIELDS[$field]->prepare($this->escape($value)));
        else
            $this->_OWHERE[] = array($field, $comparator, "'".$this->escape($value)."'");

        return $this;
    }

    /**
     * Query chain element, defining where clause manualy. Used for complex where clauses
     * <p><b>Be careful when using manual where clause because the string is not parsed, nor escaped. It is used in the query 'AS IS'.</b></p>
     * @param string the complete where clause, without the WHERE keyword
     */
    public function &manualWhere($clause)
    {
        if(!is_string($clause))
            throw new OrionException('Manual where parameter ['.$clause.'] is not a complete <string> where clause.', E_USER_WARNING, get_class($this));

        $this->_MWHERE = $clause;

        return $this;
    }
	
	/**
	 * Query chain element, joining provided $fields on $link.
	 * @param string $link
     * @param string $field
     * @param string $type [LEFT|RIGHT|INNER|OUTER]
	 */
	public function &join($link, $fields, $type='LEFT')
	{
		if(!array_key_exists($link, $this->_FIELDS) || !$this->_FIELDS[$link]->isLinked())
			throw new OrionException('Cannot join ['.$link.'], field is not linked in model.', E_USER_WARNING, get_class($this));
		
		if($fields == null || !is_array($fields))
			throw new OrionException('Missing array of joined fields while trying to join on ['.$link.'].', E_USER_WARNING, get_class($this));
			
		$jhClass = $this->_FIELDS[$link]->getModel();

		$jh = new $jhClass();

        $this->_LAST_JOINED_TABLE = $jh->getTable();
		
		// build joined fields array with field aliases 
		foreach($fields as $field)
		{
			$field = $field;
			$this->_JOIN_COLUMNS[] = array($this->tablePrefix($field, $jh->getTable()), self::L_FIELD_SLUG.$link.self::L_FIELD_SEP.$field);
		}
		$this->_JOIN[$link] = array($jh->getTable(), $this->escape($type));
		
		return $this;
	}

    /**
     * Save provided object into database, checking validity of model constraints.
     * @param Object $object Object to save
     * @return boolean
     */
    public function save($object=null)
    {
		if($object == null) 
            throw new OrionException('Cannot save an empty object.', E_USER_WARNING, get_class($this));
			
        if(empty($this->_FIELDS))
            throw new OrionException('No field bound in model', E_USER_WARNING, get_class($this));

        try {
            $data = get_object_vars($object);

            $keys = array();
            $values = array();

            foreach($data as $key => $value)
            {
				$value = $this->escape($value);
				$key = $this->escape($key);

                if(!array_key_exists($key, $this->_FIELDS))
                    continue;

				if(!$this->_FIELDS[$key]->validate($value))
                    throw new OrionException('Impossible to save object to database. Value does not meets field ['.$key.'] requirements.', E_USER_WARNING, get_class($this));

				$this->_FIELDS[$key]->onSave($value);

                $value = $this->_FIELDS[$key]->prepare($value);
				if($this->_FIELDS[$key]->isEmptyValue($value)) continue;

                array_push($keys, $key);
                array_push($values, $value);
            }

            $this->_TYPE = 'insert';
            $this->_KEYS = $keys;
            $this->_VALUES = $values;

            if($this->_PDO == null)
                $this->_PDO = OrionSql::getConnection();
            
            $result = $this->_PDO->exec($this->getQuery());
        }
        catch(OrionException $e)
        {
            throw $e;
        }
        catch(PDOException $e)
        {
            throw new OrionException($e->getMessage(), $e->getCode(), get_class($this));
        }

        if ($result === false) throw new OrionException('Save query failed to execute. Usage of DEBUG mode is recommended.', E_USER_ERROR, get_class($this));

        return (!($result === false));
    }

    /**
     * Update provided object into database, checking validity of model constraints.
     * @param Object $object Object to update
     * @return boolean
     */
    public function update($object=null)
    {
		if($object == null) 
            throw new OrionException('Cannot update an empty object.', E_USER_WARNING, get_class($this));
		
        if(empty($this->_FIELDS))
            throw new OrionException('No field bound in model', E_USER_WARNING, get_class($this));

        try {
            $data = get_object_vars($object);

            $sets = array();
            $wheres = array();

            foreach($this->_PRIMARY as $key)
            {
                if(empty($object->{$key}))
                    throw new OrionException('Primary key ['.$key.'] value not provided in object to update.', E_USER_WARNING, get_class($this));

                array_push($wheres, $key.'='.$this->_FIELDS[$key]->prepare($this->escape($object->{$key})));
            }

            if(empty($wheres))
                throw new OrionException('Impossible to update. No primary field found in update object.', E_USER_WARNING, get_class($this));

            $oClass = get_class($this);
			$oh = new $oClass();
			$oldData = $oh->select()
					  ->manualWhere(implode(' AND ', $wheres))
					  ->limit(1)
					  ->fetch();
			$oh->flush();

            foreach($data as $key => $value)
            {
				$value = $this->escape($value);
				$key = $this->escape($key);

                if(!array_key_exists($key, $this->_FIELDS))
                    continue;

				if(!$this->_FIELDS[$key]->validate($value))
                    throw new OrionException('Impossible to update object in database. Value does not meets field ['.$key.'] requirements.', E_USER_WARNING, get_class($this));

                $this->_FIELDS[$key]->onUpdate($value, $oldData->{$key});
				
                $value = $this->_FIELDS[$key]->prepare($value);
				if($this->_FIELDS[$key]->isEmptyValue($value)) continue;

				array_push($sets, $key.'='.$value);
            }

            $this->_TYPE = 'update';
            $this->_SETS = $sets;
            $this->_LIMIT = 1;
            $this->manualWhere(implode(' AND ', $wheres));

            if($this->_PDO == null)
                $this->_PDO = OrionSql::getConnection();
        
            $result = $this->_PDO->exec($this->getQuery());
        }
        catch(OrionException $e)
        {
            throw $e;
        }
        catch(PDOException $e)
        {
            throw new OrionException($e->getMessage(), $e->getCode(), get_class($this));
        }

        if ($result === false) throw new OrionException('Udpate query failed to execute. Usage of DEBUG mode is recommended.', E_USER_ERROR, get_class($this));

        return (!($result === false));
    }

    /**
     * Execute a delete action on database.
	 * Must be the last function of a query chain.
     * @return boolean
     */
    public function delete($object=null)
    {
        if($object == null)
            throw new OrionException('Cannot delete an empty object.', E_USER_WARNING, get_class($this));

        if(empty($this->_FIELDS))
            throw new OrionException('No field bound in model', E_USER_WARNING, get_class($this));

        try {
            $wheres = array();

            foreach($this->_PRIMARY as $key)
            {
                if(empty($object->{$key}))
                    throw new OrionException('Primary key ['.$key.'] value not provided in object to delete.', E_USER_WARNING, get_class($this));

                array_push($wheres, $key.'='.$this->_FIELDS[$key]->prepare($this->escape($object->{$key})));
            }

            $this->_TYPE = 'delete';
            $this->_LIMIT = 1;
            $this->manualWhere(implode(' AND ', $wheres));

			$oClass = get_class($this);
			$oh = new $oClass();
			$oldData = $oh->select()
					  ->manualWhere(implode(' AND ', $wheres))
					  ->limit(1)
					  ->fetch();
			$oh->flush();

			foreach($this->_FIELDS as $key => $field)
			{
				$field->onDelete($oldData->{$key});
			}

            if($this->_PDO == null)
                $this->_PDO = OrionSql::getConnection();

            $result = $this->_PDO->exec($this->getQuery());
        }
        catch(OrionException $e)
        {
            throw $e;
        }
        catch(PDOException $e)
        {
            throw new OrionException($e->getMessage(), $e->getCode(), get_class($this));
        }


        if ($result === false) throw new OrionException('Delete query failed to execute. Usage of DEBUG mode is recommended.', E_USER_ERROR, get_class($this));
    
        return (!($result === false));
    }

    /**
     * Get current query string
     * @return string current query string
     */
    protected function getQuery()
    {
        if($this->_TABLE == null)
            throw new OrionException('Unable to perform query. Table not bound.', E_USER_WARNING, get_class($this));

        if($this->_MWHERE != null)
            $where = $this->_MWHERE;
        else
        {
            if(!empty($this->_WHERE) && is_array($this->_WHERE))
            {
                $where = implode(' ', $this->_WHERE);
                if(!empty($this->_AWHERE))
                {
                    foreach($this->_AWHERE as $andClause)
                        $where .= ' AND '.implode(' ', $andClause);
                }
                if(!empty($this->_OWHERE))
                {
                    foreach($this->_OWHERE as $orClause)
                        $where .= ' OR '.implode(' ', $orClause);
                }
            }
            else
                $where=null;
        }

        switch($this->_TYPE)
        {
            case 'select':
                $query = "SELECT ";
                $query .= implode(', ', $this->_COLUMNS);
				foreach($this->_JOIN_COLUMNS as $col)
					$query .= ", ".implode(' AS ', $col);
                $query .= " FROM ".$this->_TABLE;
				foreach($this->_JOIN as $key => $data)
					$query .= " ".strtoupper($data[1])." JOIN ".$data[0]." ON ".$this->tablePrefix($this->_FIELDS[$key]->getBinding(), $this->_TABLE)."=".$this->tablePrefix($this->_FIELDS[$key]->getRightfield(), $data[0]);
                if($where != null)
                    $query .= " WHERE ".$where;
                if(!empty($this->_ORDER))
                    $query .= " ORDER BY ".implode(' ', $this->_ORDER);
                if($this->_LIMIT != null)
                    $query .= " LIMIT ".$this->_LIMIT;
                if($this->_OFFSET != null)
                    $query .= " OFFSET ".$this->_OFFSET;
            break;

            case 'insert':
                $query = "INSERT INTO ".$this->_TABLE;
                $query .= " (".implode(', ', $this->_KEYS).") VALUES (".implode(', ', $this->_VALUES).")";
            break;

            case 'update':
                $query = "UPDATE ".$this->_TABLE;
                $query .= " SET ".implode(', ', $this->_SETS);

                if($where == null)
                   throw new OrionException('Update query must have a where clause.', E_USER_WARNING, get_class($this));
                else
                    $query .= " WHERE ".$where;

                if($this->_LIMIT != null)
                    $query .= " LIMIT ".$this->_LIMIT;
            break;

            case 'delete':
                $query = 'DELETE FROM '.$this->_TABLE;

                if($where == null)
                   throw new OrionException('Delete query must have a where clause.', E_USER_WARNING, get_class($this));
                else
                    $query .= " WHERE ".$where;

                if($this->_LIMIT != null)
                    $query .= " LIMIT ".$this->_LIMIT;
            break;

            default:
                throw new OrionException('Unknown query type.', E_USER_WARNING, get_class($this));
            break;
        }

        $this->_QUERY_STRING = $query;

        if(self::DEBUG) echo 'SQL_QUERY: '.$query.'<br />';

        return $query.';';
    }

    /**
     * Resets current model
     */
    public function flush()
    {
        if($this->_QUERY instanceof PDOStatement)
            $this->_QUERY->closeCursor();
        
        $this->_COLUMNS = array();
        $this->_KEYS=null;
        $this->_VALUES=null;
        $this->_SETS=null;
        $this->_WHERE = array();
        $this->_AWHERE = array();
        $this->_OWHERE = array();
        $this->_MWHERE=null;
        $this->_ORDER = array();
        $this->_LIMIT=null;
        $this->_OFFSET=null;
        $this->_TABLE=null;
        $this->_TYPE=null;
        $this->_JOIN=null;
        $this->_QUERY=null;
        $this->_QUERY_STRING=null;
        $this->_RESULT=null;
    }

    /**
     * Escapes string for mysql usage ('\\', "\0", "\n", "\r", "'", '"', "\x1a")
     * @param string $inp
     * @return string
     */
    public function escape($inp)
    {
        if(is_array($inp))
            throw new OrionException('Use escapeArray() to escape arrays, not escape().', E_USER_ERROR, get_class($this));

        if(!empty($inp) && is_string($inp)) {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $inp);
        }

        return $inp;
    }

    /**
     * Maps the standard $this->escape to the elements of $array
     * @param array<mixed> $array Array to escape
     * @return array<mixed> Escaped array
     */
    public function escapeArray( $array )
	{
		$tmp = array();
		$count = count($array);
		for($i=0; $i<$count; $i++)
		{
			$tmp[$i] = $this->escape($array[$i]);
		}

		return $tmp;
	}

     /**
     * Format a value for SQL use
     * @param mixed $value
     * @return string SQL value formatted
     */
    public function format($value)
    {
        if($value == null)
            return "''";
        else
            return "'".$value."'";
    }

	/**
     * Maps the standard $this->format to the elements of $array
     * @param array<mixed> $array Array to format
     * @return array<mixed> Escaped array
     */
	public function formatArray($array)
	{
		$tmp = array();
		$count = count($array);
		for($i=0; $i<$count; $i++)
		{
			$tmp[$i] = $this->format($array[$i], $type);
		}

		return $tmp;
	}

	/**
	 * Add a table prefix to a field name
	 * @param string $field field name
	 * @param string $table table name
	 * @return string prefixed field name
	 */
	public function tablePrefix($field, $table)
	{
		return $table.'.'.$field;
	}
	
    /**
     * Add a table prefix to an array of columns (ex: array('a','b') & table1 gives array('table1.a', 'table1.b'))
     * @param array $array Array of columns
     * @param string $table The table prefix
     * @return array
     */
    public function tablePrefixArray($array, $table)
    {
        $tmp = array();
		$count = count($array);
		for($i=0; $i<$count; $i++)
		{
			$tmp[$i] = $this->tablePrefix($array[$i], $table);
		}

		return $tmp;
    }

	/**
	 * Parse query-resulting object to build linked objects from joined fields' syntax.<br />
	 * In other words, convert "L_FIELD_SLUG.$linkedfield.L_FIELD_SEP.$fieldname" into linked Object.
	 */
	protected function parseJoinFields(&$object)
	{
		$lfields = OrionTools::extractArrayKeysStartingWith(get_object_vars($object), self::L_FIELD_SLUG);
		$l_field_len = strlen(self::L_FIELD_SLUG);
		foreach($lfields as $key => $value)
		{
			$tmp = explode(self::L_FIELD_SEP, substr($key, $l_field_len));
			$field = $tmp[0];
			$subfield = $tmp[1];
			
			if(!isset($object->{$field}))
			{
				$lClass = $this->_FIELDS[$field]->getModel();
				$ch = new $lClass();
                $oClass = $ch->getObjectClass();
                $object->{$field} = new $oClass();
                $ch = null;
			}
			$object->{$field}->{$subfield} = $value;
			unset($object->{$key});
		}
	}
	/**
	 * Array version of parseJoinFields().
	 */
	protected function parseJoinFieldsArray(&$array)
	{
		$count = count($array);
		for($i=0; $i<$count; $i++)
		{
			$this->parseJoinFields($array[$i]);
		}
	}

    /**
     * Retreive corresponding object class
     * @return string
     */
    public function getObjectClass()
    {
        return $this->_CLASS;
    }

    /**
     * Retreive all fields
     * @return array<OrionModelField>
     */
    public function getFields()
    {
        return $this->_FIELDS;
    }

    /**
     * Retreive corresponding OrionModelField
     * @param string $field
     * @return OrionModelField
     */
    public function getField($field)
    {
        return $this->_FIELDS[$field];
    }

    /**
     * Retreive linked table
     * @return string Table
     */
    public function getTable()
    {
        return $this->_TABLE;
    }

    /**
     * Retreive last joined table
     * @return string
     */
     public function lastJoinedTable()
     {
         return $this->_LAST_JOINED_TABLE;
     }

    /**
     * Check wether a field is linked to another model
     * @param string $name
     * @return boolean
     */
    public function isLinked($field)
    {
        return $this->_FIELDS[$field]->isLinked();
    }

    /**
     * Check wether provided field is primary or not.
     * @param string $field
     * @return boolean
     */
    public function isPrimary($field)
    {
        return $this->_FIELDS[$field]->isPrimary();
    }

    /**
     * Check wether provided field is bound to model.
     * @param string $fieldname
     * @return boolean
     */
    public function hasField($fieldname)
    {
        return array_key_exists($fieldname, $this->_FIELDS);
    }
}
?>
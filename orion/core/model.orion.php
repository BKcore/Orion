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
    const DEBUG = true;
	
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
     * Bind function parameter : Integer
     */
    const PARAM_INT = 1;
    /**
     * Bind function parameter : String
     */
    const PARAM_STR = 2;
    /**
     * Bind function parameter :  Long text
     */
    const PARAM_TEXT = 3;
    /**
     * Bind function parameter : Boolean
     */
    const PARAM_BOOL = 5;
    /**
     * Bind function parameter : List
     */
    const PARAM_LIST = 6;
    /**
     * Bind function parameter : Date
     */
    const PARAM_DATE = 7;
    /**
     * Bind function parameter : ID
     */
    const PARAM_ID = 8;
    /**
     * Bind function parameter : Numeric
     */
    const PARAM_NUMERIC = 9;
    /**
     * Bind function parameter : Generic
     */
    const PARAM_GENERIC = 10;
    /**
     * Bind function parameter : Tags
     */
    const PARAM_TAGS = 11;
    /**
     * Bind function parameter : Image
     */
    const PARAM_IMAGE = 12;
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
     * @var arrayMap<string, OrionModelField>
     */
    private $_FIELDS = array();
    /**
     * Array of primary keys
     * @var array<string>
     */
    private $_PRIMARY = array();
    /**
     * Links to other Models
     * @var arrayMap<string, OrionModelLink>
     */
    private $_LINKS = array();
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

    public function  __construct()
    {
        $this->bindAll();
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
     * @param string $name
     * @param OrionModelField $field
     * @param string $legend
     * @param bool $primary_key Is this field a primary key ?
     */
    protected function bind($name, $field, $legend, $primary_key=false)
    {
        $this->_FIELDS[$name] = $field;
        $this->_FIELDS[$name]->legend = $legend;
        $this->_FIELDS[$name]->name = $name;
        if($primary_key) array_push($this->_PRIMARY, $name);
    }

    /**
     * Link a model to current one
     * @param string $model Model class to link
     * @param string linked field name of the current model
     * @param string linked field name of the linked model
     * @param string field name labelling the linked model's entries
     */
    protected function link($model, $leftfield, $rightfield, $rightfield_label)
    {
        $this->_LINKS[$leftfield] = new OrionModelLink($model, $leftfield, $rightfield, $rightfield_label);
    }

    /**
     * Start a select query chain
     */
    public function &select()
    {
        $this->_TYPE = 'select';

        if(func_num_args() == 0)
            $this->_COLUMNS = array('*');
        else
            $this->_COLUMNS = $this->tablePrefixArray($this->escapeArray(func_get_args()), $this->_TABLE);

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
            throw new OrionException($e->getMessage(), $e->getCode(), $this->CLASS_NAME);
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
            throw new OrionException($e->getMessage(), $e->getCode(), $this->CLASS_NAME);
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
        if(is_null($size) || $size == 0)
            throw new OrionException('Empty or null size limit.', E_USER_WARNING, $this->CLASS_NAME);

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
            throw new OrionException('Missing parameter in offset.', E_USER_WARNING, $this->CLASS_NAME);

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
        if(is_null($fields) || is_null($mode))
            throw new OrionException('Missing parameter in order clause.', E_USER_WARNING, $this->CLASS_NAME);

        if(!is_array($fields))
            array($fields);

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

        if(is_null($field) || is_null($comparator) || is_null($value))
            throw new OrionException('Missing argument in where clause', E_USER_WARNING, $this->CLASS_NAME);

        /*if(!array_key_exists($field, $this->_FIELDS))
            throw new OrionException('Where clause field ['.$field.'] is not a valid bounded field.', E_USER_WARNING, $this->CLASS_NAME);
        */
        $this->_WHERE = array($field, $comparator, $this->format($this->escape($value), $this->_FIELDS[$key]->type));

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
            throw new OrionException('Missing argument in AND where clause', E_USER_WARNING, $this->CLASS_NAME);

        $this->_AWHERE[] = array($field, $comparator, $this->format($this->escape($value), $this->_FIELDS[$key]->type));

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
            throw new OrionException('Missing argument in OR where clause', E_USER_WARNING, $this->CLASS_NAME);

        $this->_OWHERE[] = array($field, $comparator, $this->format($this->escape($value), $this->_FIELDS[$key]->type));

        return $this;
    }

    /**
     * Query chain element, defining where clause manualy. Used for complex where clauses
     * <p><b>Be careful when using manual where clause because the string is not parsed, nor escaped. It is used in the query 'AS IS'.</b></p>
     * @param string the complete where clause, without the WHERE keyword
     * @example $ph->where("(id = 1 OR id = 2) AND author LIKE 'Ja%'");
     */
    public function &manualWhere($clause)
    {
        if(!is_string($clause))
            throw new OrionException('Manual where parameter ['.$clause.'] is not a complete <string> where clause.', E_USER_WARNING, $this->CLASS_NAME);

        $this->_MWHERE = $clause;

        return $this;
    }
	
	/**
	 * Query chain element, joining provided $fields on $link.
	 * 
	 */
	public function &join($link, $fields, $type='LEFT')
	{
		if(!array_key_exists($link, $this->_LINKS))
			throw new OrionException('Cannot join ['.$link.'], field is not linked in model.', E_USER_WARNING, $this->CLASS_NAME);
		
		if($fields == null || !is_array($fields))
			throw new OrionException('Missing array of joined fields while trying to join on ['.$link.'].', E_USER_WARNING, $this->CLASS_NAME);
			
		$jhClass = $this->_LINKS[$link]->model;

		$jh = new $jhClass();
		
		// build joined fields array with field aliases 
		foreach($fields as $field)
		{
			$field = $this->escape($field);
			$this->_JOIN_COLUMNS[] = array($this->tablePrefix($field, $jh->getTable()), self::L_FIELD_SLUG.$link.self::L_FIELD_SEP.$field);
		}
		$this->_JOIN[$link] = array($jh->getTable(), $this->escape($type));
		
		return $this;
	}

    /**
     * Save provided object into database, checking validity of model constraints.
     * @param Object $object Object to save
     * @example // Save a post into database
     * try {
     *   $post = new Post('Title', 'Author', 'Hello World!');
     *   $ph = new PostHandler();
     *   $ph->save($post);
     * } catch(OrionException $e) {
     *   $e->toStack();
     * }
     * @return boolean
     */
    public function save($object=null)
    {
		if($object == null) 
            throw new OrionException('Cannot save an empty object.', E_USER_WARNING, $this->CLASS_NAME);
			
        if(empty($this->_FIELDS))
            throw new OrionException('No field bound in model', E_USER_WARNING, $this->CLASS_NAME);

        $data = get_object_vars($object);

        $keys = array();
        $values = array();
        $hasTags=false;

        foreach($data as $key => $value)
        {
            if(!array_key_exists($key, $this->_FIELDS))
                continue;

            if(!$this->checkConstraints($key, $value))
                throw new OrionException('Impossible to save object to database. Value does not meets field ['.$key.'] requirements :'.$value, E_USER_WARNING, $this->CLASS_NAME);

            array_push($keys, $this->escape($key));
            array_push($values, $this->format($this->escape($value), $this->_FIELDS[$key]->type));
        
            if($this->_FIELDS[$key]->type == OrionModel::PARAM_TAGS)
                $hasTags = true;
        }

        $this->_TYPE = 'insert';
        $this->_KEYS = $keys;
        $this->_VALUES = $values;

        if($this->_PDO == null)
            $this->_PDO = OrionSql::getConnection();

        try {
            $result = $this->_PDO->exec($this->getQuery());

            if($hasTags) $this->saveTags($object);
        }
        catch(OrionException $e)
        {
            throw $e;
        }
        catch(PDOException $e)
        {
            throw new OrionException($e->getMessage(), $e->getCode(), $this->CLASS_NAME);
        }

        return (!($result === false));
    }

    /**
     * Update provided object into database, checking validity of model constraints.
     * @param Object $object Object to update
     * @example // Update a post author @id=1
     * try {
     *   $ph = new PostHandler();
     *   $post = $ph->select()->where('id', '=', 1)->fetch();
     *   $post->author = "Jack";
     *   $ph->update($post);
     * } catch(OrionException $e) {
     *   $e->toStack();
     * }
     * @return boolean
     */
    public function update($object=null)
    {
		if($object == null) 
            throw new OrionException('Cannot update an empty object.', E_USER_WARNING, $this->CLASS_NAME);
		
        if(empty($this->_FIELDS))
            throw new OrionException('No field bound in model', E_USER_WARNING, $this->CLASS_NAME);

        $data = get_object_vars($object);

        $sets = array();
        $wheres = array();

        foreach($this->_PRIMARY as $key)
        {
            if(empty($data[$key]) || $data[$key] == null)
                throw new OrionException('Primary keys values not provided in object to update.', E_USER_WARNING, $this->CLASS_NAME);
        }

        foreach($data as $key => $value)
        {
            if(!array_key_exists($key, $this->_FIELDS))
                continue;

            if(in_array($key, $this->_PRIMARY))
            {
                if(empty($value))
                    throw new OrionException('Impossible to update. One or more primary field value are missing in update object.', E_USER_WARNING, $this->CLASS_NAME);

                array_push($wheres, $key.'='.$this->format(OrionTools::escapeSql($this->escape($value)), $this->_FIELDS[$key]->type));
            }

            if(!$this->checkConstraints($key, $value))
                throw new OrionException('Impossible to update object to database. Value does not meets field ['.$key.'] requirements :'.$value, E_USER_WARNING, $this->CLASS_NAME);

            array_push($sets, $this->escape($key).'='.$this->format($value, $this->_FIELDS[$key]->type));
        }

        if(empty($wheres))
            throw new OrionException('Impossible to update. No primary field found in update object.', E_USER_WARNING, $this->CLASS_NAME);

        $this->_TYPE = 'update';
        $this->_SETS = $sets;
        $this->_LIMIT = 1;
        $this->manualWhere(implode(' AND ', $wheres));

        if($this->_PDO == null)
            $this->_PDO = OrionSql::getConnection();

        try {
            $result = $this->_PDO->exec($this->getQuery());
        }
        catch(OrionException $e)
        {
            throw $e;
        }
        catch(PDOException $e)
        {
            throw new OrionException($e->getMessage(), $e->getCode(), $this->CLASS_NAME);
        }

        return (!($result === false));
    }

    /**
     * Execute a delete action on database.
	 * Must be the last function of a query chain.
     * @return boolean
     */
    public function delete($object=null)
    {
        if(empty($this->_FIELDS))
            throw new OrionException('No field bound in model', E_USER_WARNING, $this->CLASS_NAME);
			
		if($object != null)
		{
			$wheres = array();
			foreach($this->_PRIMARY as $key)
			{
				if(!isset($object->{$key}) || $object->{$key} == null)
					throw new OrionException('Primary key ['.$key.'] value not provided in object to delete.', E_USER_WARNING, $this->CLASS_NAME);
				
				array_push($wheres, $key.'='.$this->format(OrionTools::escapeSql($this->escape($value))), $this->_FIELDS[$key]->type);
			}
			$this->_LIMIT = 1;
			$this->manualWhere(implode(' AND ', $wheres));
		}

        $this->_TYPE = 'delete';

        if($this->_PDO == null)
            $this->_PDO = OrionSql::getConnection();

        try {
            $result = $this->_PDO->exec($this->getQuery());
        }
        catch(OrionException $e)
        {
            throw $e;
        }
        catch(PDOException $e)
        {
            throw new OrionException($e->getMessage(), $e->getCode(), $this->CLASS_NAME);
        }

        return (!($result === false));
    }

	/**
	 * Parse and save/update tags of model into their respective table (defined with PARAM_TAGS)
	 * @param object containing the tags fields as attributes
	 * @return boolean success
	 */
	public function saveTags($object)
	{
		if(empty($this->_FIELDS))
            throw new OrionException('No field bound in model', E_USER_WARNING, $this->CLASS_NAME);

		if($this->_PDO == null)
            $this->_PDO = OrionSql::getConnection();

		foreach($this->_FIELDS as $field)
		{
			if($field->type == OrionModel::PARAM_TAGS)
			{
				if($object->{$field->name} != null && !empty($object->{$field->name}))
				{
					$tags = explode($field->param->separator, $object->{$field->name});
					$thClass = $field->param->model;
					$th = new $thClass();
					$values = "(".implode('),(', $this->formatArray($this->escapeArray($tags))).")";
					$query = "INSERT INTO ".$this->escape($th->getTable())." (".$this->escape($field->param->namefield).") VALUES ".$values." ON DUPLICATE KEY UPDATE ".$field->param->counterfield."=".$field->param->counterfield."+1;";

					try {
						$result = $this->_PDO->exec($query);
					}
					catch(PDOException $e)
					{
						throw new OrionException($e->getMessage(), $e->getCode(), $this->CLASS_NAME);
					}
				}
			}
		}

		return !empty($tags);
	}

    /**
     * Get current query string
     * @return string current query string
     */
    protected function getQuery()
    {
        if($this->_TABLE == null)
            throw new OrionException('Unable to perform query. Table not bound.', E_USER_WARNING, $this->CLASS_NAME);

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
					$query .= " ".strtoupper($data[1])." JOIN ".$data[0]." ON ".$this->tablePrefix($this->_LINKS[$key]->leftfield, $this->_TABLE)."=".$this->tablePrefix($this->_LINKS[$key]->rightfield, $data[0]);
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
                   throw new OrionException('Update query must have a where clause.', E_USER_WARNING, $this->CLASS_NAME);
                else
                    $query .= " WHERE ".$where;

                if($this->_LIMIT != null)
                    $query .= " LIMIT ".$this->_LIMIT;
            break;

            case 'delete':
                $query = 'DELETE FROM '.$this->_TABLE;

                if($where == null)
                   throw new OrionException('Delete query must have a where clause.', E_USER_WARNING, $this->CLASS_NAME);
                else
                    $query .= " WHERE ".$where;

                if($this->_LIMIT != null)
                    $query .= " LIMIT ".$this->_LIMIT;
            break;

            default:
                throw new OrionException('Unknown query type.', E_USER_WARNING, $this->CLASS_NAME);
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
        $this->_LINKS=array();
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
    protected function escape($inp)
    {
        if(is_array($inp))
            return array_map(__METHOD__, $inp);

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
    protected function escapeArray( $array )
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
	 * Add a table prefix to a field name
	 * @param string $field field name
	 * @param string $table table name
	 * @return string prefixed field name
	 */
	protected function tablePrefix($field, $table)
	{
		return $table.'.'.$field;
	}
	
    /**
     * Add a table prefix to an array of columns (ex: array('a','b') & table1 gives array('table1.a', 'table1.b'))
     * @param array $array Array of columns
     * @param string $table The table prefix
     * @return array
     */
    protected function tablePrefixArray($array, $table)
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
				$lClass = $this->_LINKS[$field]->model;
				$object->{$field} = new $lClass();
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
     * Test wether $value meets the requirements of corresponging bounded field
     * @param string $key Field name
     * @param mixed $value Value to test
     * @return bool True if constraints are respected, false otherwise
     */
    public function checkConstraints($key, $value)
    {
        if(!array_key_exists($key, $this->_FIELDS))
            return false;

        $field = $this->_FIELDS[$key];

        if($value == null) return true; // until required is implemented

        $noc = ($field->param == null);

        switch($field->type)
        {
            case self::PARAM_INT:
            case self::PARAM_ID:
                if($noc) return (is_numeric($value));
                else return (is_numeric($value) && $value >= $field->param->min && $value <= $field->param->max);
            break;

            case self::PARAM_STR:
                if($noc) return (is_string($value));
                return (is_string($value) && strlen($value) <= $field->param);
            break;

            case self::PARAM_BOOL:
                return (is_bool($value));
            break;

            case self::PARAM_LIST:
                return (in_array($value, $field->param));
            break;

            default:
                return true;
            break;
        }
    }

    /**
     * Format a value for SQL use
     * @param mixed $value
     * @param int $type valid PARAM_<TYPE> constant (see OrionModel consts)
     * @return string SQL value formatted
     */
    public function format($value, $type=null)
    {
        if($value == null)
            return "''";

        switch($type)
        {
            case self::PARAM_INT:
            case self::PARAM_ID:
                return intval($value);
            break;

            case self::PARAM_BOOL:
                return ($value == true);
            break;

            case self::PARAM_NUMERIC:
                return $value;
            break;

            default:
                return "'".$value."'";
            break;
        }
    }

	/**
     * Maps the standard $this->format to the elements of $array
     * @param array<mixed> $array Array to format
	 * @param string $type PARAM_TYPE
     * @return array<mixed> Escaped array
     */
	public function formatArray($array, $type=null)
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
     * Retreive linked field
     * @param string $field
     * @return OrionModelLink
     */
    public function getLink($field)
    {
        return $this->_LINKS[$field];
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
     * Check wether a field is linked to another model
     * @param string $name
     * @return boolean
     */
    public function isLinked($field)
    {
        return array_key_exists($field, $this->_LINKS);
    }

    /**
     * Check wether provided field is primary or not.
     * @param string $field
     * @return boolean
     */
    public function isPrimary($field)
    {
        return in_array($field, $this->_PRIMARY);
    }

    /**
     * Returns a standard OrionModelField Integer type with constraints
     * @param int $min
     * @param int $max
     * @return OrionModelField
     */
    protected function PARAM_INT($min=-32768, $max=32767)
    {
		$param = new stdClass();
		$param->min = $min;
		$param->max = $max;

        return new OrionModelField(self::PARAM_INT, $param);
    }

    /**
     * Returns a standard OrionModelField String type with constraints
     * @param int $maxlength
     * @return OrionModelField
     */
    protected function PARAM_STR($maxlength=255)
    {
        return new OrionModelField(self::PARAM_STR, $maxlength);
    }

    /**
     * Returns a standard OrionModelField Long text type with constraints
     * @param int $maxlength
     * @return OrionModelField
     */
    protected function PARAM_TEXT($maxlength=255)
    {
        return new OrionModelField(self::PARAM_TEXT, $maxlength);
    }

    /**
     * Returns a standard OrionModelField Boolean type
     * @return OrionModelField
     */
    protected function PARAM_BOOL()
    {
        return new OrionModelField(self::PARAM_BOOL);
    }

    /**
     * Returns a standard OrionModelField List type with arguments as values
     * @param mixed... List values
     * @return OrionModelField
     * @example $this->bind('listparam', $this->PARAM_LIST('red', 'blue', 'green'));
     */
    protected function PARAM_LIST($args)
    {
        $list = func_get_args();
        return new OrionModelField(self::PARAM_LIST, $list);
    }

    /**
     * Returns a standard OrionModelField Date type
     * @param boolean $current Setting this to TRUE will use NOW() when updating or inserting
     * @return OrionModelField
     */
    protected function PARAM_DATE($current=false)
    {
        return new OrionModelField(self::PARAM_DATE, $current);
    }

    /**
     * Returns a standard OrionModelField ID type
     * @return OrionModelField
     */
    protected function PARAM_ID()
    {
        return new OrionModelField(self::PARAM_ID);
    }

    /**
     * Returns a standard OrionModelField Numeric type
     * @return OrionModelField
     */
    protected function PARAM_NUMERIC()
    {
        return new OrionModelField(self::PARAM_NUMERIC);
    }

    /**
     * Returns a standard OrionModelField Generic type
     * @return OrionModelField
     */
    protected function PARAM_GENERIC()
    {
        return new OrionModelField(self::PARAM_GENERIC);
    }

    /**
     * Returns a standard OrionModelField Tags type
     * @return OrionModelField
     */
    protected function PARAM_TAGS($separator, $model, $namefield, $counterfield)
    {
		$param = new stdClass();
		$param->separator = $separator;
		$param->model = $model;
		$param->namefield = $namefield;
		$param->counterfield = $counterfield;
        return new OrionModelField(self::PARAM_TAGS, $param);
    }

    /**
     * Returns a standard OrionModelField Image type
     * @return OrionModelField
     */
    protected function PARAM_IMAGE()
    {
        return new OrionModelField(self::PARAM_IMAGE);
    }
}

/**
 * OrionModel Field sub class, used for internal attribute binding.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionModelField
{
	/**
	 * Unique field name
	 * @var string
	 */
	public $name=null;

    /**
     * Must be a valid OrionModel::PARAM_<TYPE> constant
     * @see OrionModel constants
     * @var int
     */
    public $type=null;

    /**
     * Type constraints
     * @var mixed
     */
    public $param=null;

    /**
     * Field legend
     * @var string
     */
    public $legend=null;

    /**
     * Creates an OrionModelField sub class, used for internal attribute binding.
     * @param int $_type Must be a valid OrionModel::PARAM_<TYPE> constant
     * @param mixed $_param Type constraints
     * @param string $_legend
     */
    public function  __construct($_type, $_param=null, $_legend=null)
    {
        $this->type = $_type;
        $this->param = $_param;
        $this->legend = $_legend;
    }
}

/**
 * OrionModel Link sub class, used for internal model linking.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class OrionModelLink
{
    /**
     * Class name of the model to link
     * @var string
     */
    public $model;
    /**
     * Name of the field of the current model that is linked to $_model
     * @var string
     */
    public $leftfield;
    /**
     * Name of the field of $_model that is linked to the current model
     * @var string
     */
    public $rightfield;
    /**
     * Name of the field that represents the label of the $_model row (That is, for example, a "name" column in a category table).
     * @var string
     */
    public $rightfield_label;

    /**
     * Links another model to the current model, usually for join queries
     * @param string $_model Class name of the model to link
     * @param string $_leftfield Name of the field of the current model that is linked to $_model
     * @param string $_rightfield Name of the field of $_model that is linked to the current model
     * @param string $_rightfield_label Name of the field that represents the label of the $_model row (That is, for example, a "name" column in a category table).
     */
    public function  __construct($_model, $_leftfield, $_rightfield, $_rightfield_label)
    {
        $this->model = $_model;
        $this->leftfield = $_leftfield;
        $this->rightfield = $_rightfield;
        $this->rightfield_label = $_rightfield_label;
    }
}
?>
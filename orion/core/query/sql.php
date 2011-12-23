<?php

namespace Orion\Core\Query;

use \Orion\Core;


/**
 * \Orion\Core\Query\Sql
 * 
 * Orion Query for SQL DB type.
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 *
 * @author Thibaut Despoulain
 * @version 0.11.10
 */
class Sql implements Base
{
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

    private static $COMPARATORS = array(
        Core\Query::EQUAL => ' = ',
        Core\Query::NEQUAL => ' != ',
        Core\Query::LIKE => ' LIKE ',
        Core\Query::NOT => ' NOT ',
        Core\Query::REGEX => ' REGEXP '
    );
    private static $ORDERS = array(
        Core\Query::ASCENDING => 'ASC',
        Core\Query::DESCENDING => 'DESC'
    );
//------------------------------------------------------------------------------
// Protected Dynamic
//------------------------------------------------------------------------------
    /**
     * From table override
     * @var String
     */
    protected $_TABLE = null;

    /**
     * Columns placeholder for select, update queries.
     * @var array<string>
     */
    protected $_COLUMNS = array( );

    /**
     * Keys for save
     * @var array<string>
     */
    protected $_KEYS = null;

    /**
     * Values for save
     * @var array<string>
     */
    protected $_VALUES = null;

    /**
     * Array of key/values to update
     * @var String[String]
     */
    protected $_SETS = null;

    /**
     * Use distinct
     * @var Boolean 
     */
    protected $_DISTINCT = false;
    
    /**
     * Where clause placeholder
     * @var array<string>[3] Example: array('field', 'LIKE', '%token%');
     */
    protected $_WHERE = array( );

    /**
     * Manual where clause placeholder. Used for complex where clauses.
     * <p><b>Be careful when using manual where clause because the string is not parsed, nor escaped. It is put in the query 'AS IS'.</b></p>
     * @var string string the complete where clause, without the WHERE keyword
     */
    protected $_MWHERE = null;

    /**
     * AND where clause placeholder
     * @var string AND where clause
     */
    protected $_AWHERE = array( );

    /**
     * OR where clause placeholder
     * @var string OR where clause
     */
    protected $_OWHERE = array( );

    /**
     * Order clause placeholder
     * @var array[2]
     */
    protected $_ORDER = array( );

    /**
     * Limit clause placeholder
     * @var int
     */
    protected $_LIMIT = null;
    
    /**
     * Group by statement field holder
     * @var string
     */
    protected $_GROUPBY = null;
    
    /**
     * Offset clause placeholder
     * @var int
     */
    protected $_OFFSET = null;

    /**
     * The type of the query
     * @var string
     */
    protected $_TYPE = null;

    /**
     * Joined links
     * @var array<string> Array of linked fields' name
     */
    protected $_JOIN = array( );

    /**
     * Joined fields data. <br />
     * <b>The field alias must be internally defined using L_FIELD_SLUG and L_FIELD_SEP</b>
     * @var array<array[3]<string>> Each row correspond to a linked field, consisting in an array with [0] being the field name, [1] the field alias and [2] the linked object's class name for object convertion.
     */
    protected $_JOIN_COLUMNS = array( );
    
    /**
     * Attribute storing tables joined using joinTable().
     * @var String[][] Assoc array of [leftfield, rightfield, jointype] with tables as keys.
     */
    protected $_JOIN_TABLE = array();
    
    /**
     * PDO query result
     * @var PDOStatement
     */
    protected $_QUERY = null;

    /**
     * SQL query string for backtracing
     * @var string
     */
    protected $_QUERY_STRING = null;

    /**
     * Internal query result keeper
     * @var mixed
     */
    protected $_RESULT = null;

    /**
     * PDO instance from Sql class
     * @var PDO
     */
    protected $_PDO = null;

    /**
     * Last joined table name
     * @var string
     */
    protected $_LAST_JOINED_TABLE = null;

//------------------------------------------------------------------------------
// Public Dynamic Implementations
//------------------------------------------------------------------------------

    public function __construct( $model )
    {
        if ( isset( $model ) && is_string( $model ) )
        {
            $this->model = $model;
            if ( is_string( $model::getTable() ) )
                $this->_TABLE = $model::getTable();
        }
    }

    /**
     * Query chain element, defining AND where clause
     * @param string $field
     * @param string $comparator
     * @param mixed $value <b>If no wildcard should be used, pass this parameter using Tools::escapeSql($value).</b> Other standard quotes escapes are done internally.
     */
    public function &andWhere( $field, $comparator=null, $value=null )
    {
        if ( empty( $this->_WHERE ) )
            return $this->where( $field, $comparator, $value );

        if ( !is_string( $field ) || !is_string( $comparator ) || $value === null )
            throw new Core\Exception( 'Missing argument in AND where clause' );

        if ( !array_key_exists( $comparator, self::$COMPARATORS ) )
            throw new Core\Exception( 'Unsupported comparator. Please use manualWhere() to use custom comparators.' );

        $comparator = self::$COMPARATORS[ $comparator ];

        $model = $this->model;
        if ( $this->hasModel() && $model::hasField( $field ) )
            $this->_AWHERE[ ] = array( $this->tablePrefix( $field ), $comparator, $model::getField( $field )->prepare( $this->escape( $value ) ) );
        else
            $this->_AWHERE[ ] = array( $this->tablePrefix( $field ), $comparator, $this->quote( $this->escape( $value ) ) );

        return $this;
    }

    public function &delete()
    {
        return $this->execute( 'delete' );
    }
    
    /**
     * Ignore duplicates on provided field
     * @param String $field
     */
    public function &distinct()
    {
        $this->_DISTINCT = true;
        return $this;
    }

    /**
     * Ends a query chain and returns the resulting row
     * @return \Orion\Core\Model
     */
    public function fetch()
    {
        try
        {
            $this->_QUERY = Core\DB::getConnection()->query( $this->getQuery() );

            if ( $this->hasModel() )
                $class = $this->model;
            else
                $class = '\\Orion\\Core\\Object';

            $this->_RESULT = $this->_QUERY->fetchObject( $class );

            if ( $this->_RESULT !== false && !empty( $this->_JOIN ) )
                $this->parseJoinFields( $this->_RESULT );
        }
        catch ( PDOException $e )
        {
            throw new Core\Exception( $e->getMessage(), $e->getCode() );
        }

        return $this->_RESULT;
    }

    /**
     * Ends a query chain and returns the resulting rows
     * @return \Orion\Core\Model[]
     */
    public function fetchAll()
    {
        try
        {
            $this->_QUERY = Core\DB::getConnection()->query( $this->getQuery() );

            if ( $this->hasModel() )
                $class = $this->model;
            else
                $class = '\\Orion\\Core\\Object';

            $this->_RESULT = $this->_QUERY->fetchAll( \PDO::FETCH_CLASS, $class );

            if ( $this->_RESULT !== false && !empty( $this->_JOIN ) )
                $this->parseJoinFields( $this->_RESULT );
        }
        catch ( PDOException $e )
        {
            throw new Core\Exception( $e->getMessage(), $e->getCode() );
        }

        return $this->_RESULT;
    }
    
    /**
     * Add a GROUP BY statement on provided field
     * @param String $col
     */
    public function &groupBy($col)
    {
        $this->_GROUPBY = $this->tablePrefix($this->escape($col));
        
        return $this;
    }
    
    /**
     * This method is SQL-only.
     * Groups rows from x-to-many query by concatenating them in a sigle column.
     * @param String $field The joined column to contact
     * @param String $name The alias of the new column
     * @param String $separator The separator used for concatenation
     * @param String [$table] The table used to prefix the $field
     */
    public function &groupConcat( $field, $name, $separator, $table=null )
    {
        $this->_COLUMNS[] = 'GROUP_CONCAT(' . $this->tablePrefix( $field, false, $table ) . ' SEPARATOR ' . $this->quote( $this->escape( $separator ) ) . ') AS ' . $this->antiQuote( $name );
    
        return $this;
    }

    /**
     * Query chain element, joining provided $fields on $link.
     * This method is only usable with Query that have a model bound width linked fields.
     * For manual joints, use manualJoin().
     * @param string $link Either a linked field name if Query is bound to a Model or a table name otherwise.
     * @param string $fields The fields to join
     * @param string $type [LEFT|RIGHT|INNER|OUTER]
     */
    public function &join( $link, $fields, $type='LEFT' )
    {
        if ( $fields == null || !is_array( $fields ) )
            throw new Core\Exception( 'Missing array of joined fields while trying to join on [' . Core\Security::preventInjection( $link ) . '].' );

        if ( !Core\Tools::match( $type, '(left|right) ?(inner|outer)?', 'i' ) )
            throw new Core\Exception( 'Invalid join type while trying to join on [' . Core\Security::preventInjection( $link ) . '].' );

        if ( !$this->hasModel() )
            throw new Core\Exception( 'Cannot create join query, no model bound.' );

        $model = $this->model;

        if ( !$model::isLinked( $link ) )
            throw new Core\Exception( 'Field [' . Core\Security::preventInjection( $link ) . ' is not linked in model.' );

        $table = $model::getField( $link )->getLinkedTable();
        $this->_LAST_JOINED_TABLE = $table;
        $previousTable = $this->getTable();
        $this->setTable( $table );

        // build joined fields array with field aliases 
        foreach ( $fields as $field )
            $this->_JOIN_COLUMNS[ ] = array( $this->tablePrefix( $field ), $this->antiQuote( self::L_FIELD_SLUG . $link . self::L_FIELD_SEP . $this->escape( $field ) ) );

        $this->_JOIN[ $link ] = array( $table, $type );

        $this->setTable( $previousTable );

        return $this;
    }
    
    /**
     * /!\ This method is experimental and should be used only if you know what you are doing.
     * Query chain element, joining provided $table to the query.
     * This method does not require a bound model. 
     * But the downside is that you won't have any object formating or column aliasing, so be careful with overlaps.
     * @param string $link A table name.
     * @param string $leftfield The field from the current table
     * @param string $rightfield The field from the joined table
     * @param string $type [LEFT|RIGHT|INNER|OUTER]
     */
    public function &joinTable( $table, $leftfield, $rightfield, $type='LEFT' )
    {
        if ( empty($table) || empty($leftfield) || empty($rightfield) )
            throw new Core\Exception( 'Missing arguments while trying to join [' . Core\Security::preventInjection( $table ) . '].' );

        if ( !Core\Tools::match( $type, '(natural )?((inner|cross)|(left|right)( outer)?)?', 'i' ) )
            throw new Core\Exception( 'Invalid join type while trying to join [' . Core\Security::preventInjection( $table ) . '].' );

        $this->_JOIN_TABLE[ $table ] = array($leftfield, $rightfield, $type);
        
        return $this;
    }

    /**
     * Query chain element, limiting the query to a given $size
     * @param Integer $size
     */
    public function &limit( $size )
    {
        if ( !is_numeric( $size ) )
            throw new Core\Exception( 'Invalid limit argument.' );

        $this->_LIMIT = $size;

        return $this;
    }

    /**
     * Query chain element, defining where clause manualy. Used for complex where clauses
     * <p><b>Be careful when using manual where clause because the string is not parsed, nor escaped. It is used in the query 'AS IS'.</b></p>
     * @param string the complete where clause, without the WHERE keyword
     */
    public function &manualWhere( $clause )
    {
        if ( !is_string( $clause ) )
            throw new Core\Exception( 'Manual where argument is not a complete <string> where clause.' );

        $this->_MWHERE = $clause;

        return $this;
    }

    /**
     * Query chain element, setting the starting offset
     * @param int $start
     */
    public function &offset( $start )
    {
        if ( !is_numeric( $start ) )
            throw new Core\Exception( 'Invalid offset argument' );

        $this->_OFFSET = $start;

        return $this;
    }

    /**
     * Query chain element, setting ordering clause
     * @param mixed $fields either an array of fields or a single field
     * @param string $mode 'ASC'|'DESC' or an array of modes
     */
    public function &order( $fields, $mode )
    {
        if ( empty( $fields ) || empty( $mode ) )
            throw new Core\Exception( 'Missing parameter in order clause.' );

        $order = array( );

        if ( is_array( $fields ) && is_array( $mode ) )
        {
            $lastMode = self::$ORDERS[ Core\Query::ASCENDING ];
            $c = count( $fields );
            for ( $i = 0; $i < $c; $i++ )
            {
                if ( !isset( $mode[ $i ] ) )
                    $order[ $this->tablePrefix( $this->escape( $fields[ $i ] ) ) ] = $lastMode;
                elseif ( !array_key_exists( $mode[ $i ], self::$ORDERS ) )
                    throw new Core\Exception( 'Unsupported order statement.' );
                else
                {
                    $order[ $this->tablePrefix( $this->escape( $fields[ $i ] ) ) ] = $mode[ $i ];
                    $lastMode = $mode[ $i ];
                }
            }

            $this->_ORDER = $order;
        }
        else
        {
            if ( !is_array( $fields ) )
                $fields = array( $fields );
            if ( !is_array( $mode ) )
                $mode = array( $mode );

            return $this->order( $fields, $mode );
        }

        return $this;
    }

    /**
     * Query chain element, defining OR where clause
     * @param string $field
     * @param string $comparator
     * @param mixed $value <b>If no wildcard should be used, pass this parameter using Tools::escapeSql($value).</b> Other standard quotes escapes are done internally.
     */
    public function &orWhere( $field, $comparator=null, $value=null )
    {
        if ( empty( $this->_WHERE ) )
            return $this->where( $field, $comparator, $value );

        if ( !is_string( $field ) || !is_string( $comparator ) || $value === null )
            throw new Core\Exception( 'Missing argument in OR where clause' );

        if ( !array_key_exists( $comparator, self::$COMPARATORS ) )
            throw new Core\Exception( 'Unsupported comparator. Please use manualWhere() to use custom comparators.' );

        $comparator = self::$COMPARATORS[ $comparator ];

        $model = $this->model;
        if ( $this->hasModel() && $model::hasField( $field ) )
            $this->_OWHERE[ ] = array( $this->tablePrefix( $field ), $comparator, $model::getField( $field )->prepare( $this->escape( $value ) ) );
        else
            $this->_OWHERE[ ] = array( $this->tablePrefix( $field ), $comparator, $this->quote( $this->escape( $value ) ) );

        return $this;
    }

    public function &save()
    {
        return $this->execute( 'insert' );
    }

    /**
     * Start a select query chain
     * @param mixed Either select('f1','f2', ...) or select(array('f1','f2',...))
     */
    public function &select( $data=null )
    {
        $this->_TYPE = 'select';

        if ( func_num_args() == 0 || $data == null )
            if ( $this->hasModel() )
            {
                $model = $this->model;
                $this->_COLUMNS = $this->tablePrefix( $this->escape( array_keys( $model::getFields() ) ) );
            }
            else
                $this->_COLUMNS = array( '*' );
        else
        {
            if ( is_array( $data ) )
                $cols = $data;
            else
                $cols = func_get_args();
            $this->_COLUMNS = $this->tablePrefix( $this->escape( $cols ) );
        }

        return $this;
    }

    /**
     * Start a select query chain by selecting all fields of a model except those provided.
     * Can only be used on Query with a model bound.
     * @param mixed $data Either selectAllExcept('f1','f2', ...) or selectAllExcept(array('f1','f2',...))
     */
    public function &selectAllExcept( $data=null )
    {
        if ( !$this->hasModel() )
            throw new Core\Exception( 'selectAllExcept() can only be used with Query when a model is bound.' );

        $this->_TYPE = 'select';

        if ( func_num_args() == 0 || $data == null )
            throw new Core\Exception( 'SelectAllExcept() needs at least one field as argument.' );

        if ( is_array( $data ) )
            $exceptCols = $data;
        else
            $exceptCols = func_get_args();

        $cols = array( );
        $model = $this->model;
        foreach ( array_keys( $model::getFields() ) as $field )
            if ( !in_array( $field, $exceptCols ) )
                $cols[ ] = $field;

        if ( empty( $cols ) )
            throw new Core\Exception( 'SelectAllExcept() Too many fields removed. Needs at least one field to select.' );

        $this->_COLUMNS = $this->tablePrefix( $this->escape( $cols ) );

        return $this;
    }

    /** 
     * Set columns values for insert and update queries
     * @param string $key
     * @param string $value
     * @return Sql 
     */
    public function &set( $key, $value )
    {
        if ( !is_string( $key ) )
            throw new Core\Exception( 'set() key must be a valid string.' );

        $model = $this->model;
        if ( $this->hasModel() && $model::hasField( $key ) )
            $this->_DATA [ $this->antiQuote( $this->escape( $key ) ) ] = $model::getField( $key )->prepare( $this->escape( $value ) );
        else
            $this->_DATA [ $this->antiQuote( $this->escape( $key ) ) ] = $this->quote( $this->escape( $value ) );

        return $this;
    }

    public function success()
    {
        return ($this->_RESULT != null && $this->_RESULT !== false);
    }

    public function &update()
    {
        return $this->execute( 'update' );
    }

    /**
     * Query chain element, defining where clause
     * @param string $field
     * @param string $comparator
     * @param mixed $value <b>If no wildcard should be used, pass this parameter using Tools::escapeSql($value).</b> Other standard quotes escapes are done internally.
     */
    public function &where( $field, $comparator=null, $value=null )
    {
        if ( !is_string( $field ) || !is_string( $comparator ) || $value === null )
            throw new Core\Exception( 'Missing argument in where clause' );

        if ( !array_key_exists( $comparator, self::$COMPARATORS ) )
            throw new Core\Exception( 'Unsupported comparator. Please use manualWhere() to use custom comparators.' );

        $comparator = self::$COMPARATORS[ $comparator ];

        $model = $this->model;
        if ( $this->hasModel() && $model::hasField( $field ) )
            $this->_WHERE = array( $this->tablePrefix( $field ), $comparator, $model::getField( $field )->prepare( $this->escape( $value ) ) );
        else
            $this->_WHERE = array( $this->tablePrefix( $field ), $comparator, $this->quote( $this->escape( $value ) ) );

        return $this;
    }

    /**
     * Add a table prefix to a field name or an array of field names if they do not already have a table prefix.
     * Also add surrounding antiquotes.
     * @param String|String[] $field field name
     * @param Boolean $force Force prefix, even if field is already prefixed
     * @param String $table Prefix with a custom table name instead of the current table.
     * @return String prefixed field name
     */
    public function tablePrefix( $fields, $force=false, $table=null )
    {
        if ( $table == null )
            $table = $this->_TABLE;

        if ( $table == null )
            return $fields;

        if ( is_array( $fields ) )
        {
            $result = array( );

            foreach ( $fields as $field )
                $result[ ] = self::tablePrefix( $field, $force, $table );

            return $result;
        }

        if ( strpos( '.', $fields ) !== false )
        {
            if ( $force )
                return $this->antiQuote( $table ) . '.' . $this->antiQuote( implode( '`.`', explode( '.', $fields ) ) );
            else
                return $this->antiQuote( implode( '`.`', explode( '.', $fields ) ) );
        }
        else
        {
            return $this->antiQuote( $table ) . '.' . $this->antiQuote( $fields );
        }
    }

    /**
     * Surrround a string with antiquotes
     * @param String $data
     * @return String
     */
    public function antiQuote( $data )
    {
        if ( is_array( $data ) )
        {
            $c = count( $data );
            for ( $i = 0; $i < $c; $i++ )
                $data[ $i ] = self::escape( $data[ $i ] );

            return $data;
        }

        if ( is_string( $data ) )
            return '`' . $data . '`';
        else
            throw new Core\Exception( 'antiquote() must be applyed to strings.' );
    }

    /**
     * Escapes string for mysql usage ('\\', "\0", "\n", "\r", "'", '"', "\x1a")
     * @param string $data
     * @param string[] $custom An array of custom elements to remove
     * @return string
     */
    public function escape( $data )
    {
        if ( is_array( $data ) )
        {
            $c = count( $data );
            for ( $i = 0; $i < $c; $i++ )
                $data[ $i ] = self::escape( $data[ $i ] );

            return $data;
        }

        if ( is_string( $data ) )
            return str_replace( array( '\\', "\0", "\n", "\r", "'", '"', "\x1a" ), array( '\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z' ), $data );
        else
            return $data;
    }

    /**
     * Execute given query type using current query data
     * @param string $type Query type ('insert', 'delete', ..etc)
     * @return Sql 
     */
    public function &execute( $type=null )
    {
        if ( $type != null )
            $this->_TYPE = $type;

        $this->_RESULT = Core\DB::getConnection()->exec( $this->getQuery() );

        return $this;
    }

    /**
     * Adds single quotes to a string
     * @param stirng $string
     * @return string
     */
    public function quote( $string )
    {
        return "'" . $string . "'";
    }

    /**
     * Get current table in use
     * @return String
     */
    public function getTable()
    {
        return $this->_TABLE;
    }

    /**
     * Get last joined table's name
     * @return string
     */
    public function getLastJoinedTable()
    {
        return $this->_LAST_JOINED_TABLE;
    }

    /**
     * Overrides current table name.
     * @param String $table 
     */
    public function &setTable( $table )
    {
        if ( is_string( $table ) )
            $this->_TABLE = $this->escape( Core\Tools::removeString( ' ', $table ) );
        else
            throw new Core\Exception( 'setTable() method must be given a string.' );

        return $this;
    }

    /**
     * Revert table name back to model's table.
     * Usable only on Query with a bound Model.
     * @param Boolean $dontUseModel Set this to TRUE to skip revert and force table to none
     */
    public function &unsetTable( $dontUseModel=false )
    {
        if ( !$this->hasModel() )
            throw new Core\Exception( 'Unable to unset table on Query with no bound Model.' );
        else
        {
            $model = $this->model;
            if ( is_string( $model::getTable() ) )
                $this->_TABLE = $model::getTable();
            else
                throw new Core\Exception( 'Cannot revert table to model table, varable is not a string.' );
        }

        return $this;
    }

    /**
     * Is a model bound to Query object ?
     * @return Boolean
     */
    public function hasModel()
    {
        return ( is_string( $this->model ) );
    }

    /**
     * Get current query string
     * @return string current query string
     */
    public function getQuery()
    {
        if ( $this->_TABLE == null )
            throw new Core\Exception( 'Unable to perform query, no table provided.' );

        if ( $this->hasModel() )
            $this->unsetTable();

        if ( $this->_MWHERE != null )
            $where = $this->_MWHERE;
        else
        {
            if ( !empty( $this->_WHERE ) && is_array( $this->_WHERE ) )
            {
                $where = implode( ' ', $this->_WHERE );
                if ( !empty( $this->_AWHERE ) )
                {
                    foreach ( $this->_AWHERE as $andClause )
                        $where .= ' AND ' . implode( ' ', $andClause );
                }
                if ( !empty( $this->_OWHERE ) )
                {
                    foreach ( $this->_OWHERE as $orClause )
                        $where .= ' OR ' . implode( ' ', $orClause );
                }
            }
            else
                $where = null;
        }

        switch ( $this->_TYPE )
        {
            case 'select':
                $query = "SELECT ";
                if ($this->_DISTINCT)
                    $query .= 'DISTINCT ';
                            
                $query .= implode( ', ', $this->_COLUMNS );

                foreach ( $this->_JOIN_COLUMNS as $col )
                    $query .= ", " . implode( ' AS ', $col );

                $query .= " FROM " . $this->antiQuote( $this->_TABLE );

                if ( !empty( $this->_JOIN ) )
                    if ( !$this->hasModel() )
                        throw new Core\Exception( 'Trying to execute a join query with no model bound.' );
                    else
                        $model = $this->model;

                foreach ( $this->_JOIN as $key => $data )
                    $query .= " " . strtoupper( $data[ 1 ] ) . " JOIN " . $this->antiQuote( $data[ 0 ] ) . " ON " . $this->tablePrefix( $model::getField( $key )->getBinding() ) . "=" . $this->tablePrefix( $model::getField( $key )->getRightfield(), false, $data[ 0 ] );

                foreach ( $this->_JOIN_TABLE as $table => $data )
                    $query .= " " . strtoupper( $data[ 2 ] ) . " JOIN " . $this->antiQuote( $table ) . " ON " . $this->tablePrefix( $data[0] ) . "=" . $this->tablePrefix( $data[ 1 ], false, $table );
                
                if ( $where != null )
                    $query .= " WHERE " . $where;
                
                if( $this->_GROUPBY != null)
                    $query .= " GROUP BY ".$this->_GROUPBY;
                
                if ( !empty( $this->_ORDER ) )
                {
                    $order = array( );
                    foreach ( $this->_ORDER as $field => $mode )
                        $order[ ] = $field . ' ' . $mode;
                    $query .= " ORDER BY " . implode( ', ', $order );
                }
                if ( $this->_LIMIT != null )
                    $query .= " LIMIT " . $this->_LIMIT;
                if ( $this->_OFFSET != null )
                    $query .= " OFFSET " . $this->_OFFSET;
                break;

            case 'insert':
                if ( empty( $this->_DATA ) )
                    throw new Core\Exception( 'Missing row data in insert query.' );

                $query = "INSERT INTO `" . $this->_TABLE . "`";

                $keys = array_keys( $this->_DATA );
                $values = array_values( $this->_DATA );
                $query .= " (" . implode( ', ', $keys ) . ") VALUES (" . implode( ', ', $values ) . ")";
                break;

            case 'update':
                if ( empty( $this->_DATA ) )
                    throw new Core\Exception( 'Missing row data in update query.' );

                $query = "UPDATE `" . $this->_TABLE . "`";

                $sets = array( );
                foreach ( $this->_DATA as $key => $value )
                    $sets[ ] = $key . '=' . $value;

                $query .= " SET " . implode( ', ', $sets );

                if ( $where == null )
                    throw new Core\Exception( 'Update query must have a where clause.' );
                else
                    $query .= " WHERE " . $where;

                if ( $this->_LIMIT != null )
                    $query .= " LIMIT " . $this->_LIMIT;
                break;

            case 'delete':
                $query = "DELETE FROM `" . $this->_TABLE . "`";

                if ( $where == null )
                    throw new Core\Exception( 'Delete query must have a where clause.', E_USER_WARNING, get_class( $this ) );
                else
                    $query .= " WHERE " . $where;

                if ( $this->_LIMIT != null )
                    $query .= " LIMIT " . $this->_LIMIT;
                break;

            default:
                throw new Core\Exception( 'Unknown query type.', E_USER_WARNING, get_class( $this ) );
                break;
        }

        $this->_QUERY_STRING = $query;

        if ( \Orion::isDebug() )
            echo 'SQL_QUERY: ' . $query . '<br />';

        return $query . ';';
    }

    /**
     * Parse query-resulting object to build linked objects from joined fields' syntax.<br />
     * In other words, convert "L_FIELD_SLUG.$linkedfield.L_FIELD_SEP.$fieldname" into linked Object.
     * @param \Orion\Core\Model|\Orion\Core\Object
     */
    protected function parseJoinFields( &$object )
    {
        if ( is_array( $object ) )
        {
            $c = count( $object );
            for ( $i = 0; $i < $c; $i++ )
                self::parseJoinFields( $object[ $i ] );

            return;
        }

        $linkedFields = Core\Tools::extractArrayKeysStartingWith( get_object_vars( $object ), self::L_FIELD_SLUG );
        $l_field_len = strlen( self::L_FIELD_SLUG );
        foreach ( $linkedFields as $key => $value )
        {
            $tmp = explode( self::L_FIELD_SEP, substr( $key, $l_field_len ) );
            $field = $tmp[ 0 ];
            $subfield = $tmp[ 1 ];

            if ( !($object->{$field} instanceOf Core\Model) || !($object->{$field} instanceOf Core\Object) )
            {
                if ( $this->hasModel( $field ) )
                {
                    $model = $this->model;
                    if ( !$model::isLinked( $field ) )
                        $object->{$field} = new Object();
                    else
                    {
                        $linkedClass = $model::getField( $field )->getModel();
                        $object->{$field} = new $linkedClass();
                    }
                }
                else
                    $object->{$field} = new Object();
            }
            $object->{$field}->{$subfield} = $value;
            unset( $object->{$key} );
        }
    }

}

?>

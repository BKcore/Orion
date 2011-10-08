<?php

/**
 * Orion abstract model class.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.11.10
 *
 * @abstract
 */

namespace Orion\Core;

class Model extends Object
{
//------------------------------------------------------------------------------
// Protected Static @override
//------------------------------------------------------------------------------

    /**
     * DB Table used by the model
     * @var String
     */
    protected static $table;
    
    /**
     * Attribute storing fields assigned using the define() function.
     * @var \Orion\Core\Model\Field[]
     */
    protected static $fields = array( );

    /**
     * Array containing the names of the primary fields.
     * @var String[]
     */
    protected static $primaryKeys = array( );
    
    /**
     * Enable/Disable events
     * @var Boolean[]
     */
    protected static $events = array( );

    /**
     * Override this function to bind model's attributes to corresponding \Orion\Model\Field's.
     * Usage of self::has() inside this function is advised.
     * Be sure to first declare the required $table, $fields, $primaryKeys and $events static properties.
     * @abstract
     */
    protected static function describe()
    {
        // -
    }

//------------------------------------------------------------------------------
// Protected Static
//------------------------------------------------------------------------------

    /**
     * Define and bind a new field to current model.
     * @param \Orion\Model\Field $field The field definition to bind
     */
    protected static final function has( $field )
    {
        static::$fields[ $field->getName() ] = $field;

        if ( $field->isPrimary() )
            static::$primaryKeys[ ] = $field->getName();

        if ( method_exists( $field, 'onDelete' ) )
            static::$events[ 'delete' ] = true;

        if ( method_exists( $field, 'onSave' ) )
            static::$events[ 'save' ] = true;

        if ( method_exists( $field, 'onUpdate' ) )
            static::$events[ 'update' ] = true;
    }

    protected static final function hasEvent( $type )
    {
        if ( empty( static::$fields ) )
        {
            static::describe();
            if ( empty( static::$fields ) )
                throw new Exception( 'No field bound to model.', E_ERROR, get_called_class() );
        }

        return isset( static::$events[ $type ] );
    }

    /**
     * Prepare model for Query::Factory().
     * 
     * Call describe() if fields attribute is not set.
     * Check table binding.
     * 
     * @return String The model class name to be used by the Query::Factory 
     */
    protected static final function prepare()
    {
        if ( empty( static::$fields ) )
        {
            static::describe();
            if ( empty( static::$fields ) )
                throw new Exception( 'No field bound to model.', E_ERROR, get_called_class() );
        }

        if ( empty( static::$table ) )
            throw new Exception( 'No table bound to model.', E_ERROR, get_called_class() );

        return get_called_class();
    }

//------------------------------------------------------------------------------
// Public Static
//------------------------------------------------------------------------------

    /**
     * Return a new model object instanced filled using POST vars
     * @param \Orion\Core\Model $object If given, data will be appended to $object
     * @param boolean $mergeEmpty Merge fields even if empty post data ?
     * @return \Orion\Core\Model
     */
    public static final function &fetchPostData( &$object=null, $mergeEmpty=false )
    {
        if ( !( $object instanceof \Orion\Core\Model ) )
        {
            $class = get_called_class();
            $object = new $class();
        }
        
        foreach ( array_keys( self::getFields() ) as $field )
            if ( isset( $_POST[ $field ] )
                    && (
                    ($mergeEmpty)
                    || (!$mergeEmpty && !empty( $_POST[ $field ] ))
                    )
            )
            {
                $object->{$field} = $_POST[ $field ];
            }

        return $object;
    }

    /**
     * Return a new model object instanced filled using PUT vars from php input stream (php://input).
     * @param \Orion\Core\Model $object If given, data will be appended to $object
     * @param boolean $mergeEmpty Merge fields even if empty post data ?
     * @return \Orion\Core\Model 
     */
    public static final function &fetchRestData( &$object=null, $mergeEmpty=false )
    {
        if ( !( $object instanceof \Orion\Core\Model ) )
        {
            $class = get_called_class();
            $object = new $class();
        }

        $data = null;
        parse_str( file_get_contents( "php://input" ), $data );

        foreach ( array_keys( self::getFields() ) as $field )
            if ( isset( $data[ $field ] )
                    && (
                    ($mergeEmpty)
                    || (!$mergeEmpty && $data[ $field ] != null)
                    )
            )
            {
                $object->{$field} = $data[ $field ];
            }

        return $object;
    }

    /**
     * Shortcut function. Similar to self::query()->select( $args ).
     * @param String... $args Fields to select (variable-length argument list)
     * @return \Orion\Model\Query\Base 
     */
    public static final function get( $args=null )
    {
        $query = self::query();
        return $query->select( func_get_args() );
    }

    public static final function getClass()
    {
        return get_called_class();
    }

    /**
     * Get a field of the model by its name.
     * @param String $name The name (binding identifier) of the field
     * @return \Orion\Model\Field
     */
    public static final function getField( $name )
    {
        if ( empty( static::$fields ) )
            static::describe();
        return static::$fields[ $name ];
    }

    /**
     * Get all of the model fields as an array of fields
     * @return \Orion\Model\Field[]
     */
    public static final function getFields()
    {
        if ( empty( static::$fields ) )
            static::describe();
        return static::$fields;
    }

    public static final function getLinkedTable( $field )
    {
        if ( !self::isLinked( $field ) )
            throw new Exception( 'Field [' . Core\Security::preventInjection( $field ) . '] is not linked, unable to get table.', E_WARNING, self::getClass() );
        
        $model = self::getField( $field )->getModel();
        return $model::getTable();
    }

    /**
     * Get bound table name.
     * @return String
     */
    public static final function getTable()
    {
        return static::$table;
    }

    /**
     * Check wether provided field is bound to model.
     * @param string $fieldname
     * @return boolean
     */
    public static final function hasField( $fieldname )
    {
        return array_key_exists( $fieldname, self::getFields() );
    }

    /**
     * Check wether a field is linked to another model
     * @param String $field
     * @return boolean
     */
    public static final function isLinked( $field )
    {
        return self::getField( $field )->isLinked();
    }

    /**
     * Check wether provided field is primary or not.
     * @param string $field
     * @return boolean
     */
    public static final function isPrimary( $field )
    {
        return self::getField( $field )->isPrimary();
    }

    /**
     * (Factory) Create and return a new Query instance.
     * @return \Orion\Core\Query\Base
     */
    public static final function query()
    {
        return Query::Factory( self::prepare() );
    }

//------------------------------------------------------------------------------
// Public Dynamic
//------------------------------------------------------------------------------

    /**
     * Delete current object from database.
     * Object must have its primary keys defined.
     * @return \Orion\Core\Query
     */
    public function delete()
    {
        $query = self::query();

        // Setup where clause using primaryKeys
        foreach ( static::$primaryKeys as $key )
        {
            if ( !isset( $this->{$key} ) || $this->{$key} == null )
                throw new Exception( 'Primary key [' . $key . '] value not provided in object to delete.', E_USER_WARNING, get_class() );

            $query->andWhere( $key, Query::EQUAL, $this->{$key} );
        }

        if ( self::hasEvent( 'delete' ) )
        {
            // Retrieve old data for onDelete event
            $oldData = $query->select()->fetch();
            // Trigger onDelete event
            foreach ( self::getFields() as $key => $field )
                $field->onDelete( $oldData->{$key} );
        }

        return $query->delete();
    }

    /**
     * Given that primary keys are provided in object, fills out remaining attributes using an automatic select Query.
     * @param String... $args Fields to fill out (variable-length argument list)
     * @return \Orion\Core\Model
     */
    public function fillout( $args )
    {
        $query = $this->get( $args );

        // Setup where clause using primaryKeys
        foreach ( static::$primaryKeys as $key )
        {
            if ( !isset( $this->{$key} ) || $this->{$key} == null )
                throw new Exception( 'Primary key [' . $key . '] value not provided in object to fill out.', E_USER_WARNING, get_class() );

            $query->andWhere( $key, Query::EQUAL, $this->{$key} );
        }

        return $query->select()->fetch();
    }

    /**
     * Save current object into database.
     * Object must have its primary keys defined.
     * @return \Orion\Core\Query\Base
     */
    public function save()
    {
        $query = self::query();
        $savedPairs = array( );

        foreach ( self::getFields() as $key => $field )
        {
            $value = $this->{$key};
            if ( isset( $value ) && $value != null )
            {
                if ( !$field->validate( $value ) )
                    throw new Exception( 'Unable to save object to database. Field [' . $key . '] is not valid.', E_USER_ERROR, get_class() );

                if ( $field->isEmptyValue( $value ) )
                    continue;

                $query->set( $field->getName(), $value );

                $savedPairs[ $field->getName() ] = $value;
            }
        }

        // Trigger onSave event
        if ( self::hasEvent( 'save' ) )
            foreach ( $savedPairs as $key => $value )
                self::getField( $key )->onSave( $value );

        return $query->save();
    }

    /**
     * Check the uniqueness of the object for provided fields
     * @param Mixed $fields Can either be a single field name, an array of field names, 
     *      or even a variable-length argument list of field names. 
     *      Leave blank for a check on all fields.
     * @param string Field name
     * @return Boolean
     */
    public function unique( $fields )
    {
        if ( func_num_args() == 0 || $fields == null )
            $cols = array( '*' );
        else
        {
            if ( is_array( $fields ) )
                $cols = $fields;
            else
                $cols = func_get_args();
        }

        $query = self::get( $cols );

        foreach ( $fields as $key )
        {
            $query->andWhere( $key, Query::EQUAL, $this->{$key} );
        }

        $result = $query->limit( 1 )->fetch();

        if ( $result === false )
            return true;

        return false;
    }

    /**
     * Update current object into database.
     * Object must have its primary keys defined.
     * @return \Orion\Core\Query\Base
     */
    public function update()
    {
        $query = self::query();

        // Setup where clause using primaryKeys
        foreach ( static::$primaryKeys as $key )
        {
            if ( !isset( $this->{$key} ) || $this->{$key} == null )
                throw new Exception( 'Primary key [' . $key . '] value not provided in object to update.', E_USER_WARNING, get_class() );

            $query->andWhere( $key, Query::EQUAL, $this->{$key} );
        }

        // Retrieve old data for onUpdate event
        if ( self::hasEvent( 'update' ) )
            $oldData = $query->select()->fetch();

        // Setup updated keys/values
        $savedPairs = array( );

        foreach ( self::getFields() as $key => $field )
        {
            $value = $this->{$key};
            if ( isset( $value ) && $value != null )
            {
                if ( !$field->validate( $value ) )
                    throw new Exception( 'Unable to update object to database. Field [' . $key . '] is not valid.', E_USER_ERROR, get_class() );

                if ( $field->isEmptyValue( $value ) )
                    continue;

                $query->set( $field->getName(), $value );

                $savedPairs[ $field->getName() ] = $value;
            }
        }

        // Trigger onUpdate event
        if ( self::hasEvent( 'update' ) )
            foreach ( $savedPairs as $key => $value )
                self::getField( $key )->onUpdate( $value, $oldData->{$key} );

        return $query->update();
    }

}

?>
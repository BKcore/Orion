<?php

/**
 * Orion form class.
 *
 * Form creation helper
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */

namespace Orion\Core;

define( "NEWLINE", "\n" );

class Form
{
    /**
     * Class name
     */
    const CLASS_NAME = 'OrionForm';

    /**
     * Hidden input type
     */
    const HIDDEN = 0;
    /**
     * Text input type
     */
    const TEXT = 1;
    /**
     * Passwod input type
     */
    const PASSWORD = 2;
    /**
     * Textarea type
     */
    const TEXTAREA = 3;
    /**
     * Checkbox input type
     */
    const CHECKBOX = 4;
    /**
     * Checkbox list input type
     */
    const CHECKLIST = 5;
    /**
     * Radio list input type
     */
    const RADIOLIST = 6;
    /**
     * Droplist input type
     */
    const DROPLIST = 7;
    /**
     * Image browse type
     */
    const IMAGE = 8;
    /**
     * File browse type
     */
    const FILE = 9;
    /**
     * Valued list input type
     */
    const VALUEDLIST = 10;
    /**
     * Submit input type
     */
    const SUBMIT = 11;
    /**
     * Cancel button type
     */
    const CANCEL = 12;
    /**
     * Message type
     */
    const MESSAGE = 13;

    /**
     * Model handler
     * @var OrionModel
     */
    private $model = null;

    /**
     * Array of OrionFormFields, used to store form fields as Data Object
     * @var array<OrionFormField>
     */
    private $fields = array( );

    /**
     * Ordered array of fields' name.
     * It's used to determine fields order in form.
     * @var array<int, string>
     */
    private $indexes = array( );

    /**
     * Array of required fields names
     * @var string array
     */
    private $required = array( );

    /**
     * Form name
     * @var string
     */
    private $name = null;

    /**
     * Action url
     * @var string
     */
    private $action = null;

    /**
     * Creates a new form
     */
    public function __construct( $_name, $_action='#' )
    {
        $this->fields = array( );
        $this->required = array( );
        $this->name = $_name;
        $this->action = $_action;
    }

    /**
     * Add a new field to the form.
     * These functions can be chained like $form->add(...)->add(...) etc.
     * @param OrionModelField $field
     * @param mixed $value
     * @return OrionForm instance
     */
    public function &add( $field, $value=null )
    {
        if ( array_key_exists( $field->getName(), $this->fields ) )
            throw new Exception( 'Duplicate field [' . $field->getName() . '] during form creation.', E_WARNING, get_class( $this ) );

        if ( $value != null )
            $field->setValue( $value );
        $this->fields[ $field->getName() ] = $field;
        $this->indexes[ ] = $field->getName();

        return $this;
    }

    /**
     * Add a new field to the form before given element.
     * These functions can be chained like $form->add(...)->add(...) etc.
     * @param string Name of the element before which the field will be added.
     * @param OrionModelField $field
     * @param mixed $value
     * @return OrionForm instance
     */
    public function &addBefore( $element, $field, $value=null )
    {
        if ( array_key_exists( $field->getName(), $this->fields ) )
            throw new Exception( 'Duplicate field [' . $field->getName() . '] during form creation.', E_WARNING, get_class( $this ) );

        if ( $value != null )
            $field->setValue( $value );
        $this->fields[ $field->getName() ] = $field;
        array_splice( $this->indexes, array_search( $element, $this->indexes ), 0, $field->getName() );

        return $this;
    }

    /**
     * Add a new field from model to current form.
     * Similiar to add() but getting its field object from provided model based on field binding.
     * @param string $field
     * @param mixed $value
     */
    public function &addField( $fieldname, $value=null )
    {
        if ( $this->model == null )
            throw new Exception( 'You need to provide a correct OrionModel before using addField()', E_WARNING, get_class( $this ) );

        if ( array_key_exists( $fieldname, $this->fields ) )
            throw new Exception( 'Duplicate field [' . Core\Security::preventInjection( $fieldname ) . '] during form creation.', E_WARNING, get_class( $this ) );

        $model = $this->model;

        if ( !$model::hasField( $fieldname ) )
            throw new Exception( 'The field [' . Core\Security::preventInjection( $fieldname ) . '] does not exist in provided model.', E_WARNING, get_class( $this ) );

        $this->fields[ $fieldname ] = $model::getField( $fieldname );
        if ( $value != null )
            $this->fields[ $fieldname ]->setValue( $value );

        $this->indexes[ ] = $fieldname;

        return $this;
    }

    /**
     * Removes a registered field
     * @param string $fieldname 
     * @return OrionForm instance
     */
    public function &remove( $fieldname )
    {
        if ( func_num_args() > 1 )
        {
            foreach ( func_get_args() as $arg )
                $this->remove( $arg );
        }
        else
        {
            if ( !array_key_exists( $fieldname, $this->fields ) )
                throw new Exception( 'Unable to remove field [' . $fieldname . ']. Field does not exist.' );

            unset( $this->fields[ $fieldname ] );
            array_splice( $this->indexes, array_search( $fieldname, $this->indexes ), 1 );
        }

        return $this;
    }

    /**
     * Hydrate form fields with values stored in provided object (usually for editing purpose)
     * @param Object $object Object to retrives each field values from.
     */
    public function hydrate( $object )
    {
        foreach ( array_keys( $this->fields ) as $key )
        {
            if ( isset( $object->{$key} ) && $object->{$key} != null )
                $this->fields[ $key ]->setValue( stripslashes( $object->{$key} ) );
        }
    }

    /**
     * Prepare this form using provided Model class
     * @param String $model
     */
    public function prepare( $model=null )
    {
        if ( $model == null && $this->model == null )
            throw new Exception( 'You need to provide a model to prepare this form.', E_WARNING, get_class( $this ) );

        if ( $model != null )
            $this->model = $model;

        foreach ( $model::getFields() as $field )
            $this->add( $field );
    }

    /**
     * Retreive the HTML version of the form
     * @param boolean $XHTML Output (x)HTML if set to TRUE instead of pure HTML
     */
    public function toHtml( $XHTML=false )
    {
        $html = '<form id="' . $this->name . '" name="' . $this->name . '" method="post" action="' . $this->action . '"';
        if ( $this->isMultipart() )
            $html.= ' enctype="multipart/form-data"';
        $html .= '>' . NEWLINE;

        foreach ( $this->indexes as $fieldname )
        {
            $field = $this->fields[ $fieldname ];
            if ( !$field->isVisible() )
                continue;
            if ( !method_exists( $field, 'toHtml' ) )
                throw new Exception( 'Missing toHtml() method in field [' . $field->getName() . ']', E_USER_ERROR, get_class( $this ) );
            $html .= $field->toHtml( $XHTML ) . NEWLINE;
        }

        $html .= '</form>' . NEWLINE;

        return $html;
    }

    /**
     * Bind an OrionModel to the form
     * @param OrionModel $_model
     */
    public function setModel( $_model )
    {
        $this->model = $_model;
    }

    /**
     * Get bound model
     * @return OrionModel
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Get a field
     * @param string $name
     * @return OrionFormField
     */
    public function &getField( $name )
    {
        return $this->fields[ $name ];
    }

    public function isMultipart()
    {
        foreach ( $this->fields as $field )
            if ( $field->isMultipart() )
                return true;
        return false;
    }

}

?>

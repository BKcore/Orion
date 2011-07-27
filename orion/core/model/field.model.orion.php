<?php
/**
 * Base model field class.
 * Extend this abstract class when creating a new model field class. (OrionModelCustomfield extends OrionModelField). 
 * <b>Be sure to specify at least $bind and $type attributes</b>
 */
abstract class OrionModelField
{
    /**
     * Sets field visibility in Form creation
     * @var boolean
     */
    protected $visible=true;
    /**
     * Set this to true if it's a field type used for table linkage
     * @var boolean
     */
    protected $linked=false;
    /**
     * Set this to true if this field need the form type to be multipart (like a file uploader for example).
     * @var boolean
     */
    protected $multipart=false;
    /**
     * The field's value
     * @var mixed
     */
    protected $value;
    /**
     * Standard field type identifier
     * @var string
     */
    protected $type;
    /**
     * Field identifier (used as DB field name, for field ID, and such)
     * @var string
     */
    protected $bind;
    /**
     * Field label, used for display and form labelling purposes
     * @var string
     */
    protected $label='OrionField';
    /**
     * Set this to true if the field is a primary DB key
     * @var boolean
     */
    protected $primary=false;
    /**
     * Set this to true if it's a required field
     * @var boolean
     */
    protected $required=false;

    /**
     * Retreive field's label
     * @return string
     */
	public function getLabel()
	{
		return $this->label;
	}

    /**
     * Retreive field's type
     * @return string
     */
	public function getType()
	{

		return $this->type;
	}

    /**
     * Retreive field's binding (field ID)
     * @return string
     */
	public function getBinding()
	{
		return $this->bind;
	}

    /**
     * Same as getBinding(), just a more common name. Retreive field's binding (field ID)
     * @return string
     */
    public function getName()
    {
        return $this->bind;
    }

    /**
     * Retreive field's value
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set field's value
     */
    public function setValue($val)
    {
        $this->value = $val;
    }

    /**
     * Set field's form visibility
     */
    public function setVisibility($bool)
    {
        $this->visible = $bool;
    }

    /**
     * Check if the field's value is empty (override for specific cases)
     * @return boolean
     */
    public function isEmptyValue($value)
    {
        return ($value == null || $value == '' || $value == "''");
    }

    /**
     * Check if the field is linked
     * @return boolean
     */
    public function isLinked()
    {
        return $this->linked;
    }

    /**
     * Check if the field needs a multipart form type
     * @return boolean
     */
    public function isMultipart()
    {
        return $this->multipart;
    }

    /**
     * Check if the field is a primary DB key
     * @return boolean
     */
	public function isPrimary()
	{
		return $this->primary;
	}

    /**
     * Check if the field is required
     * @return boolean
     */
    public function isRequired()
    {
        return $this->required;
	}

    /**
     * Check if the field is visible in form creation
     * @return boolean
     */
    public function isVisible()
    {
        return $this->visible;
    }

    /**
     * This method is called before every field value usage in DB transaction.
     * Override this method to define a specific way of preparing the field value for DB insertion
     * @return string
     */
	public function prepare($value)
	{
		return "'".$value."'";
	}

    /**
     * This method is called before every field value usage in DB transaction.
     * Override this method to use a specific value validation process for DB insertion
     * @return boolean
     */
	public function validate($value)
	{
		return true;
	}

    public function  __toString()
    {
        return '[OrionModelField] '.$this->label.' bound to '.$this->bind;
    }

    /**
     * This method is called before DB deletion action.
     * Override this method to define a specific pre-deletion behaviour
     * @param mixed $oldvalue The old field value (before deletion occurs)
     */
    public function onDelete($oldvalue)
	{
		//-
	}
    /**
     * This method is called before DB insertion action
     * Override this method to define a specific pre-insertion behaviour
     * @param mixed $value The new field value (before insertion occurs)
     */
    public function onSave($value)
	{
		//-
	}
    /**
     * This method is called before DB update action
     * Override this method to define a specific pre-update behaviour
     * @param mixed $oldvalue The old field value (before update occurs)
     * @param mixed $value The new field value (before update occurs)
     */
    public function onUpdate($oldvalue, $newvalue)
	{
		//-
	}

    /**
     * Override this function to define a specific (x)HTML form field to use during model-to-form translation.
     * By default, this function returns a hidden form field.
     * @param boolean $XHTML
     * @return string
     */
    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        return '<input name="'.$this->bind.'" type="hidden" value="'.$this->value.'"'.$tag.'>';
    }
}

?>

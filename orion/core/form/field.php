<?php
namespace Orion\Core\Form;

/**
 * Base form field class.
 *
 * Contrary to OrionModelFields, these fields are used only for form creation. Never for DB model handling.
 * Extend this abstract class when creating a new form field class. (Customfield extends Field).
 * <b>Be sure to specify at least $bind and $type attributes</b>
 *
 * This class is part of Orion, the PHP5 Framework (http://orionphp.org/).
 */
abstract class Field
{
    /**
     * Override this function to define a specific (x)HTML form field to use during model-to-form translation.
     * @param boolean $XHTML
     * @return string
     */
    public abstract function toHtml($XHTML=true);

    /**
     * Sets field visibility in Form creation
     * @var boolean
     */
    protected $visible=true;
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
     * Field identifier (used as field ID, and such)
     * @var string
     */
    protected $bind;
    /**
     * Field label, used for display and form labelling purposes
     * @var string
     */
    protected $label='OrionField';
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
     * Check if the field needs a multipart form type
     * @return boolean
     */
    public function isMultipart()
    {
        return $this->multipart;
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

    public function  __toString()
    {
        return '[Field] '.$this->label.' with id: '.$this->bind;
    }
}

?>

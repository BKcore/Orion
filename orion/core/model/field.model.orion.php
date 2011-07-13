<?php
/**
 * Base model field class
 */
abstract class OrionModelField
{    
    protected $visible=true;
    protected $linked=false;
    protected $multipart=false;
    protected $value;
    protected $type;
    protected $bind;
    protected $label='OrionField';
    protected $primary=false;
    protected $required=false;

	public function getLabel()
	{
		return $this->label;
	}

	public function getType()
	{

		return $this->type;
	}

	public function getBinding()
	{
		return $this->bind;
	}

    public function getName()
    {
        return $this->bind;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($val)
    {
        $this->value = $val;
    }

    public function setVisibility($bool)
    {
        $this->visible = $bool;
    }

    public function isEmptyValue($value)
    {
        return ($value == null || $value == '' || $value == "''");
    }

    public function isLinked()
    {
        return $this->linked;
    }

    public function isMultipart()
    {
        return $this->multipart;
    }

	public function isPrimary()
	{
		return $this->primary;
	}

    public function isRequired()
    {
        return $this->required;
	}

    public function isVisible()
    {
        return $this->visible;
    }

	public function prepare($value)
	{
		return "'".$value."'";
	}
	public function validate($value)
	{
		return true;
	}

    public function  __toString()
    {
        return '[OrionModelField] '.$this->label.' bound to '.$this->bind;
    }

    public function onDelete($oldvalue)
	{
		//-
	}
    public function onSave($value)
	{
		//-
	}
    public function onUpdate($oldvalue, $newvalue)
	{
		//-
	}
}

?>

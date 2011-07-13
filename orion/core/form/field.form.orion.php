<?php

abstract class OrionFormField
{
    public abstract function toHtml($XHTML=true);

    protected $visible=true;
    protected $multipart=false;
    protected $value;
    protected $type;
    protected $bind;
    protected $label='OrionField';
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

    public function isMultipart()
    {
        return $this->multipart;
    }

    public function isRequired()
    {
        return $this->required;
	}

    public function isVisible()
    {
        return $this->visible;
    }

    public function  __toString()
    {
        return '[OrionFormField] '.$this->label.' with id: '.$this->bind;
    }
}

?>

<?php
namespace Orion\Core\Model;

use \Orion\Core;

class String extends Field
{
    protected $length;
    protected $regex;

    public function __construct($bind='string', $label='String', $length=255, $regex=null, $required=false, $primary=false)
    {
        $this->type = 'string';
        $this->bind = $bind;
        $this->label = $label;
        $this->length = $length;
        $this->regex = $regex;
        $this->required = $required;
        $this->primary = $primary;
    }

	public function validate($value)
	{
        if($value == null) return true;
        
		if($this->length != null && strlen($value) > $this->length)
			return false;
		elseif($this->regex != null && !Core\Tools::match($value, $this->regex))
            return false;
        
		return true;
	}

    public function prepare($value)
    {
		return "'".$value."'";
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        return '<div class="form-row"><label for="'.$this->bind.'">'.$this->label.'</label><div class="form-container"><input name="'.$this->bind.'" type="text" class="form-text" value="'.$this->value.'"'.$tag.'></div></div>';
    }
}

?>

<?php

class OrionModelString extends OrionModelField
{
    protected $length;

    public function __construct($bind='string', $label='String', $length=255, $required=false, $primary=false)
    {
        $this->type = 'string';
        $this->bind = $bind;
        $this->label = $label;
        $this->length = $length;
        $this->required = $required;
        $this->primary = $primary;
    }

	public function validate($value)
	{
		if($this->length != null && strlen($value) > $this->length)
			return false;
		else
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

        return '<label for="'.$this->bind.'">'.$this->label.'</label><input name="'.$this->bind.'" type="text" class="form-text" value="'.$this->value.'"'.$tag.'>';
    }
}

?>

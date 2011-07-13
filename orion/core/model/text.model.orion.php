<?php

class OrionModelText extends OrionModelField
{
    protected $length;

    public function __construct($bind='text', $label='Text', $length=1000, $required=false)
    {
        $this->type = 'text';
        $this->bind = $bind;
        $this->label = $label;
        $this->length = $length;
        $this->required = $required;
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

        return '<label for="'.$this->bind.'">'.$this->label.'</label><textarea name="'.$this->bind.'" class="form-textarea">'.$this->value.'</textarea>';
    }
}

?>

<?php

class OrionModelBool extends OrionModelField
{
    public function __construct($bind='bool', $label='Boolean', $required=false)
    {
        $this->type = 'bool';
        $this->bind = $bind;
        $this->label = $label;
        $this->required = $required;
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        return '<label for="'.$this->bind.'">'.$this->label.'</label><input name="'.$this->bind.'" type="checkbox" class="form-checkbox" value="1"'. ($this->value ? ' checked="checked"' : '' ) .$tag.'>';
    }
}

?>

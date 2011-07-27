<?php

class OrionModelDate extends OrionModelField
{
    protected $current;

    public function __construct($bind='string', $label='String', $current=true, $required=false, $primary=false)
    {
        $this->type = 'string';
        $this->bind = $bind;
        $this->label = $label;
        $this->current = $current;
        $this->required = $required;
        $this->primary = $primary;
    }

    public function prepare($value)
    {
        if($this->current)
            return 'NOW()';
        else
            return "'".$value."'";
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        return ($this->current) ? '' : '<div class="form-row"><label for="'.$this->bind.'">'.$this->label.'</label><div class="form-container"><input name="'.$this->bind.'" type="text" class="form-element form-date" value="'.$this->value.'"'.$tag.'></div></div>';
    }
}

?>

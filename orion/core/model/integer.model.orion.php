<?php

class OrionModelInteger extends OrionModelField
{
    /**
     * Integer model field
     * @param string $bind
     * @param string $label
     * @param boolean $required
     * @param boolean $primary
     */
    public function __construct($bind='number', $label='Number', $required=false, $primary=false)
    {
        $this->type = 'integer';
        $this->bind = $bind;
        $this->label = $label;
        $this->required = $required;
        $this->primary = $primary;
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

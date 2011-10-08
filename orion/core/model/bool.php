<?php
namespace Orion\Core\Model;


class Bool extends Field
{
    public function __construct($bind='bool', $label='Boolean', $required=false)
    {
        $this->type = 'bool';
        $this->bind = $bind;
        $this->label = $label;
        $this->required = $required;
    }
    
    public function isEmptyValue($value)
    {
        return false;
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        return '<div class="form-row"><label for="'.$this->bind.'">'.$this->label.'</label><div class="form-container"><div class="form-element"><input name="'.$this->bind.'" type="checkbox" class="form-checkbox" value="1"'. ($this->value ? ' checked="checked"' : '' ) .$tag.'></div></div></div>';
    }
}

?>

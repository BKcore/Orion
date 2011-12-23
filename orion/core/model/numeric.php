<?php
namespace Orion\Core\Model;


class Numeric extends Field
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
        $this->type = 'numeric';
        $this->bind = $bind;
        $this->label = $label;
        $this->required = $required;
        $this->primary = $primary;
    }
    
    public function validate( $value )
    {
        return is_numeric( $value );
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

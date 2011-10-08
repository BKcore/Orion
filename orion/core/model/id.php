<?php
namespace Orion\Core\Model;


class Id extends Field
{
    /**
     * ID model field
     * @param string $bind
     * @param string $label
     * @param boolean $primary
     */
    public function __construct($bind='id', $label='Id', $primary=true)
    {
        $this->type = 'id';
        $this->bind = $bind;
        $this->label = $label;
        $this->primary = $primary;
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';
        
        return '<input name="'.$this->bind.'" type="hidden" value="'.$this->value.'"'.$tag.'>';
    }
}

?>

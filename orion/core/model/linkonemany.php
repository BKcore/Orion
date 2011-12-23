<?php
namespace Orion\Core\Model;


class LinkOneMany extends Link
{
    /**
     * Link One-One model field
     * @param string $bind
     * @param string $label
     * @param boolean $primary
     */
    public function __construct($bind, $label, $model, $rightfield='id')
    {
        parent::__construct($bind, $label, $model, $rightfield, $rightfield, false, false);
        $this->type = 'link-one-many';
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

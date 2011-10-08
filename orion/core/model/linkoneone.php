<?php
namespace Orion\Core\Model;


class LinkOneOne extends Link
{
    /**
     * Link One-One model field
     * @param string $bind
     * @param string $label
     * @param boolean $primary
     */
    public function __construct($bind='category', $label='Category', $model='CategoryHandler', $rightfield='id', $rightfieldlabel='name', $required=true, $primary=false)
    {
        parent::__construct($bind, $label, $model, $rightfield, $rightfieldlabel, $required, $primary);
        $this->type = 'link-one-one';
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

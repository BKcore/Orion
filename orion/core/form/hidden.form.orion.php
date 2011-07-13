<?php

class OrionFormHidden extends OrionFormField
{
    public function __construct($bind='hidden', $value='')
    {
        $this->type = 'hidden';
        $this->value = $value;
        $this->bind = $bind;
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

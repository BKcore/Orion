<?php

class OrionFormCancel extends OrionFormField
{
    public function __construct($bind='cancel', $label='Cancel')
    {
        $this->type = 'cancel';
        $this->bind = $bind;
        $this->label = $label;
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        return '<div class="form-row"><input name="'.$this->bind.'" type="button" onclick="javascript:history.go(-1);" class="form-button form-cancel" value="'.$this->label.'"'.$tag.'></div>';
    }
}

?>

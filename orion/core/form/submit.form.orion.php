<?php

class OrionFormSubmit extends OrionFormField
{
    public function __construct($bind='submit', $label='Submit')
    {
        $this->type = 'submit';
        $this->bind = $bind;
        $this->label = $label;
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        return '<input name="'.$this->bind.'" type="submit" class="form-button form-submit" value="'.$this->label.'"'.$tag.'>';
    }
}

?>

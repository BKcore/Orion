<?php
namespace Orion\Core\Form;


class Submit extends Field
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

        return '<div class="form-row"><input name="'.$this->bind.'" type="submit" class="form-button form-submit" value="'.$this->label.'"'.$tag.'></div>';
    }
}

?>

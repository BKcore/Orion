<?php
namespace Orion\Core\Form;


class Message extends Field
{
    public function __construct($bind='message', $label='Message', $value='')
    {
        $this->type = 'message';
        $this->bind = $bind;
        $this->label = $label;
        $this->value = $value;
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        return '<div class="form-row"><div class="form-label">'.$this->label.'</div><div class="form-container"><div class="form-message">'.$this->value.'</div></div></div>';
    }
}

?>

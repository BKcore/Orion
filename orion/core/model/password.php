<?php
namespace Orion\Core\Model;


class Password extends Field
{
    
    protected $needsConfirm = true;

    public function __construct($bind='password', $label='Password', $required=true, $needsConfirm=true)
    {
        $this->type = 'password';
        $this->bind = $bind;
        $this->label = $label;
        $this->required = $required;
        $this->needsConfirm = $needsConfirm;
    }

    public function prepare($value)
    {
		return "'".$value."'";
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        $out = '<div class="form-row"><label for="'.$this->bind.'">'.$this->label.'</label><div class="form-container"><input name="'.$this->bind.'" type="password" class="form-text" value="'.$this->value.'"'.$tag.'></div></div>';
        
        if($this->needsConfirm)
            $out .='<div class="form-row"><label for="'.$this->bind.'">'.$this->label.' (confirm)</label><div class="form-container"><input name="'.$this->bind.'-confirm" type="password" class="form-text" value="'.$this->value.'"'.$tag.'></div></div>';
    
        return $out;
    }
}

?>

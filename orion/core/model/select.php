<?php
namespace Orion\Core\Model;


class Select extends Field
{
	protected $data;
    /**
     * List model field
     * @param string $bind
     * @param string $label
	 * @param array $data The associative data array. (keys are the values' label)
     * @param boolean $primary
     */
    public function __construct($bind='select', $label='List', $data=array(), $required=true)
    {
        $this->type = 'select';
        $this->bind = $bind;
        $this->label = $label;
		$this->data = $data;
        $this->required = $required;
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

		$tmp = '<div class="form-row"><label for="'.$this->bind.'">'.$this->label.'</label><div class="form-container"><select class="form-element" name="'.$this->bind.'">'."\n";
		foreach($this->data as $key => $val)
			$tmp .= '<option value="'.$val.'"'. ($this->value == $val ? ' selected="selected"' : '') .'>'.$key.'</option>'."\n";
		$tmp .= '</select></div></div>';
        
        return $tmp;
    }

    public function getData()
    {
        return $this->data;
    }
}

?>

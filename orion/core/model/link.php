<?php
namespace Orion\Core\Model;

use \Orion\Core;

class Link extends Field
{
    protected $model;
    protected $rightfield;
    protected $rightfieldlabel;

    /**
     * Link model field
     * @param string $bind
     * @param string $label
     * @param boolean $primary
     */
    public function __construct($bind='category', $label='Category', $model='CategoryHandler', $rightfield='id', $rightfieldlabel='name', $required=true, $primary=false)
    {
        $this->type = 'link';
        $this->linked = true;
        $this->bind = $bind;
        $this->label = $label;
        $this->model = $model;
        $this->rightfield = $rightfield;
        $this->rightfieldlabel = $rightfieldlabel;
        $this->required = $required;
        $this->primary = $primary;
    }

    public function toHtml($XHTML=true)
    {
        if($XHTML)
            $tag = ' /';
        else
            $tag = '';

        try {
            $linkedmodel = $this->model;
            $data = $linkedmodel::get($this->rightfield, $this->rightfieldlabel)
                                ->order($this->rightfieldlabel, Core\Query::ASCENDING)
                                ->fetchAll();
            
            $tmp = '<div class="form-row"><label for="'.$this->bind.'">'.$this->label.'</label><div class="form-container"><select class="form-element" name="'.$this->bind.'">'."\n";
            foreach($data as $item)
                $tmp .= '<option value="'.$item->{$this->rightfield}.'"'. ($this->value == $item->{$this->rightfield} ? ' selected="selected"' : '') .'>'.$item->{$this->rightfieldlabel}.'</option>'."\n";
            $tmp .= '</select></div></div>';
        }
        catch(Core\Exception $e)
        {
            $tmp = $e->toString();
        }
        
        return $tmp;
    }
    
    public function getLinkedTable()
    {
        $model = $this->model;
        return $model::getTable();
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getRightfield()
    {
        return $this->rightfield;
    }

    public function getRightfieldlabel()
    {
        return $this->rightfieldlabel;
    }

}

?>

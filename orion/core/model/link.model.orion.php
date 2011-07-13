<?php

class OrionModelLink extends OrionModelField
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
            $lm = new $linkedmodel();
            $data = $lm->select($this->rightfield, $this->rightfieldlabel)
                        ->order($this->rightfieldlabel, OrionModel::ASCENDING)
                        ->fetchAll();
            $lm->flush();

            $tmp = '<label for="'.$this->bind.'">'.$this->label.'</label><select name="'.$this->bind.'">\n';
            foreach($data as $item)
                $tmp .= '<option value="'.$item->{$this->rightfield}.'"'. ($this->value == $item->{$this->rightfield} ? ' selected="selected"' : '') .'>'.$item->{$this->rightfieldlabel}.'</option>\n';
            $tmp .= '</select>';
        }
        catch(OrionException $e)
        {
            $tmp = $e->toString();
        }
        
        return $tmp;
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

<?php
class Tag
{
    public $name;
    public $counter;
}

class TagHandler extends OrionModel
{
    public function bindAll()
    {
        $this->bindTable('bkcore_labs_tags');
        $this->bindClass('Tag');
        $this->bind('name', $this->PARAM_STR(255), true);
        $this->bind('counter', $this->PARAM_INT());
    }
}
?>

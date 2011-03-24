<?php
class Category
{
    public $id;
    public $name;
    public $url;
    public $icon;
    public $date;
    public $description;
}

class CategoryHandler extends OrionModel
{
    public function bindAll()
    {
        $this->bindTable('bkcore_labs_categories');
        $this->bindClass('Category');
        $this->bind('id', $this->PARAM_ID(), 'Identifier', true);
        $this->bind('name', $this->PARAM_STR(140), 'Name');
        $this->bind('url', $this->PARAM_STR(100), 'Url tag');
        $this->bind('icon', $this->PARAM_IMAGE(), 'Icon');
        $this->bind('date', $this->PARAM_DATE(true), 'Date');
        $this->bind('description', $this->PARAM_STR(1000), 'Description');
    }
}
?>

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

class FolderHandler extends OrionModel
{
    public function bindAll()
    {
        $this->bindTable('bkcore_labs_categories');
        $this->bindClass('Category');
        $this->bind('id', $this->PARAM_ID(), true);
        $this->bind('name', $this->PARAM_STR(255));
        $this->bind('url', $this->PARAM_STR(100));
        $this->bind('icon', $this->PARAM_IMAGE());
        $this->bind('date', $this->PARAM_DATE(true));
        $this->bind('description', $this->PARAM_STR(1000));
    }
}
?>

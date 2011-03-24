<?php
class Post
{
    public $id;
    public $title;
    public $url;
    public $tags;
    public $date;
    public $category;
    public $intro;
    public $content;

    public function getTagList()
    {
        return explode(' ', $this->tags);
    }
}

class PostHandler extends OrionModel
{
    protected $CLASS_NAME = 'PostHandler';

    public function bindAll()
    {
        $this->bindTable('bkcore_labs_posts');
        $this->bindClass('Post');
        $this->bind('id', $this->PARAM_ID(), 'Identifier', true);
        $this->bind('title', $this->PARAM_STR(255), 'Title');
        $this->bind('url', $this->PARAM_STR(100), 'Url tag');
        $this->bind('tags', $this->PARAM_TAGS(' ', 'TagHandler', 'name', 'counter'), 'Tags');
        $this->bind('date', $this->PARAM_DATE(true), 'Date');
        $this->bind('category', $this->PARAM_ID(), 'Category');
        $this->bind('intro', $this->PARAM_TEXT(400), 'Introduction');
        $this->bind('content', $this->PARAM_TEXT(), 'Content');
        $this->link('CategoryHandler', 'category', 'id', 'name');
    }
}
?>

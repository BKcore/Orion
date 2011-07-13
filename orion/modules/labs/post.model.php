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

    public function getFormattedContent()
    {
        return $this->format($this->content);
    }

    public function getFormattedIntro()
    {
        return $this->format($this->intro);
    }

    public function format($string)
    {
        $array = explode('[code]', stripslashes($string));
        $k = count($array);
        for($i=1; $i<$k; $i+=2)
            $array[$i] = htmlspecialchars($array[$i]);
        return implode('', $array);
    }
}

class PostHandler extends OrionModel
{
    public function bindAll()
    {
        $this->bindTable('bkcore_labs_posts');
        $this->bindClass('Post');
        $this->bind(new OrionModelLink('category', 'Category', 'CategoryHandler', 'id', 'name', true));
        $this->bind(new OrionModelId('id', 'Identifier', true));
        $this->bind(new OrionModelString('title', 'Title', 255, true));
        $this->bind(new OrionModelString('url', 'Url tag', 100, true));
        $this->bind(new OrionModelTags('tags', 'Tags', ' ', 'TagHandler', 'name', 'counter'));
        $this->bind(new OrionModelDate('date', 'Date', true));
        $this->bind(new OrionModelText('intro', 'Introduction', 400));
        $this->bind(new OrionModelText('content', 'Content', null));
        /*$this->bind('id', $this->PARAM_ID(), 'Identifier', true);
        $this->bind('title', $this->PARAM_STR(255), 'Title');
        $this->bind('url', $this->PARAM_STR(100), 'Url tag');
        $this->bind('tags', $this->PARAM_TAGS(' ', 'TagHandler', 'name', 'counter'), 'Tags');
        $this->bind('date', $this->PARAM_DATE(true), 'Date');
        $this->bind('category', $this->PARAM_ID(), 'Category');
        $this->bind('intro', $this->PARAM_TEXT(400), 'Introduction');
        $this->bind('content', $this->PARAM_TEXT(), 'Content');
        $this->link('CategoryHandler', 'category', 'id', 'name');*/
    }
}
?>

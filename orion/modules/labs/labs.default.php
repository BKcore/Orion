<?php
/**
 * BKcore labs module.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class LabsModule extends OrionModule
{
    protected $name = "labs";
    protected $title = "Labs";
    protected $perpage = 10;
    protected $renderer = OrionRenderer::SMARTY;

    public function  __construct()
    {
        $this->route = new OrionRoute();
        $this->route->addRule('/tag/?', 'tags');
        $this->route->addRule('/post/?/offset/@', 'post_category');
        $this->route->addRule('/post/?/?', 'post');
        $this->route->addRule('/post/?', 'post_category');
        $this->route->addRule('/offset/@', 'index');
        $this->route->addRule('/index', 'index');

        $this->useModel('post');
        $this->useModel('category');
        $this->useModel('tag');
    }

    public function _index($offset=0)
    {
        $this->loadStd('inc.side.tpl', 'labs-side');
        
        try {
            $ph = new PostHandler();
            $posts = $ph->select('date', 'title', 'url', 'intro', 'content', 'tags')
						->join('category', array('name', 'url'))
						->order('date', OrionModel::DESCENDING)
                        ->offset($offset)
                        ->limit($this->perpage+1) // * see below
                        ->fetchAll();

            if($offset != 0)
            {
                $this->assign('prevOffset', ($offset > $this->perpage ? $offset-$this->perpage : 0));
                $this->assign('offset', $offset);
            }

            if(count($posts) > $this->perpage)
            {// * effective way to determine if there are more posts to display
                $this->assign('nextOffset', $offset+$this->perpage);
                array_shift($posts);
            }

            $this->assign('posts', $posts);


            if($offset > 0)
            {
                $this->title .= ' | Offset '.$offset;
                $this->assign('subtitle', 'Showing posts '.$offset.'&tild;'.($offset+count($posts)).' from all categories');
            }
            else
                $this->assign('subtitle', 'Showing latest posts from all categories');

            $this->assign('title', $this->title);
        }
        catch(OrionException $e)
        {
            $this->title .= ' | Error';
            $this->assign('type', 'error');
            $this->assign('info', $e->getMessage());
        }

        $this->renderView('default.list', 'labs-index', 'labs-index');
    }

    public function _error($e)
    {
        if($e == OrionRoute::E_NORULE)
            echo 'Requested URI matches no rule.';

        $this->renderView('default.list', 'labs-error', 'labs-error');
    }

    public function loadStd($file, $id)
    {
        if(!$this->isCached($file, $id, $id))
        {
            $ch = new CategoryHandler();
            $cats = $ch->select('name','url')
                       ->order('name', OrionModel::ASCENDING)
                       ->fetchAll();
            $th = new TagHandler();
            $tags = $th->select('name','counter')
                       ->order('name',OrionModel::ASCENDING)
                       ->fetchAll();

            $this->assign('categories', $cats);
            $this->assign('tags', $tags);
        }
        $this->assign('side_cache_id', $id);

        OrionPlugin::load('jQuery.FancyBox', array('tpl' => $this->tpl));
    }
}
?>

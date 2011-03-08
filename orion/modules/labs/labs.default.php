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
        $this->route->addRule('/page/@', 'index');
        $this->route->addRule('/index', 'index');

        $this->useModel('post');
        $this->useModel('category');
    }

    public function _index($offset=0)
    {
        if($offset == 0)
            $title = $this->title;
        else
            $title = $this->title.' - Offset '.$offset;

        $this->assign('title', $this->title);
        $this->assign('subtitle', 'Labs');

        try {
            $ph = new PostHandler();
            $posts = $ph->select()
                        ->offset(0)
                        ->limit($this->perpage+1) // * see below
                        ->fetchAll();

            if($offset != 0)
            {
                $this->assign('prevOffset', ($offset > $this->perpage ? $offset-$this->perpage : 0));
                $this->assign('offset', $offset);
            }
            else
                $this->assign('prevOffset', -1);

            if(count($posts) > $this->perpage)
            {// * effective way to determine if there are more posts to display
                $this->assign('nextOffset', $offset+$this->perpage);
                array_shift($posts);
            }

            $this->assign('posts', $posts);
            $ph->flush();
        }
        catch(OrionException $e)
        {
            $this->assign('output', $e->__toString(), true);
        }

        $this->displayView('list');
    }

    public function _error($e)
    {
        if($e == OrionRoute::E_NORULE)
            echo 'Requested URI matches no rule.';

    }
}
?>

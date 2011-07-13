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
        $this->route->addRule('/tag/?/offset/@', 'tag');
        $this->route->addRule('/tag/?', 'tag');
        $this->route->addRule('/post/?/offset/@', 'category');
        $this->route->addRule('/post/?/?', 'post');
        $this->route->addRule('/post/?', 'category');
        $this->route->addRule('/offset/@', 'index');
        $this->route->addRule('/index', 'index');

        $this->useModel('post');
        $this->useModel('category');
        $this->useModel('tag');
    }

    public function _index($offset=0)
    {
        $this->loadStd();
        
        $id = $this->genID('index', 'list', $offset);

        if(!$this->isCached('default.list', $id)) $this->listPosts($offset);

        $this->renderView('default.list', $id, 'labs-list');
    }

    public function _category($name=null, $offset=0)
    {
        $this->loadStd();

        $id = $this->genID('category', $name, $offset);

        if(!$this->isCached('default.list', $id)) $this->listPosts($offset, 'category', $name);

        $this->renderView('default.list', $id, 'labs-list');
    }

    public function _tag($name=null, $offset=0)
    {
        $this->loadStd();

        $id = $this->genID('tag', $name, $offset);

        if(!$this->isCached('default.list', $id)) $this->listPosts($offset, 'tag', $name);

        $this->renderView('default.list', $id, 'labs-list');
    }

    public function _post($cat_slug, $post_slug)
    {
        $this->loadStd();
        try {
            $id = $this->genID('post', $post_slug);

            if(!$this->isCached('default.post', $id))
            {
                $ph = new PostHandler();
                $post = $ph->select('date', 'title', 'url', 'intro', 'content', 'tags')
                           ->join('category', array('name', 'url'))
                           ->where($ph->getTable().'.url', '=', $post_slug)
                           ->limit(1)
                           ->fetch();
                $ph->flush();

                if($post == null || empty($post->title)) 
                    throw new OrionException('Could not find post bound to ['.$post_slug.'].', E_USER_ERROR, $this->name);

                $this->assign('post', $post);
                $this->title = $post->title.' | '.$this->title;

                OrionPlugin::load('Disqus', array('tpl' => $this->tpl
                                                 ,'id' => $id
                                                 ,'dev' => true
                                                 ,'permalink' => OrionContext::genModuleURL($this->name, '/post/'.$post->category->url.'/'.$post->url)
                                                 ));                
            }
        }
        catch(OrionException $e)
        {
            $this->assign('type', 'error');
            $this->assign('info', $e->getMessage());
        }

        $this->assign('title', $this->title);
        $this->renderView('default.post', $id, 'labs-post');

    }

    public function _error($e)
    {
        if($e == OrionRoute::E_NORULE)
            echo 'Requested URI matches no rule.';

        $this->renderView('default.list', 'labs-error');
    }

    private function genID($type, $slug, $offset=null)
    {
        return $offset == null ? 'labs-'.$type.'-'.$slug : 'labs-'.$type.'-'.$slug.'-offset'.$offset;
    }

    private function loadStd()
    {
        try {
            $file = 'inc.side.tpl';
            $id = 'labs-side';
            if(!$this->isCached($file, $id))
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
            OrionPlugin::load('jQuery.SyntaxHighlighter');
        } catch(OrionException $e)
        {
            $this->assign('type', 'error');
            $this->assign('info', $e->getMessage());
        }
    }

    private function listPosts($offset=0, $type=null, $filter=null)
    {
        try {
            $ph = new PostHandler();
            $posts = $ph->select('date', 'title', 'url', 'intro', 'tags')
						->join('category', array('name', 'url'))
						->order('date', OrionModel::DESCENDING)
                        ->offset($offset)
                        ->limit($this->perpage+1);

            $subtitle = '';
            $titlefrom = '';

            switch($type)
            {
                case 'category':
                    if($filter == null) throw new OrionException('Trying to filter a category with no category name provided.', E_USER_ERROR, $this->name);
                    $ph->where($ph->lastJoinedTable().'.url', '=', $filter);
                    $subtitle = 'Posts in '.$filter;
                    $titlefrom = 'from "'.$filter.'"';
                break;

                case 'tag':
                    if($filter == null) throw new OrionException('Trying to filter a tag with no tag name provided.', E_USER_ERROR, $this->name);
                    $ph->where('tags', 'LIKE', '%'.$filter.'%');
                    $subtitle = 'Posts tagged in '.$filter;
                    $titlefrom = 'tagged in "'.$filter.'"';
                break;

                default:
                    $subtitle = 'Index';
                    $titlefrom = 'from all categories';
                break;
            }

            $posts = $ph->fetchAll();

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
                $this->assign('subtitle', 'Showing posts '.$offset.'&tild;'.($offset+count($posts)).' '.$titlefrom);
            }
            else
                $this->assign('subtitle', 'Showing latest posts '.$titlefrom);

            $this->assign('title', $subtitle.' | '.$this->title);
        }
        catch(OrionException $e)
        {
            $this->assign('type', 'error');
            $this->assign('info', $e->getMessage());
        }
    }
}
?>

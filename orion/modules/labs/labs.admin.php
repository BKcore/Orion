<?php
/**
 * BKcore labs admin module.
 *
 * @author Thibaut Despoulain
 * @license BSD 4-clauses
 * @version 0.2.11
 */
class LabsModule extends OrionModule
{
    protected $name = 'labs';
    protected $title = 'Labs';
    protected $mode = 'admin';
    protected $perpage = 20;
    protected $renderer = OrionRenderer::SMARTY;
    protected $template = 'orion-admin';

    private $submenu = array();

    public function  __construct()
    {
        $this->allow('administrator');

        $this->route = new OrionRoute();
        $this->route->addRule('/post/new', 'post_new');
        $this->route->addRule('/post/list', 'post_list');
        $this->route->addRule('/post/list/offset/@', 'post_list');
        $this->route->addRule('/post/edit/@', 'post_new');
        $this->route->addRule('/post/delete/@', 'post_delete');
        $this->route->addRule('/category/new', 'cat_new');
        $this->route->addRule('/category/list', 'cat_list');
        $this->route->addRule('/category/list/offset/@', 'cat_list');
        $this->route->addRule('/category/edit/@', 'cat_new');
        $this->route->addRule('/category/delete/@', 'cat_delete');
        $this->route->addRule('/do', 'do');
        $this->route->addRule('/index', 'index');

        $this->useModel('post');
        $this->useModel('category');
        $this->useModel('tag');

        $muri = OrionContext::getModuleURI();
        $this->submenu = array(new OrionMenuEntry('New post', $muri, '/post/new')
                              ,new OrionMenuEntry('Post list', $muri, '/post/list')
                              ,new OrionMenuEntry('New category', $muri, '/category/new')
                              ,new OrionMenuEntry('Category list', $muri, '/category/list'));
        $this->assign('submenu', $this->submenu);
    }

    public function _index($offset=0)
    {
        $this->assign('title', $this->title);
        $this->assign('subtitle', 'Labs manager');

        try {
			$ph = new PostHandler();
			$posts = $ph->select('id', 'date', 'title', 'url')
						->limit(10)
						->order('id', OrionModel::DESCENDING)
						->fetchAll();
			$ch = new CategoryHandler();
			$cats = $ch->select('id', 'date', 'name', 'url')
						->limit(10)
						->order('id', OrionModel::DESCENDING)
						->fetchAll();
			$this->assign('posts', $posts);
			$this->assign('cats', $cats);
        }
        catch(OrionException $e)
        {
            $this->title .= ' | Error';
            $this->assign('type', 'error');
            $this->assign('info', $e->getMessage());
        }

        $this->displayView('admin.index');
    }

    public function _do()
    {
        $links = array();
        $this->assign('type', 'info');

        try {
            if($_POST['action'] == 'post_new')
            {
                $ph = new PostHandler();
                $post = $ph->fetchPostData();
                $ph->save($post);
                $this->assign('info', 'Post "'.$post->title.'" created with success');
                $links[] = new OrionMenuEntry("View post", $this->name.OrionContext::getDefaultModeExtension(), '/post/'.$post->url);
            }
            elseif($_POST['action'] == 'post_edit')
            {
                $ph = new PostHandler();
                $post = $ph->fetchPostData();
                $ph->update($post);

                $this->assign('info', 'Post "'.$post->title.'" updated with success');
                $links[] = new OrionMenuEntry("View post", $this->name.OrionContext::getDefaultModeExtension(), '/post/'.$post->url);
            }
			elseif($_POST['action'] == 'post_delete')
			{
                $ph = new PostHandler();
                $post = $ph->fetchPostData();
				$ph->delete($post);
                $this->assign('info', 'Post "'.$post->title.'" deleted with success');
			}
            $this->title .= ' | Info';
        }
        catch(OrionException $e)
        {
            $this->title .= ' | Error';
            $this->assign('type', 'error');
            $this->assign('info', $e->getMessage());
        }

        $links[] = new OrionMenuEntry("Go back to labs admin", OrionContext::getModuleURI());
        $this->assign('title', $this->title, true);
        $this->assign('links', $links);
        $this->displayView('admin.info');
    }

    public function _post_new($id=null)
    {
        try {
            $ph = new PostHandler();
            $form = new OrionForm('form_post', OrionContext::genModuleURL('labs', '/do', $this->mode));
            $form->prepare($ph);

            if(!is_null($id))
            {
                $post = $ph->select()
                           ->where('id', '=', $id)
                           ->limit(1)
                           ->fetch();
                $form->hydrate($post);
                $form->add(OrionForm::HIDDEN, 'action', 'post_edit');
                $form->add(OrionForm::SUBMIT, 'submit', 'Save');
                $this->assign('subtitle', 'Edit post');
                $this->title .= ' | Edit post';
            }
            else
            {
                $form->add(OrionForm::HIDDEN, 'action', 'post_new');
                $form->add(OrionForm::SUBMIT, 'submit', 'Create');
                $this->assign('subtitle', 'New post');
                $this->title .= ' | New post';
            }

            $this->assign('form', $form->toHtml(true), true);
        }
        catch(OrionException $e)
        {
            $this->title .= ' | Error';
            $this->assign('type', 'error');
            $this->assign('info', $e->getMessage());
        }

        $this->assign('title', $this->title, true);
        $this->displayView('admin.form');
    }
	
	public function _post_delete($id=null)
	{
		try {
			if($id == null) throw new OrionException('You need to provide a valid [id] to delete something.', E_WARNING, $this->name);
			
			$post = $ph->select()
                        ->where('id', '=', $id)
                        ->limit(1);
						
			if($post == null) throw new OrionException('Trying to delete an unexisting post.', E_WARNING, $this->name);
			
			$form = new OrionForm('form_post', OrionContext::genModuleURL('labs', '/do', $this->mode));
			$form->add(OrionForm::HIDDEN, 'action', 'post_delete');
			$form->add(OrionForm::HIDDEN, 'id', $post->id);
			$form->add(OrionForm::HIDDEN, 'title', $post->title);
			$form->add(OrionForm::MESSAGE, 'confirm', 'This will delete "'.$post->title.'". Proceed ?"');
			$form->add(OrionForm::SUBMIT, 'submit', 'Delete');
			$form->add(OrionForm::CANCEL, 'cancel', 'Cancel');
			
            $this->assign('form', $form->toHtml(true), true);
		}
		catch(OrionException $e)
        {
            $this->title .= ' | Error';
            $this->assign('type', 'error');
            $this->assign('info', $e->getMessage());
        }
		
        $this->assign('title', $this->title, true);
        $this->displayView('admin.form');
	}
	
	public function _post_list($offset=0)
	{
		$this->title .= ' | Post list';
		
		if($offset > 0)
            $this->title .= ' | Offset '.$offset;

        $this->assign('title', $this->title);
        $this->assign('subtitle', 'Post list');

        try {
            $ph = new PostHandler();
            $posts = $ph->select('id', 'date', 'title')
						->join('category', array('id', 'name'))
						->order('id', OrionModel::DESCENDING)
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
        }
        catch(OrionException $e)
        {
            $this->title .= ' | Error';
            $this->assign('type', 'error');
            $this->assign('info', $e->getMessage());
        }

        $this->displayView('admin.list');
	}
	
	

    public function _error($e)
    {
        if($e == OrionRoute::E_NORULE)
            $err = 'Requested URI matches no rule.';

        $this->title .= ' | Error';
        $this->assign('type', 'error');
        $this->assign('info', $err);

        $this->assign('title', $this->title, true);
        $this->displayView('admin.info');

    }
}
?>

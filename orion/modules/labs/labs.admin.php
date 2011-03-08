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

    public function  __construct()
    {
        $this->allow('administrator');

        $this->route = new OrionRoute();
        $this->route->addRule('/post/new', 'post_new');
        $this->route->addRule('/post/edit/?', 'post_new');
        $this->route->addRule('/category/?', 'category');
        $this->route->addRule('/post/page/@', 'index');
        $this->route->addRule('/index', 'index');

        $this->useModel('post');
        $this->useModel('category');
    }

    public function _index($offset=0)
    {
        $this->assign('title', $this->title);
        $this->assign('subtitle', 'Labs');

        try {

        }
        catch(OrionException $e)
        {
            $this->assign('output', $e->__toString(), true);
        }

        $this->displayView('index.admin');
    }

    public function _do()
    {
        // parse POST data
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
                           ->limit(1);
                $form->hydrate($post);
                $form->add(OrionForm::HIDDEN, 'action', 'post_edit');
                $this->assign('subtitle', 'Edit post');
                $this->title .= ' | Edit post';
            }
            else
            {
                $form->add(OrionForm::HIDDEN, 'action', 'post_new');
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
        $this->displayView('form.admin');
    }

    public function _error($e)
    {
        if($e == OrionRoute::E_NORULE)
            echo 'Requested URI matches no rule.';

    }
}
?>

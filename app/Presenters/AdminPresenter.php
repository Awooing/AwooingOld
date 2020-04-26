<?php
declare(strict_types=1);

/**
 * This file is part of Awooing.moe
 */

namespace App\Presenters;

use Nette;
use Awoo\Models\MainModel;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Ublaboo\DataGrid\DataGrid;

class AdminPresenter extends BasePresenter
{
    /** @var Context */
    private $database;

    /** @var MainModel */
    private $model;

    /**
     * AdminPresenter constructor.
     * @param Context $db
     * @param MainModel $model
     */
    public function __construct(Context $db, MainModel $model)
    {
        parent::__construct($db, $model);
        $this->database = $db;
        $this->model = $model;
    }

    /**
     * Startup method which checks
     * if user is logged in and allowed to view the admin panel.
     * @throws AbortException
     */
    protected function startup()
    {
        parent::startup(); // TODO: Change the autogenerated stub
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("<script id='41458148941125_denied'>Swal.fire({title: 'Access Denied', content: 'You don\'t have access to this site.', icon:'error', showConfirmButton: false, timer: 1000, timerProgressBar: true});$('#41458148941125_denied').remove();</script>", "script");
            $this->redirect("Homepage:");
        }
        if (!$this->getUser()->isAllowed("admin", "view")) {
            $this->flashMessage("<script id='41458148941125_denied'>Swal.fire({title: 'Access Denied', content: 'You don\'t have access to this site.', icon:'error', showConfirmButton: false, timer: 1000, timerProgressBar: true});$('#41458148941125_denied').remove();</script>", "script");
            $this->redirect("Homepage:");
        }
    }

    /**
     * Gets all Posts from NewsModel
     * and adds them to the template
     * @param int $page
     * @throws Nette\Application\BadRequestException
     * @throws Nette\Application\UI\InvalidLinkException
     */
    public function actionListPosts(int $page=1): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error("Unauthorized", 401);
        } else {
            if (!$this->getUser()->isAllowed("admin", "view")) {
                $this->error("Unauthorized", 401);
            }
        }

        $news = $this->model->news->getArticles();
        $lastPage = 0;
        $this->template->posts = $news->page($page, 10, $lastPage);
        $this->template->page = $page;
        $this->template->last = $lastPage;
        $this->template->delUrl = $this->link("Admin:deletePost");
        $this->template->users = $this->model->user->getUsers();
    }

    public function actionListUsers(int $page=1): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error("You need to be logged in to perform this action", 401);
        }
        if (!$this->getUser()->isAllowed("admin", "view")) {
            $this->error("Unauthorized", 401);
        }

        $users = $this->model->user->getUsers();
        $lastPage = 0;
        $this->template->users = $users->page($page, 10, $lastPage);
        $this->template->page = $page;
        $this->template->last = $lastPage;
        $this->template->delUrl = $this->link("Admin:deleteUser");
        $this->template->deacUrl = $this->link("Admin:deactivateUser");
        $this->template->acUrl = $this->link("Admin:activateUser");
    }

    public function actionEditPost($id): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error("Unauthorized", 401);
        } else {
            if (!$this->getUser()->isAllowed("news", "edit")) {
                $this->error("Unauthorized", 401);
            }
        }

        if ($id === null || !$id) {
            $this->error("Page Not Found", 404);
        }
        $post = $this->model->news->getArticleById($id);
        if (!$post) {
            $this->error("Page Not Found", 404);
        }
        $this['post']->setDefaults($post->toArray());
    }

    public function actionDeletePost($p): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->error("Unauthorized", 401);
        } else {
            if (!$this->getUser()->isAllowed("news", "delete")) {
                $this->error("Unauthorized", 401);
            }
        }

        if ($p === null || !$p) {
            $this->error("Page Not Found", 404);
        }
        $post = $this->model->news->getArticleById($p);
        if (!$post) {
            $this->error("Page Not Found", 404);
        }
        $post->delete();
        $this->redirect("Admin:listPosts");
    }
    /**
     * Creates New/Edit Post Form
     * @return Form
     */
    protected function createComponentPost(): Form {

        $form = new Form;

        $form->setHtmlAttribute("class", "");
        $form->addText('title', 'Post Title:')->setHtmlAttribute("class", "form-control")->setRequired();
        $form->addTextArea('content', null)->setHtmlAttribute("class", "form-control")->setHtmlAttribute("id", "wysiwyg")->setRequired();
        $form->addSubmit('send', 'Send')->setHtmlAttribute("class", "btn btn-primary float-right");

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'div';
        $renderer->wrappers['pair']['container'] = 'dl';
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = 'dd';
        $renderer->wrappers['button']['container'] = null;

        $form->onSuccess[] = [$this, 'processPost'];

        return $form;
    }

    /**
     * Processes the New/Edit Forms
     * @param Form $f
     * @param array $v
     * @throws AbortException
     * @throws Nette\Application\BadRequestException
     */
    public function processPost(Form $f, array $v): void
    {

        $postId = $this->getParameter('id');
        if ($postId) {
            if (!$this->getUser()->isLoggedIn()) {
                $this->error("Unauthorized", 401);
            } else {
                if (!$this->getUser()->isAllowed("news", "edit")) {
                    $this->error("Unauthorized", 401);
                }
            }
            $post = $this->database->table("awoo_posts")->get($postId);
            $post->update($v);
            $this->flashMessage('<script id="script">Swal.fire({title:"Success", text:"The post was edited successfully.", icon:"success"});</script>', "script");
        } else {
            if (!$this->getUser()->isLoggedIn()) {
                $this->error("Unauthorized", 401);
            } else {
                if (!$this->getUser()->isAllowed("news", "create")) {
                    $this->error("Unauthorized", 401);
                }
            }
            $v['user_id'] = $this->getUser()->getIdentity()->getId();
            $post = $this->database->table("awoo_posts")->insert($v);
            $this->flashMessage('<script id="script">Swal.fire({title:"Success", text:"The post was created successfully.", icon:"success"});</script>', "script");
        }
        $this->redirect("Admin:listPosts");
    }

    public function createComponentListUsers($name)
    {
        $grid = new DataGrid($this, $name);

        $grid->setDataSource($this->database->table("awoo_users"));
        $grid->addColumnText('id', '#');
        $grid->addColumnText('username', 'Name');
        $grid->addColumnText('email', 'Email');
        $grid->addColumnText('showAs', 'Show as');
        $grid->addColumnText('role', 'Role');
    }

}
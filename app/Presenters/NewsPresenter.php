<?php
declare(strict_types=1);
/*
 * This file is part of Awooing.moe
 */

namespace App\Presenters;


use Awoo\Models\MainModel;
use Nette\Application\UI\Form;
use Nette\Database\Context;

class NewsPresenter extends BasePresenter
{
    /** @var Context **/
    private $database;
    /** @var MainModel */
    private $model;

    /**
     * BasePresenter constructor.
     * @param Context $db
     * @param MainModel $model
     */
    public function __construct(Context $db, MainModel $model)
    {
        parent::__construct($db, $model);
        $this->database = $db;
        $this->model = $model;
    }


    public function actionView($id): void
    {
        if ($id === null || !$id) {
            $this->error("Page Not Found", 404);
        }
        $post = $this->model->news->getArticleById($id);
        if (!$post) {
            $this->error("Page Not Found", 404);
        }
        $this->template->news = $post;
        $this->template->author = $this->model->user->getUser($post->user_id);
        $this->template->comments = $post->related('comments');
        $this->template->users = $this->model->user;
        $this->template->discord = $this->model->discord;

    }

    public function actionDefault(int $page=1) {
        $news = $this->model->news->getArticles();

        $lastPage = 0;
        $this->template->posts = $news->page($page, 10, $lastPage);
        $this->template->page = $page;
        $this->template->last = $lastPage;
        $this->template->userModel = $this->model->user;
    }

    protected function createComponentComment(): Form
    {
        $form = new Form;
        $form->addTextArea("content", "What do you want to say?")->setRequired(true)->setHtmlAttribute("class", "form-control bg-dark");
        $form->addSubmit("submit", "Send")->setHtmlAttribute("class", "btn btn-primary text-left");
        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'div';
        $renderer->wrappers['pair']['container'] = 'dl';
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = 'dd';
        $form->onSuccess[] = [$this, "processComment"];
        return $form;
    }

    public function processComment(Form $form, \stdClass $vo): void
    {
        $form->reset();
        if (!$this->getUser()->isLoggedIn()) {
            $this->error("You need to be logged in to perform this action", 401);
        }
        if (!$this->getUser()->isAllowed("comments", "create")) {
            $this->error("You're not allowed to perform this action", 401);
        }
        $postId = $this->getParameter("id");
        if (!$postId || $postId === null) {
            $this->error("Parameter 'id' is missing", 404);
        }
        if (!$this->model->news->getArticleById($postId)) {
            $this->error("Post doesn't exist. Can't comment on non-existent post.", 404);
        }
        $this->database->table("awoo_post_comments")->insert([
            "post_id"=>$postId,
            "author_id"=>$this->user->getId(),
            "content"=>$vo->content
        ]);
    }

}
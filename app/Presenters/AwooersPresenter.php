<?php


namespace App\Presenters;


use Awoo\Models\MainModel;
use Nette\Application\UI\Form;
use Nette\Database\Context;

class AwooersPresenter extends BasePresenter
{
    /** @var Context */
    private $database;

    /** @var MainModel  */
    private $model;

    /**
     * AwooersPresenter constructor.
     * @param Context $db
     * @param MainModel $model
     */
    public function __construct(Context $db, MainModel $model)
    {
        parent::__construct($db, $model);
        $this->database = $db;
        $this->model = $model;
    }

    public function actionView($id)
    {
        if (!$id || $id === null) {
            $this->error("Page not found", 404);
        }
        $user = $this->model->user->getUser($id);
        if (!$user || $user === null) {
            $this->error("User not found", 404);
        }
        $this->template->u = $user;
        $this->template->banner = $this->database->table("awoo_user_banners")->get($user->banner);
        $this->template->postCount = sizeof($this->database->table("awoo_posts")->where("user_id = ?", $user->id));
        $this->template->commentCount = sizeof($this->database->table("awoo_post_comments")->where("author_id = ?", $user->id))
                                    + sizeof($this->database->table("awoo_profile_comments")->where("author_id = ?", $user->id));
        $this->template->comments = $this->database->table("awoo_profile_comments")->where("profile_id = ?", $user->id)->order("created_at DESC");
        $this->template->users = $this->model->user;
    }

    protected function createComponentAddUpdate(): Form
    {
        $f = new Form;
        $f->setHtmlAttribute("class", "ajax")->setHtmlAttribute("style", "width:100%");

        $f->addTextArea("content", null)
            ->setHtmlAttribute("class", "form-control")
            ->setHtmlAttribute("placeholder", "Post an update...")
            ->setRequired("This value is required.");

        $f->addSubmit("submit", "Post")
            ->setHtmlAttribute("class", "btn btn-primary float-right");

        $renderer = $f->getRenderer();
        $renderer->wrappers['controls']['container'] = 'div';
        $renderer->wrappers['pair']['container'] = 'dl';
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = 'dd';

        $f->onSuccess[] = [$this, 'processProfileComment'];
        return $f;
    }

    public function processProfileComment(Form $form, array $v): void
    {
        $v['profile_id'] = $this->getParameter("id");
        $v['author_id'] = $this->getUser()->id;
        $this->database->table("awoo_profile_comments")->insert($v);
    }



}
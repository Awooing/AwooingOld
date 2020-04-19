<?php


namespace App\Presenters;


use Awoo\Models\MainModel;
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
        $this->template->posts = sizeof($this->database->table("awoo_posts")->where("user_id = ?", $user->id));
        $this->template->comments = sizeof($this->database->table("awoo_post_comments")->where("author_id = ?", $user->id))
                                    + sizeof($this->database->table("awoo_profile_comments")->where("author_id = ?", $user->id));
    }



}
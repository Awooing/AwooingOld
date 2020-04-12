<?php
declare(strict_types=1);
/*
 * This file is part of Awooing.moe
 */

namespace App\Presenters;


use Awoo\Models\MainModel;
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

    /**
     * Gets all articles from NewsModel
     * and adds them to the template
     */
    public function actionDefault(): void
    {
        $this->template->posts = $this->model->news->getArticles()->order("created_at DESC");
        $this->template->userModel = $this->model->user;
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
    }


}
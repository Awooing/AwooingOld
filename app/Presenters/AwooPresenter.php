<?php


namespace App\Presenters;



use Awoo\Models\MainModel;
use Latte\Runtime\FilterExecutor;
use Latte\Runtime\FilterInfo;
use Latte\Runtime\Filters;
use Nette\Application\Responses\TextResponse;
use Nette\Database\Context;

class AwooPresenter extends BasePresenter
{

    /** @var MainModel */
    private $model;

    /**
     * AwooPresenter constructor.
     * @param Context $db
     * @param MainModel $model
     */
    public function __construct(Context $db, MainModel $model)
    {
        parent::__construct($db, $model);
        $this->model = $model;
    }

    public function actionGet(): void
    {
        $awoo = $this->model->cdn->getRandomAwoo();
        if ($awoo === null || !$awoo) {
            $this->sendJson(["error"=>'var_awoo_null']);
        } else {
            $this->sendJson(["path"=>$this->model->cdn->getFileKey($awoo), "fileSize"=>$this->model->cdn->getSize($awoo), "createdAt"=>$this->model->cdn->getLastModified($awoo)]);
        }
    }

    public function actionNews(): void
    {
        $latest = $this->model->news->getArticles()->order("created_at DESC")->limit(3);
        $posts = array();
        $this->getHttpResponse()->setHeader("Access-Control-Allow-Origin", "*");
        foreach ($latest as $post) {
            $p = $post->toArray();
            array_push($posts, $post->toArray());
        }
        $this->sendJson($posts);
    }

    public function actionAvatar(string $id="630439552389218313"): void
    {
        $this->sendJson([
            "username"=>$this->model->discord->getUserNameById($id),
            "url"=>$this->model->discord->getAvatarUrlByUserId($id)
        ]);
    }

    public function actionRandom(): void
    {
        $this->template->image = $this->model->cdn->getDataUrl() . $this->model->cdn->getRandomAwoo()['Key'];
    }
}
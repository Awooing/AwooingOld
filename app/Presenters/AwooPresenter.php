<?php


namespace App\Presenters;



use Awoo\Models\MainModel;
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

    public function actionRandom(): void
    {
        $this->template->image = $this->model->cdn->getDataUrl() . $this->model->cdn->getRandomAwoo()['Key'];
    }
}
<?php
declare(strict_types=1);

namespace App\Presenters;


use Awoo\Models\MainModel;
use Nette\Database\Context;

class CouncilPresenter extends BasePresenter
{
    /** @var Context */
    private $database;

    /** @var MainModel */
    private $model;

    /**
     * CouncilPresenter constructor.
     * @param Context $db
     * @param MainModel $model
     */
    public function __construct(Context $db, MainModel $model)
    {
        parent::__construct($db, $model);
        $this->database = $db;
        $this->model = $model;
    }

    public function actionDefault()
    {
        // me big dumb
        $this->template->council = $this->database->table("awoo_council")->order("id DESC");
        $this->template->discord = $this->model->discord;
    }
}
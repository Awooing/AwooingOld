<?php
declare(strict_types=1);

/**
 * This file is part of Awooing.moe
 */

namespace App\Presenters;

use Awoo\Models\MainModel;
use Nette\Database\Context;


class HomepagePresenter extends BasePresenter {

    /** @var Context **/
    private $database;
    /** @var MainModel */
    private $model;

    /**
     * HomepagePresenter constructor.
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
     * Gets latest news,
     * adds it to the template
     */
    public function actionDefault(): void
    {
        $latest = $this->model->news->getArticles()->order("created_at DESC")->limit(3);
        $this->template->newstellers = $latest;
        $this->template->userModel = $this->model->user;
    }

}

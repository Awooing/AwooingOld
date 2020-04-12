<?php

declare(strict_types=1);

namespace App\Presenters;

use Awoo\Models\MainModel;
use Nette\Database\Context;


class VotingPresenter extends BasePresenter {

    /** @var Context **/
    private $database;

    /** @var MainModel **/
    private $model;

    /**
     * VotingPresenter constructor.
     * @param Context $db
     * @param MainModel $model
     */
    public function __construct(Context $db, MainModel $model)
    {
        parent::__construct($db, $model);
        $this->database = $db;
        $this->model = $model;
    }

    public function actionApplicants(): void
    {
        $this->template->applicants = $this->model->voting->getApplicantSpeeches();
    }

}

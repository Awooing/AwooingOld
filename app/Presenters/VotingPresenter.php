<?php
declare(strict_types=1);

/**
 * This file is part of Awooing.moe
 */

namespace App\Presenters;

use Awoo\Models\MainModel;
use Cassandra\Date;
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

    public function actionDefault():void
    {
        $this->template->votes = $this->model->voting->getApplicantVotes()->order("votes DESC");
        $this->template->votesAsc = $this->model->voting->getApplicantVotes()->order("votes ASC");

        $dateEnd = new \DateTime("2020-04-14");
        $dateNow = new \DateTime();
        if ($dateEnd < $dateNow) {
            $this->template->setFile("../app/Presenters/templates/donevote.latte");
        }
    }

    /**
     * Gets applicants from VotingModel,
     * adds it to the template
     */
    public function actionApplicants(): void
    {
        $this->template->applicants = $this->model->voting->getApplicantSpeeches();
    }

}

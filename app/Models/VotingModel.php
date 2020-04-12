<?php

declare(strict_types=1);

namespace Awoo\Models;

use Nette\Database\Context;
use Nette\Database\Table\Selection;

class VotingModel {

    /** @var Context **/
    private $database;

    public function __construct(Context $db)
    {
        $this->database = $db;
    }

    public function getApplicantSpeeches(): ?Selection {
        return $this->database->table("awoo_applicants")->order("id ASC");
    }

    public function getSpeechByApplicantName(string $applicant): ?Selection {
        return $this->getApplicantSpeeches()->where("name = ?", $applicant);
    }

}
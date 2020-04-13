<?php
declare(strict_types=1);

/**
 * This file is part of Awooing.moe
 */

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

    /**
     * Gets all applicants,
     * or returns null if table doesn't exist
     * or there are no applicants.
     * @return Selection|null
     */
    public function getApplicantSpeeches(): ?Selection {
        return $this->database->table("awoo_applicants")->order("id ASC");
    }

    /**
     * Gets applicant by his/her name,
     * or returns null if doesn't exist.
     * @param string $applicant
     * @return Selection|null
     */
    public function getSpeechByApplicantName(string $applicant): ?Selection {
        return $this->getApplicantSpeeches()->where("name = ?", $applicant);
    }

    public function getApplicantVotes(): ?Selection {
        return $this->database->table("awoo_votes");
    }

}
<?php
declare(strict_types=1);

namespace Awoo\Models;

/**
 * This file is part of Awooing.moe
 */

use Latte\Engine;
use Nette\Database\Context;
use Nette\Database\Table\ActiveRow;
use Nette\Http\Session;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Mail\SmtpMailer;
use Nette\Security\User;
use Nette\Utils\Json;
use Nette\Utils\Random;

class UserModel
{
    /** @var Context */
    private $database;

    /** @var SmtpMailer */
    private $mailer;

    /**
     * UserModel constructor.
     * @param Context $db
     */
    public function __construct(Context $db)
    {
        $this->database = $db;
        $this->mailer = new SmtpMailer([
            'smtp' => true,
            'host' => "mail.awooing.moe",
            'username' => "noreply@awooing.moe",
            'password' => 'wy&W60$bYVQQsr4ld',
            'secute' => "ssl",
        ]);
    }

    /**
     * This method checks if the login IP
     * matches with the current IP address.
     * This is a precaution against Session Hijacking
     * @param Session $session
     * @param User $user
     * @return bool|null
     */
    public function checkSession(Session $session, User $user): ?bool
    {
        if ($user->isLoggedIn()) {
            if ($user->getIdentity()->loginIP === $_SERVER['REMOTE_ADDR']) {
                return true;
            } else {
                $user->logout(true);
                $session->destroy();
                return false;
            }
        } else {
            return null;
        }
    }

    /**
     * Gets user with id in param $id,
     * or returns null if user doesn't exist
     * @param $id
     * @return ActiveRow|null
     */
    public function getUser($id): ?ActiveRow {
        return $this->database->table("awoo_users")->get($id);
    }

    /**
     * Checks if user is banned, returns
     * either true or false, in case the user
     * doesn't exist it returns null
     * @param $id
     * @return bool|null
     */
    public function isUserBanned($id): ?bool {
        $user = $this->getUser($id);
        if (!$user) {
            return false;
        }
        if ($user->banned === 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the IP from param $ip is banned.
     * @param $ip
     * @return bool
     */
    public function isIPBanned($ip): bool {
        /*$query = $this->database->table("awoo_banned_ip")->where("address === ?", $ip);
        if (!$query) {
            return true;
        } else {
            return false;
        }*/
    }

    /**
     * Signs out the user and destroys the session.
     * @param Session $session
     * @param User $user
     * @return bool
     */
    public function logout(Session $session, User $user): bool
    {
        if ($user->isLoggedIn()) {
            $user->logout(true);
            $session->destroy();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Gets user by username,
     * or returns null if user doesn't exist
     * @param string $username
     * @return ActiveRow|null
     */
    public function getUserByName(string $username): ?ActiveRow {
        return $this->database->table("awoo_users")->where("username = ?", $username)->fetch();
    }

    /**
     * Gets user by email,
     * or returns null if user doesn't exist
     * @param string $email
     * @return ActiveRow|null
     */
    public function getUserByEmail(string $email): ?ActiveRow {
        return $this->database->table("awoo_users")->where("email = ?", $email)->fetch();
    }

    /**
     * Sends verification email to email in param $to,
     * assigns verification code to user id from param $userId.
     * Used in registration process.
     * @param string $to
     * @param $userId
     */
    public function sendVerifyEmail(string $to, $userId)
    {
        $latte = new Engine;
        $email = new Message;
        $code = Random::generate(16, "a-z0-9");
        $action = "emailVerify";
        $this->database->table("awoo_verify")->insert(['id' => $code, 'user_id' => $userId, 'type' => $action]);
        $info = [
            "name" => $this->database->table("awoo_users")->get($userId)->username,
            "url" => '/auth/verify?a=' . $userId . '&c=' . $code . '&ac=' . $action,
            "baseUrl" => "https://awooing.moe"
        ];
        $email
            ->setFrom("noreply@awooing.moe", "The Awooing Place")
            ->addTo($to)
            ->setSubject("Verify your email")
            ->setHtmlBody($latte->renderToString("../app/Presenters/templates/email.latte", $info));
        $this->mailer->send($email);
    }


    /**
     * Verifies the account, returns JSON
     * with error: success if succeeded or returns error.
     * It checks if the account matches the code and action.
     * @param string $a
     * @param string $c
     * @param string $ac
     * @return string
     * @throws \Nette\Utils\JsonException
     */
    public function verifyUser(string $a, string $c, string $ac): string
    {
        if ($a == null || $c == null || $ac == null) { return Json::encode(["error"=>"x1_varnull"]); }
        $user = $this->getUser($a);
        $code = $this->database->table("awoo_verify")->get($c);
        if (!$user || !$code) { return Json::encode(["error"=>"x2_invalid"]); }
        if ($code->user_id != $a || $code->type != $ac || $user->active == 1) { return Json::encode(["error"=>"x3_mismatch"]); }

        $this->activateUser($a);
        $code->delete();
        return Json::encode(["error"=>"success"]);
    }

}
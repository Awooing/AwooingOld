<?php

namespace Awoo\Models;

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
    public function getUser($id): ?ActiveRow {
        return $this->database->table("awoo_users")->get($id);
    }

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
    public function isIPBanned($ip): bool {
        $query = $this->database->table("awoo_banned_ip")->where("address = ?", $ip);
        if (!$query) {
            return true;
        } else {
            return false;
        }
    }

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

    public function getUserByName(string $username): ?ActiveRow {
        return $this->database->table("awoo_users")->where("username = ?", $username)->fetch();
    }
    
    public function getUserByEmail(string $email): ?ActiveRow {
        return $this->database->table("awoo_users")->where("email = ?", $email)->fetch();
    }
    
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
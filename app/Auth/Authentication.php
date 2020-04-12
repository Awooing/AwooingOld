<?php

namespace Awoo\Auth;

use Nette;
use Nette\Http\Session;
use Nette\Security\IAuthenticator;
use Nette\Security\IIdentity;
use Nette\Database\Context;
use Nette\Security\Passwords;
use Nette\Security\AuthenticationException;

class Authentication implements IAuthenticator {

    /** @var Context **/
    private $database;

    /** @var Passwords **/
    private $passwords;

    /** @var Session **/
    private $session;

    public function __construct(Context $db, Passwords $pw, Session $sess)
    {
        $this->database = $db;
        $this->passwords = $pw;
        $this->session = $sess;
    }

    public function authenticate(array $credentials): IIdentity
    {
        [$username, $password] = $credentials;

        $user = $this->database->table("awoo_users")->where("username = ?", $username)->fetch();

        if (!$user) {
            throw new AuthenticationException("ACC_NOT_FOUND");
        }

        if (!$this->passwords->verify($password, $user->password)) {
            throw new AuthenticationException("ACC_WRONG_PASS");
        }

        $ip = $this->session->getSection("ip");
        $ip->value = $_SERVER['REMOTE_ADDR'];

        if (!$user->active) {
            throw new AuthenticationException("ACC_NOT_VERIFIED");
        }


        return new Nette\Security\Identity($user->id, $user->role, [
            'id'=>$user->id,
            'username'=>$user->username,
            'email'=>$user->email,
            'role'=>$user->role,
            'loginIP'=>$_SERVER['REMOTE_ADDR']
        ]);
    }
}
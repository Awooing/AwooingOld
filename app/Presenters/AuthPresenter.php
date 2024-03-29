<?php
declare(strict_types=1);

/**
 * This file is part of Awooing.moe
 */

namespace App\Presenters;
use Awoo\OAuth\Discord;
use Awoo\Models\MainModel;
use Awoo\OAuth\Flow\DiscordAuthCodeFlow;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Security\Passwords;

class AuthPresenter extends BasePresenter
{
    /** @var Context */
    private $database;

    /** @var MainModel */
    private $model;

    /** @var Passwords */
    private $passwords;

    /** @var DiscordAuthCodeFlow */
    private $flow;

    /** @var Discord */
    private $discord;

    /**
     * AuthPresenter constructor.
     * @param Context $db
     * @param Passwords $pw
     * @param MainModel $model
     * @param DiscordAuthCodeFlow $flow
     */
    public function __construct(Context $db, Passwords $pw, MainModel $model, DiscordAuthCodeFlow $flow)
    {
        parent::__construct($db, $model);
        $this->database = $db;
        $this->model = $model;
        $this->passwords = $pw;
        $this->flow = $flow;
        $this->discord = new Discord($this->flow);
    }

    /**
     * Logout Action,
     * calls method logout($sess, $user) in UserModel,
     * redirects to Homepage
     * @throws AbortException
     */
    public function actionLogout(): void
    {
        if ($this->model->user->logout($this->getSession(), $this->getUser())) {
            $this->flashMessage('<script id="script98776231_login">Swal.fire({title:"Success",text:"You have been logged out successfully.",icon:"success",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script98776231_login").remove();</script>', "script");
        } else {
            $this->flashMessage('<script id="script4465523113_error">Swal.fire({title:"Warning",text:"You\'re currently not logged into any account.",icon:"warning",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script4465523113_error").remove();</script>', "script");
        }
        $this->redirect("Homepage:default");
    }

    public function actionVerify($a, $c, $ac): void
    {
        $result = json_decode($this->model->user->verifyUser($a, $c, $ac));
        if ($result->error === "success") {
            $this->flashMessage('<script id="script">Swal.fire({title:"Success",text:"Your account has been successfully verified!",icon: "success",showConfirmButton:false, showCancelButton:false, timer:1250, timerProgressBar:true});("#script").remove();</script>', "script");
        } else {
            $this->flashMessage("'<script id='script'>Swal.fire({title:'Invalid verify link',text:'The link you try to use is invalid. Error code $result->error',icon: 'warning', confirmButtonText: 'Okay', showCancelButton:false});('#script').remove();</script>'", "script");
        }
        $this->redirect("Homepage:default");
    }

    /**
     * Register Component
     * @return Form
     */
    protected function createComponentRegister(): Form
    {
        $form = new Form();

        $form->setHtmlAttribute("class", "ajax");
        $form->addText('username', "Username:")->setRequired("This field is required.")->setHtmlAttribute("class", "form-control");
        $form->addEmail('email', "Your Email:")->setRequired("This field is required.")->setHtmlAttribute("class", "form-control");
        $form->addPassword('password', "New Password:")->setRequired("This field is required.")->setHtmlAttribute("class", "form-control");
        //$form->addReCaptcha('recaptcha', $label = "", $required = TRUE);
        $form->addSubmit("submit", "Register your account")->setHtmlAttribute("class", "btn btn-primary my-4");
        $form->onSuccess[] = [$this, 'processRegister'];

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'div';
        $renderer->wrappers['pair']['container'] = 'dl';
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = 'dd';

        return $form;
    }

    /**
     * Processes Registering
     * @param Form $f
     * @param \stdClass $vo
     * @throws AbortException
     */
    public function processRegister(Form $f, \stdClass $vo): void
    {
        if ($this->getUser()->isLoggedIn()) {
            $this->redirect("Homepage:default");
        }
        if (!$this->model->user->getUserByName($vo->username)) {
            if (!$this->model->user->getUserByEmail($vo->email)) {
                $user = $this->database->table("awoo_users")->insert([
                    "username" => $vo->username,
                    "email" => $vo->email,
                    "password" => $this->passwords->hash($vo->password),
                    "showAs" => $vo->username,
                    "role" => "member",
                    "active" => "0",
                    "banned" => "0",
                    "profile_picture" => "https://via.placeholder.com/150",
                    "banner" => "none",
                    "location" => "unset"
                ]);
                $this->model->user->sendVerifyEmail($vo->email, $user->id);
                $this->flashMessage('<script id="script983346418551_register">Swal.fire({title:"Success", text:"You have been registered successfully", icon:"success"}).then(() => {Swal.fire({title:"Verify your email",text:"You need to verify your email before logging in.",icon: "warning", iconHtml:"<i class=\'far fa-envelope\' style=\'font-size:3rem;\'></i>", confirmButtonText: "Okay", showCancelButton:false});}); $("#script983346418551_register").remove();</script>', "script");
                $this->redirect("Homepage:default");
            } else {
                $f->addError("This email is already used.");
            }
        } else {
            $f->addError("This username is already used.");
        }
    }

    public function actionDiscordAuthenticate(): void
    {
        $this->discord->authenticate($this);
    }

    public function actionDiscordAuthorize(): void
    {
        $owner = $this->discord->authorize($this->getHttpRequest()->getQuery(), $this);
        if ($this->model->user->getUserByEmail($owner->getEmail()) !== null) {
            $this->flashMessage('<script id="script858362266612366411223145_reg">Swal.fire({title:"Email already used",text:"This email is already associated with another account.",icon:"error",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script858362266612366411223145_reg").remove();</script>', "script");
            $this->redirect("Homepage:default");
        }
        $this['registerDiscord']->setDefaults(["username" => preg_replace("/[^a-zA-Z]/", "", $owner->getUsername())]);
        $this->template->owner = $owner;

    }

    protected function createComponentRegisterDiscord(): Form
    {
        $form = new Form;
        //$form->setHtmlAttribute("class", "ajax");

        $form->addText("username", "Username")->setHtmlAttribute("class", "form-control")
            ->setRequired();

        $form->addPassword("password", "Password")->setHtmlAttribute("class", "form-control")
            ->setRequired();
        $form->addPassword("repeatpw", "Repeat Password")->setHtmlAttribute("class", "form-control")
            ->setRequired();

        //$form->addReCaptcha('recaptcha', $label = "", $required = TRUE);

        $form->addSubmit("submit", "Register using Discord")->setHtmlAttribute("class", "btn btn-primary my-4");

        $form->onSuccess[] = [$this, 'processDiscordReg'];

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'div';
        $renderer->wrappers['pair']['container'] = 'dl';
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = 'dd';

        return $form;
    }

    public function processDiscordReg(Form $f, \stdClass $vo): void
    {
        if ($vo->password === $vo->repeatpw) {
            if (!$this->model->user->getUserByName($vo->username)) {
                if (!$this->model->user->getUserByEmail("c")) {
                    $user = $this->database->table("awoo_users")->insert([
                        "username" => $vo->username,
                        "email" => "c",
                        "password" => $this->passwords->hash($vo->password),
                        "showAs" => $vo->username,
                        "role" => "member",
                        "active" => "0",
                        "banned" => "0",
                        "discord_id" => "id",
                        "location" => "unset"
                    ]);
                    $this->model->user->sendVerifyEmail("justatest@post.cz", $user->id);
                } else {
                    $this->flashMessage('<script id="script858362266612366411223145_reg">Swal.fire({title:"Email already used",text:"This email is already associated with another account.",icon:"error",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script858362266612366411223145_reg").remove();</script>', "script");
                }
            } else {
                $this->flashMessage('<script id="script858362266612366411223145_reg">Swal.fire({title:"Username already used",text:"This username is already used by someone else.",icon:"error",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script858362266612366411223145_reg").remove();</script>', "script");

            }
        } else {
            $this->flashMessage('<script id="script858362266612366411223145_reg">Swal.fire({title:"Passwords doen\'t",text:"The passwords are not the same. Please check them and try again.",icon:"error",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script858362266612366411223145_reg").remove();</script>', "script");
        }
        $this->redirect("Homepage:default");
    }

}
<?php

namespace App\Presenters;
use Awoo\Models\MainModel;
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

    /**
     * AuthPresenter constructor.
     * @param Context $db
     * @param Passwords $pw
     * @param MainModel $model
     */
    public function __construct(Context $db, Passwords $pw, MainModel $model)
    {
        parent::__construct($db, $model);
        $this->database = $db;
        $this->model = $model;
        $this->passwords = $pw;
    }

    public function actionLogout(): void
    {
        if ($this->model->user->logout($this->getSession(), $this->getUser())) {
            $this->flashMessage('<script id="script98776231_login">Swal.fire({title:"Success",text:"You have been logged out successfully.",icon:"success",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script98776231_login").remove();</script>', "script");
        } else {
            $this->flashMessage('<script id="script4465523113_error">Swal.fire({title:"Warning",text:"You\'re currently not logged into any account.",icon:"warning",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script4465523113_error").remove();</script>', "script");
        }
        $this->redirect("Homepage:default");
    }

    protected function createComponentRegister(): Form
    {
        $form = new Form();

        $form->setHtmlAttribute("class", "ajax");
        $form->addText('username', "Username:")->setRequired("This field is required.")->setHtmlAttribute("class", "form-control");
        $form->addEmail('email', "Your Email:")->setRequired("This field is required.")->setHtmlAttribute("class", "form-control");
        $form->addPassword('password', "New Password:")->setRequired("This field is required.")->setHtmlAttribute("class", "form-control");
        // TODO: Add ReCaptcha
        $form->addSubmit("submit", "Register your account")->setHtmlAttribute("class", "btn btn-primary my-4");
        $form->onSuccess[] = [$this, 'processRegister'];

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'div';
        $renderer->wrappers['pair']['container'] = 'dl';
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = 'dd';

        return $form;
    }

    public function processRegister(Form $f, \stdClass $vo): void
    {
        if (!$this->model->user->getUserByName($vo->username)) {
            if (!$this->model->user->getUserByEmail($vo->email)) {
                $user = $this->database->table("awoo_users")->insert([
                    "username" => $vo->username,
                    "email" => $vo->email,
                    "password" => $this->passwords->hash($vo->password),
                    "showAs" => $vo->username,
                    "role" => "member",
                    "active" => "0",
                    "banned" => "0"
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




}
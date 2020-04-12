<?php

namespace Awoo\Models;

use Nette\Application\UI\Form;
use Nette\Database\Context;
use Nette\Http\Session;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\User;
use stdClass;

class MainModel
{

    /** @var Context */
    private $database;

    /** @var Passwords */
    private $passwords;

    /** @var VotingModel */
    public $voting;

    /** @var UserModel */
    public $user;

    /**
     * MainModel constructor.
     * @param Context $db
     * @param Passwords $pw
     * @param VotingModel $vote
     * @param UserModel $user
     */
    public function __construct(Context $db, Passwords $pw, VotingModel $vote, UserModel $user)
    {
        $this->database = $db;
        $this->passwords = $pw;
        $this->voting = $vote;
        $this->user = $user;
    }

    /* Components */

    /**
     * Creates Login Form
     * @param string $align
     * @return Form
     */
    public function createLoginForm(string $align): Form
    {
        $form = new Form();
        $form->setHtmlAttribute("class", "ajax");

        $form->addText('username', "Username:")->setRequired("This field is required.")->setHtmlAttribute("class", "form-control")->setHtmlAttribute("placeholder", "Username");
        $form->addPassword('password', "Password:")->setRequired("This field is required.")->setHtmlAttribute("class", "form-control")->setHtmlAttribute("placeholder", "Password");
        // TODO: Add ReCaptcha
        $form->addSubmit("submit", "Login")->setHtmlAttribute("class", "btn btn-primary my-4");

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'div';
        $renderer->wrappers['pair']['container'] = 'dl';
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = "dd style='text-align:$align;'";

        return $form;
    }

    /**
     * Login Processing
     * @param Session $session
     * @param User $user
     * @param stdClass $vo
     * @return string
     */
    public function login(Session $session, User $user, stdClass $vo): string
    {
        $removeModal = "console.log('(awoo) removed backdrop for login modal');$('.modal-backdrop').remove();$('body').attr('class', $('body').attr('class').replace('modal-open', ''));";
        if ($user->isLoggedIn()) { return '<script id="script989443381216_procLogin">' . $removeModal . 'Swal.fire({title:"Warning",text:"You\'re already logged in.",icon:"warning",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script989443381216_procLogin").remove();</script>'; }
        try {
            $user->login($vo->username, $vo->password);
            return  '<script id="script3985163847415_procLogSuc">' . $removeModal . 'Swal.fire({title:"Success",text:"You have been logged in successfully.",icon:"success",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script3985163847415_procLogSuc").remove();</script>';
        } catch (AuthenticationException $e) {
            switch ($e->getMessage()) {
                case "ACC_NOT_VERIFIED":
                    return '<script id="script9867412814_procLoginMail">' . $removeModal . 'Swal.fire({title:"Verify your email",text:"You need to verify your email before logging in.",icon: "warning", iconHtml:"<i class=\'far fa-envelope\' style=\'font-size:3rem;\'></i>", confirmButtonText: "Okay", showCancelButton:false});$("#script9867412814_procLoginMail").remove();</script>';
                    break;
                case "ACC_NOT_FOUND":
                    return '<script id="script114585415854_notFound">' . $removeModal . 'Swal.fire({title:"Account doesn\'t exist",text:"Please check your username and try again.",icon:"error",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script114585415854_notFound").remove();</script>';
                    break;
                case "ACC_WRONG_PASS":
                    return '<script id="script458488415463_logProcIncorrectPw">' . $removeModal . 'Swal.fire({title:"Incorrect Password",text:"Check your password and try again.",icon:"error",showConfirmButton: false,timer:1250,timerProgressBar:true});$("#script458488415463_logProcIncorrectPw").remove();</script>';
                    break;
                default:
                    return '<script id="script794157761036654695_unknownErr">' . $removeModal . 'Swal.fire({title:"Unknown error occurred",text:"Please contact the administrator.",icon:"error"});$("#script_794157761036654695_unknownErr").remove();</script>';
                    break;
            }
        }
    }
}
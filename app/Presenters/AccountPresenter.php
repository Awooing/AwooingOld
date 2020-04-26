<?php
declare(strict_types=1);

/**
 * This file is part of Awooing.moe
 */

namespace App\Presenters;

use Nette;
use Awoo\Models\MainModel;
use Nette\Application\AbortException;
use Nette\Application\UI\Form;
use Nette\Database\Context;

class AccountPresenter extends BasePresenter
{
    /** @var Context */
    private $database;

    /** @var MainModel */
    private $model;

    /** @var Nette\Security\Passwords */
    private $passwords;

    /**
     * AccountPresenter constructor.
     * @param Context $db
     * @param MainModel $model
     * @param Nette\Security\Passwords $pw
     */
    public function __construct(Context $db, MainModel $model, Nette\Security\Passwords $pw)
    {
        parent::__construct($db, $model);
        $this->database = $db;
        $this->model = $model;
        $this->passwords = $pw;
    }

    /**
     * Startup method which checks
     * if user is logged in and allowed to view the admin panel.
     * @throws AbortException
     */
    protected function startup()
    {
        parent::startup(); // TODO: Change the autogenerated stub
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("<script id='41458148941125_denied'>Swal.fire({title: 'Not logged in!', content: 'You need to be logged in to access this page.', icon:'error', showConfirmButton: false, timer: 1000, timerProgressBar: true});$('#41458148941125_denied').remove();</script>", "script");
            $this->redirect("Homepage:");
        }
    }

    public function actionDefault()
    {
        $this->redirect(":settings");
    }

    public function actionSettings()
    {

    }

    protected function createComponentSettings(): Form
    {
        $user = $this->model->user->getUser($this->getUser()->getId());
        $form = new Form;
        $form->setHtmlAttribute("class", "awoo-wrapper");
        $form->addEmail("email", "Your Email")->setDefaultValue($user->email)->setHtmlAttribute("class", "form-control");
        $form->addText("showAs", "Show your name as")->setDefaultValue($user->showAs)->setHtmlAttribute("class", "form-control");
        $form->addText("location", "Location")->setDefaultValue($user->location)->setHtmlAttribute("class", "form-control");
        $form->addPassword("password", "Your password (to save changes)")
            ->setHtmlAttribute("class", "form-control");
        $form->addReCaptcha('recaptcha', $label = "", $required = TRUE);
        $form->addSubmit("submit", "Save settings")->setHtmlAttribute("class", "btn btn-primary");

        $renderer = $form->getRenderer();
        $renderer->wrappers['controls']['container'] = 'div';
        $renderer->wrappers['pair']['container'] = 'dl';
        $renderer->wrappers['label']['container'] = 'dt';
        $renderer->wrappers['control']['container'] = 'dd';

        $form->onSuccess[] = [$this, "processSettingsUpdate"];
        return $form;
    }

    public function processSettingsUpdate(Form $f, \stdClass $vo): void
    {
        if (!$this->getUser()->isLoggedIn()) {
            $this->flashMessage("<script id='6964563385_settingsunlogged'>Swal.fire({title: 'You need to be logged in', text: 'You need to be logged in to change your profile settings.', icon:'error', showConfirmButton: false, timer: 1000, timerProgressBar: true});$('#41458148941125_denied').remove();</script>", "script");
            $this->redirect("Homepage:");
        }
        $user = $this->model->user->getUser($this->getUser()->getId());
        if (!$user) {
            $this->flashMessage("<script id='6964563385_settingsunlogged'>Swal.fire({title: 'Account not found', text: 'Your account doesn\'t exist anymore, therefore you were automatically logged out.', icon:'error', showConfirmButton: false, timer: 1000, timerProgressBar: true});$('#41458148941125_denied').remove();</script>", "script");
        } else {
            if ($this->passwords->verify($vo->password, $user->password)) {
                $this->model->user->getUser($this->getUser()->getId())->update([
                    "email"=>$vo->email,
                    "showAs"=>$vo->showAs,
                    "location"=>$vo->location
                ]);
                $this->flashMessage("<script id='43346337412651_settingssuccess'>Swal.fire({title: 'Saved', text: 'Settings successfully changed.', icon:'success', showConfirmButton: false, timer: 1000, timerProgressBar: true});$('#41458148941125_denied').remove();</script>", "script");
            } else {
                $this->flashMessage("<script id='6964563385_settingswrongpass'>Swal.fire({title: 'Incorrect Password', text: 'The passwords you entered isn\'t correct or you haven\'t entered one.', icon:'error', showConfirmButton: false, timer: 1000, timerProgressBar: true});$('#41458148941125_denied').remove();</script>", "script");
            }
        }
    }
}
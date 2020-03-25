<?php
declare(strict_types = 1);

namespace web\Presenters;


use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\AuthenticationException;

class LoginPresenter extends Presenter
{

    protected function startup(): void
    {
        parent::startup();
        if ($this->getUser()->isLoggedIn() && $this->getAction() !== 'logout') {
            $this->redirect('Homepage:');
        }
    }

    public function actionLogout(): void
    {
        $this->getUser()->logout(true);
        $this->redirect('default');
    }

    public function createComponentLogin(): Form
    {
        $form = new Form();
        $form->addText('username', 'Přihlašovací jméno')->setRequired(true);
        $form->addPassword('password', 'Heslo')->setRequired(true);
        $form->addSubmit('send', 'Přihlásit se')->onClick[] = [$this, 'login'];
        return $form;
    }

    public function login(SubmitButton $bt): void
    {
        $form = $bt->getForm();
        if ($form) {
            $values = $form->getValues();
            if ($values) {
                try {
                    $this->getUser()->setExpiration('180 days');
                    $this->getUser()->login($values->username, $values->password);
                    $this->redirect('Homepage:');
                } catch (AuthenticationException $e) {
                    $this->flashMessage('Nepodařilo se přihlásit. Uživatelské jméno nebo heslo je nesprávné.', 'alert-warning');
                }
            }
        }
    }
}

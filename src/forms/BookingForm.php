<?php

namespace LZaplata\Booking\Forms;


use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;

class BookingForm extends Control
{
    /** @var array */
    public $onFormValidate;

    /** @var array */
    public $onFormSucceeded;

    /** @var DateTime */
    private $dateTime;

    /**
     * @return Form
     */
    public function createComponentForm()
    {
        $form = new Form();

        $form->addText("name", "Jméno")
            ->setRequired("Vyplňte prosím jméno");

        $form->addText("surname", "Příjmení")
            ->setRequired("Vyplňte prosím příjmení");

        $form->addText("street", "Ulice")
            ->setRequired("Vyplňte prosím ulici");

        $form->addText("street_no", "Č. p.")
            ->setRequired("Vyplňte prosím číslo poštovné");

        $form->addText("city", "Město")
            ->setRequired("Vyplňte prosím město");

        $form->addText("zip", "PSČ")
            ->setRequired("Vyplňte prosím poštovní směrovací číslo");

        $form->addText("mail", "Mail")
            ->setRequired("Vyplňte prosím mail")
            ->addRule(Form::EMAIL, "Nesprávný formát mailu");

        $form->addText("amount", "Počet míst")
            ->setType("number")
            ->setDefaultValue(1)
            ->addRule(Form::FILLED, "Vyplňte prosím počet míst")
            ->addRule(Form::NUMERIC, "Počet míst musí být číslo");

        $form->addTextArea("text", "Poznámka");

        $form->addSubmit("send", "Odeslat rezervaci");

        $form->onValidate[] = [$this, "formValidate"];
        $form->onSuccess[] = [$this, "formSucceeded"];

        return $form;
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     * @return void
     */
    public function formValidate(Form $form, ArrayHash $values)
    {
        $this->onFormValidate($this, $form, $values);
    }

    /**
     * @param Form $form
     * @param ArrayHash $values
     * @return void
     */
    public function formSucceeded($form, $values)
    {
        $this->onFormSucceeded($this, $values);
    }

    /**
     * @return void
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . "/templates/bookingForm.latte");
        $this->template->dateTime = DateTime::from($_GET["bohumirBookingRoom-dateTime"]);
        $this->template->render();
    }
}

interface IBookingFormFactory
{
    /** @return BookingForm */
    public function create();
}
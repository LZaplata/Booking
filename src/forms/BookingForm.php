<?php

namespace LZaplata\Booking\Forms;


use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Nette\Utils\DateTime;
use Nette\Utils\Html;

class BookingForm extends Control
{
    /** @var array */
    public $onFormValidate;

    /** @var array */
    public $onFormSucceeded;

    /** @var DateTime */
    private $dateTime;

    /** @var string */
    private $gdprLink;

    /** @var array */
    private $settings;

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

        if (!isset($this->getSettings()["street"]) || (isset($this->getSettings()["street"]) && $this->getSettings()["street"]["visible"])) {
            $form->addText("street", "Ulice")
                ->setRequired(
                    !isset($this->getSettings()["street"]) || (isset($this->getSettings()["street"]) && $this->getSettings()["street"]["required"]) ?
                        "Vyplňte prosím ulici" :
                        false
                );

            $form->addText("street_no", "Č. p.")
                ->setRequired("Vyplňte prosím číslo poštovné");
        }

        $form->addText("city", "Město")
            ->setRequired(
                !isset($this->getSettings()["street"]) || (isset($this->getSettings()["street"]) && $this->getSettings()["street"]["required"]) ?
                    "Vyplňte prosím město" :
                    false
            );

        if (!isset($this->getSettings()["zip"]) || (isset($this->getSettings()["zip"]) && $this->getSettings()["zip"]["visible"])) {
            $form->addText("zip", "PSČ")
                ->setRequired(
                    !isset($this->getSettings()["zip"]) || (isset($this->getSettings()["zip"]) && $this->getSettings()["zip"]["required"]) ?
                        "Vyplňte prosím poštovní směrovací číslo" :
                        false
                );
        }

        $form->addText("mail", "E-mail")
            ->setRequired("Vyplňte prosím e-mail")
            ->addRule(Form::EMAIL, "Nesprávný formát e-mailu");

        $form->addText("phone", "Telefon")
            ->setEmptyValue("+420");

        $form->addText("amount", "Počet míst")
            ->setType("number")
            ->setHtmlAttribute("min", 1)
            ->setDefaultValue(1)
            ->addRule(Form::FILLED, "Vyplňte prosím počet míst")
            ->addRule(Form::NUMERIC, "Počet míst musí být číslo");

        $form->addTextArea("text", "Poznámka");

        $form->addCheckbox("gdpr", Html::el("span")->addHtml("Souhlasím se ")->add(Html::el("a")->href($this->getGdprLink())->addText("zpracování osobních údajů pro potřeby rezervace.")))
            ->setRequired("Musíte souhlasit se zpracování osobních údajů")
            ->setOmitted();

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
     * @return string
     */
    public function getGdprLink()
    {
        return $this->gdprLink;
    }

    /**
     * @param string $link
     * @return void
     */
    public function setGdprLink($link)
    {
        $this->gdprLink = $link;
    }

    /**
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @param DateTime $dateTime
     * @return void
     */
    public function setDateTime(DateTime $dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param array $settings
     * @return void
     */
    public function setSettings(array $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return void
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . "/templates/bookingForm.latte");
        $this->template->dateTime = $this->getDateTime();
        $this->template->settings = $this->getSettings();
        $this->template->render();
    }
}

interface IBookingFormFactory
{
    /** @return BookingForm */
    public function create();
}
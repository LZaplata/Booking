<?php

namespace LZaplata\Booking\Components;


use LZaplata\Booking\Day;
use LZaplata\Booking\Exceptions\DayException;
use LZaplata\Booking\Forms\BookingForm;
use LZaplata\Booking\Forms\IBookingFormFactory;
use LZaplata\Booking\Loaders\IDatabaseLoader;
use LZaplata\Booking\Mail;
use mysql_xdevapi\Exception;
use Nette\Application\UI\Control;
use Nette\Application\UI\Form;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Bridges\ApplicationLatte\TemplateFactory;
use Nette\Database\Table\ActiveRow;
use Nette\InvalidArgumentException;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\Utils\ArrayHash;
use Nette\Utils\Arrays;
use Nette\Utils\DateTime;
use Nette\Utils\Strings;
use Nette\Utils\Validators;

class RoomControl extends Control
{
    /** @var IDatabaseLoader */
    private $databaseLoader;

    /** @var string */
    private $name;

    /** @var string */
    private $id;

    /** @var array */
    private $days;

    /** @var \DateInterval */
    private $interval;

    /** @var int */
    private $capacity = 1;

    /** @var string */
    private $templateFile;

    /** @var DateTime */
    private $startDateTime;

    /** @var DateTime */
    private $endDateTime;

    /** @persistent int */
    public $year;

    /** @persistent int */
    public $week;

    /** @var array */
    private $bookings;

    /** @var bool */
    private $isBookingFormVisible = false;

    /** @var BookingForm */
    private $bookingFormFactory;

    /** @persistent */
    public $bookingFormDateTime;

    /** @var array */
    private $disabledPeriods = [];

    /** @var Mail */
    private $customerMail;

    /** @var Mail */
    private $officeMail;

    /** @var ActiveRow */
    private $booking;

    /** @var string */
    private $componentName;

    /**
     * RoomControl constructor.
     * @param string $name
     * @param string $componentName
     * @param IDatabaseLoader $databaseLoader
     * @param IBookingFormFactory $bookingFormFactory
     * @param Mail $mail
     * @return void
     */
    public function __construct($name, $componentName, IDatabaseLoader $databaseLoader, IBookingFormFactory $bookingFormFactory, Mail $mail)
    {
        $this->componentName = $componentName;
        $this->databaseLoader = $databaseLoader;
        $this->bookingFormFactory = $bookingFormFactory;
        $this->customerMail = clone $mail;
        $this->officeMail = clone $mail;

        if (is_string($name)) {
            $this->setRoomName($name);
        }

        if (!$this->year) {
            $this->year = date("Y");
        }

        if (!$this->week) {
            $this->week = date("W");
        }
    }

    /**
     * @param string $name
     * @return void
     */
    public function setRoomName($name)
    {
        $this->name = $name;
        $this->id = base64_encode($name);
    }

    /**
     * @return null|string
     */
    public function getRoomName()
    {
        return $this->name;
    }

    /**
     * @param array $days
     * @throws \Exception
     * @return void
     */
    public function setDays(array $days)
    {
        foreach ($days as $day) {
            if (!$day->isOk()) {
                throw new \Exception("You must set day of week, start time and end time for all days.");
            } else {
                if (!$this->startDateTime || $this->startDateTime > $day->getStartDateTime()) {
                    $this->startDateTime = $day->getStartDateTime();
                }

                if (!$this->endDateTime || $this->endDateTime < $day->getEndDateTime()) {
                    $this->endDateTime = $day->getEndDateTime();
                }
            }

            $this->days[$day->getDayOfWeek()] = $day;
        }
    }

    /**
     * @param \DateInterval $interval
     * @return void
     */
    public function setInterval(\DateInterval $interval)
    {
        $this->interval = $interval;
    }

    /**
     * @param int $capacity
     * @throws \Nette\Utils\AssertionException
     */
    public function setCapacity($capacity)
    {
        Validators::assert($capacity, "int", "Argument 1");

        $this->capacity = $capacity;
    }

    /**
     * @return array
     */
    private function getBookings()
    {
        return $this->databaseLoader->getBookingTable()
            ->select("*, SUM(amount) AS total")
            ->where("year", $this->year)
            ->where("week", $this->week)
            ->where("room_id", $this->id)
            ->group("hour, minute, day_of_week")
            ->fetchAssoc("day_of_week|hour|minute=total");
    }

    /**
     * @param int $dayOfWeek
     * @param int $hour
     * @param int $minute
     * @return int|mixed
     */
    public function getPeriodBookings($dayOfWeek, $hour, $minute)
    {
        try {
            return Arrays::get($this->bookings, [$dayOfWeek, $hour, $minute]);
        } catch (InvalidArgumentException $exception) {
            return 0;
        }
    }

    /**
     * @return void
     */
    public function handleShowBookingForm($dateTime)
    {
        $this->isBookingFormVisible = true;
        $this->bookingFormDateTime = $dateTime;
    }

    /**
     * @return BookingForm
     */
    protected function createComponentBookingForm()
    {
        $control = $this->bookingFormFactory->create();

        $control->onFormValidate[] = function (BookingForm $control, Form $form, ArrayHash $values) {
            $this->handleShowBookingForm($this->bookingFormDateTime);

            $dateTime = DateTime::from($this->bookingFormDateTime);
            $bookings = $this->getBookings();

            try {
                $amount = Arrays::get($bookings, [$dateTime->format("w"), $dateTime->format("H"), $dateTime->format("i")]);
                $amount += $values->amount;
            } catch (InvalidArgumentException $exception) {
                $amount = $values->amount;
            }

            if ($amount > $this->capacity) {
                $form->addError("Na tento termín možné rezervovat uvedený počet míst");
            }
        };

        $control->onFormSucceeded[] = function (BookingForm $control, ArrayHash $values) {
            $dateTime = DateTime::from($this->bookingFormDateTime);

            $values->year = $dateTime->format("Y");
            $values->week = $dateTime->format("W");
            $values->day_of_week = $dateTime->format("N");
            $values->hour = $dateTime->format("H");
            $values->minute = $dateTime->format("i");
            $values->room_name = $this->getRoomName();
            $values->room_id = $this->id;

            $insertedBooking = $this->databaseLoader->getBookingTable()->insert($values);
            $insertedBooking->update(["hash" => hash("sha512", $insertedBooking->id)]);

            $this->bookingFormDateTime = null;

            $this->customerMail->setParams($insertedBooking);
            $this->customerMail->setComponentName($this->componentName);
            $this->customerMail->send();

            $this->officeMail->setParams($insertedBooking);
            $this->officeMail->setComponentName($this->componentName);
            $this->officeMail->send();

            $this->redirect("this");
        };

        return $control;
    }

    /**
     * @param \DatePeriod $period
     * @throws \Exception
     * @return void
     */
    public function addDisabledPeriod(\DatePeriod $period)
    {
        if (!$period instanceof \DatePeriod) {
            $period = new \DatePeriod($this->startDateTime, new \DateInterval("PT0M"), $this->startDateTime);
        } else {
            if (!$period->getEndDate() instanceof DateTime) {
                $endDate = $period->getStartDate()->add($period->getDateInterval());

                $period = new \DatePeriod($period->getStartDate(), $period->getDateInterval(), $endDate);
            }
        }

        $this->disabledPeriods[] = $period;
    }

    /**
     * @return array
     */
    private function getDisabledPeriods()
    {
        return $this->disabledPeriods;
    }

    /**
     * @param Message $message
     * @return Mail
     */
    public function setupCustomerMail(Message $message)
    {
        $this->customerMail->setMessage($message);

        return $this->customerMail;
    }

    /**
     * @param Message $message
     * @return Mail
     */
    public function setupOfficeMail(Message $message)
    {
        $this->officeMail->setMessage($message);

        return $this->officeMail;
    }

    /**
     * @param string $hash
     * @param bool $value
     * @throws \Nette\Application\AbortException
     * @return void
     */
    public function handleConfirmCancel($hash, $value)
    {
        if ($value) {
            $this->databaseLoader->getBookingTable()->where("hash", $hash)->delete();

            $this->redirect("this", ["isCanceled" => true]);
        } else {
            $this->redirect("this");
        }
    }

    /**
     * @param string $template
     * @return void
     */
    public function setTemplate($template)
    {
        $this->templateFile = $template;
    }

    /**
     * @throws \Nette\Utils\AssertionException
     * @return void
     */
    public function render()
    {
        Validators::assert($this->name, "string", "Room name");

        if (!$this->interval) {
            $this->interval = new \DateInterval("PT30M");
        }

        $dateTimePeriod = new \DatePeriod($this->startDateTime, $this->interval, $this->endDateTime);
        $this->bookings = $this->getBookings();

        $this->template->defaultTemplate = __DIR__ . "/templates/room.latte";
        $this->template->setFile($this->templateFile ?: $this->template->defaultTemplate);
        $this->template->name = $this->name;
        $this->template->dateTimePeriod = $dateTimePeriod;
        $this->template->capacity = $this->capacity;
        $this->template->days = $this->days;
        $this->template->year = $this->year;
        $this->template->week = $this->week;
        $this->template->isBookingFormVisible = $this->isBookingFormVisible;
        $this->template->disabledPeriods = $this->getDisabledPeriods();
        $this->template->actualDateTime = DateTime::from("+1 hour");
        $this->template->weekPeriod = new \DatePeriod(DateTime::from("now")->setISODate($this->year, $this->week, Day::MONDAY), new \DateInterval("P1W"), DateTime::from("now")->setISODate($this->year, $this->week, Day::SUNDAY));
        $this->template->weeksPeriod = new \DatePeriod(DateTime::from("now")->setISODate(date("Y"), date("W"), Day::MONDAY)->modify("-5 weeks"), new \DateInterval("P1W"), 20);
        $this->template->prevWeekDateTime = $this->template->weekPeriod->getStartDate()->modify("-1 week");
        $this->template->nextWeekDateTime = $this->template->weekPeriod->getStartDate()->modify("+1 week");
        $this->template->booking = $this->databaseLoader->getBookingByHash($this->getParameter("hash"));
        $this->template->isCanceled = $this->getParameter("isCanceled");
        $this->template->render();
    }
}

interface IRoomControlFactory
{
    /** @return RoomControl */
    public function create($name, $componentName);
}
<?php

namespace LZaplata\Booking;


use Nette\InvalidArgumentException;
use Nette\Object;
use Nette\SmartObject;
use Nette\Utils\DateTime;
use Nette\Utils\Validators;

class Day extends Object
{
    /** @var int */
    private $dayOfWeek;

    /** @var DateTime */
    private $startDateTime;

    /** @var DateTime */
    private $endDateTime;

    /** @var \DatePeriod */
    private $disabledPeriod;

    /** @var int */
    const MONDAY = 1,
        TUESDAY = 2,
        WEDNESDAY = 3,
        THURSDAY = 4,
        FRIDAY = 5,
        SATURDAY = 6,
        SUNDAY = 7;

    /**
     * @param $dayOfWeek
     * @throws \Nette\Utils\AssertionException
     * @return void
     */
    public function setDayOfWeek($dayOfWeek)
    {
        Validators::assert($dayOfWeek, "int", "Argument 1");

        $this->dayOfWeek = $dayOfWeek;
    }

    /**
     * @return int
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * @param DateTime $time
     * @return void
     */
    public function setStartDateTime(DateTime $time)
    {
        $this->startDateTime = $time;
    }

    /**
     * @return DateTime
     */
    public function getStartDateTime()
    {
        return $this->startDateTime;
    }

    /**
     * @param DateTime $time
     * @return void
     */
    public function setEndDateTime(DateTime $time)
    {
        $this->endDateTime = $time;
    }

    /**
     * @return DateTime
     */
    public function getEndDateTime()
    {
        return $this->endDateTime;
    }

    /**
     * @param \DatePeriod $period
     * @return void
     */
    public function setDisabledPeriod(\DatePeriod $period)
    {
        $this->disabledPeriod = $period;
    }

    /**
     * @return \DatePeriod
     * @throws \Exception
     */
    public function getDisabledPeriod()
    {
        if (!$this->disabledPeriod instanceof \DatePeriod) {
            return new \DatePeriod($this->startDateTime, new \DateInterval("PT0M"), $this->startDateTime);
        } else {
            if (!$this->disabledPeriod->getEndDate() instanceof DateTime) {
                $endDate = $this->disabledPeriod->getStartDate()->add($this->disabledPeriod->getDateInterval());

                return new \DatePeriod($this->disabledPeriod->getStartDate(), $this->disabledPeriod->getDateInterval(), $endDate);
            } return $this->disabledPeriod;
        }
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        if ($this->dayOfWeek !== null && $this->startDateTime && $this->endDateTime) {
            return true;
        } else return false;
    }
}
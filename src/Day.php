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

    /** @var array */
    private $disabledPeriods = [];

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
    public function getDisabledPeriods()
    {
        return $this->disabledPeriods;
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
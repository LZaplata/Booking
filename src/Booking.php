<?php

namespace LZaplata\Booking;


use http\Exception\InvalidArgumentException;
use LZaplata\Booking\Components\ICancelControlFactory;
use LZaplata\Booking\Components\IRoomControl;
use LZaplata\Booking\Components\IRoomControlFactory;
use LZaplata\Booking\Components\RoomControl;
use LZaplata\Booking\Loader\NetteDbLoader;
use LZaplata\Booking\Loaders\IDatabaseLoader;
use Nette\Application\UI\Form;
use Nette\Mail\IMailer;
use Nette\Object;
use Nette\SmartObject;

class Booking extends Object
{
    /** @var IRoomControlFactory */
    private $roomControlFactory;

    /**
     * Booking constructor.
     * @param IRoomControlFactory $roomControlFactory
     */
    public function __construct(IRoomControlFactory $roomControlFactory)
    {
        $this->roomControlFactory = $roomControlFactory;
    }

    /**
     * @param string $name
     * @return RoomControl
     */
    public function createRoom($name = null, $componentName)
    {
        return $this->roomControlFactory->create($name, $componentName);
    }
}
# ČSOB
This is simple Nette Framework booking component.

## Installation
The easiest way to install library is via Composer.

````sh
$ composer require lzaplata/booking: dev-master
````
or edit `composer.json` in your project

````json
"require": {
        "lzaplata/booking": "dev-master"
}
````

You have to register the library as extension in `config.neon` file.

````neon
extensions:
        booking: LZaplata\Booking\DI\BookingExtension
````

Autowire library to presenter and autowire template factory

````php
use LZaplata\Booking\Booking;
use Nette\Application\UI\ITemplateFactory;

/** @var Booking @inject */
public $booking;

/** @var ITemplateFactory @inject */
public $templateFactory;
````

## Usage
Create booking room as a component.

````php
/**
* @param string $name
*/
public function createComponentParkBookingRoom($name)
{
    // create days objects
    
    $monday = new Day(); // creates single day object using LZaplata\Booking\Day
    $monday->setDayOfWeek(Day::MONDAY); // sets day of week 
    $monday->setStartDateTime(new \DateTime()); // sets day start time (you can also use Nette\Utils\DateTime)
    $monday->setEndDateTime(new DateTime()); // sets day end time (you can also use Nette\Utils\DateTime)
    $monday->addDisabledPeriod(new \DatePeriod()); // you can disable period for lunch
    
    // for other days you can create its own objects or you can clone day object
    
    $tuesday = clone $monday;
    $tuesday->setDayOfWeek(Day::TUESDAY); // you must set corresponding day of week
    
    // create booking room
    
    $room = $this->booking->createRoom(string $roomName, string $name); // first parameter is your room name, second is component name
    $room->setDays([$monday, $tuesday, $wednesday, $thursday, $friday, $saturday, $sunday]); // sets days as array of objects
    $room->setCapacity(int $capacity); // sets capacity of each period
    $room->setInterval(new \DateInterval()); // sets booking interval
    $room->setGdprLink(string $link); // sets link to GDPR informations page
    $room->setCapacityExceededMessage(string $message); // sets booking form error message if capacity is exceeded
    $room->addDisabledPeriod(new \DatePeriod()); // disable year period
    $room->setupBookingFormStreetInput(bool $visible, bool $required); // configure form street input
    $room->setupBookingFormZipInput(bool $visible, bool $required); // configure form ZIP input
    $room->setWeeksOptions(int $count, int $history); // sets week selection options
    
    // setup confirmation emails for customer
    
    $customerMessageTemplate = $this->templateFactory->createTemplate();
    $customerMessageTemplate->setFile(string $file);
    
    $customerMessage = new Message(); // create message using Nette\Mail\Message
    $customerMessage->setFrom(string $email);
    $customerMessage->setSubject(string $subject);
    
    $customerMail = $room->setupCustomerMail($customerMessage);
    $customerMail->setTemplate($customerMessageTemplate);
    
    // setup confirmation emails for office
        
    $officeMessageTemplate = $this->templateFactory->createTemplate();
    $officeMessageTemplate->setFile(string $file);
    
    $officeMessage = new Message(); // create message using Nette\Mail\Message
    $officeMessage->addTo(string $email);
    $officeMessage->setSubject(string $subject);
    
    $officeMail = $room->setupCustomerMail($officeMessage);
    $officeMail->setTemplate($officeMessageTemplate);
    
    // setup cancel booking email
    
    $bookingCancelMessageTemplate = $this->templateFactory->createTemplate();
    $bookingCancelMessageTemplate->setFile(string $file);

    $bookingCancelMessage = new Message();
    $bookingCancelMessage->addTo(string $email);
    $bookingCancelMessage->setSubject(string $subject);
    
    $bookingCancelMail = $room->setupBookingCancelMail($bookingCancelMessage);
    $bookingCancelMail->setTemplate($bookingCancelMessageTemplate);
    
    // end finally return whole component
    
    return $room;
}
````
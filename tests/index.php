<?php

require __DIR__ . "/bootstrap.php";
require __DIR__ . "/../src/Booking.php";

use LZaplata\Booking\Booking;
use Tracy\Debugger;

$room = new Booking();

//Debugger::dump($room->getDatabaseLoader());

return $room;
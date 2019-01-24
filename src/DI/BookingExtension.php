<?php

namespace LZaplata\Booking\DI;


use Nette\DI\CompilerExtension;

class BookingExtension extends CompilerExtension
{
    private $defaults = [
        "database" => [
            "loader" => "nettedb",
            "prefix" => "booking"
        ]
    ];

    private $loaders = [
        "nettedb" => "LZaplata\Booking\Loaders\NetteDbLoader"
    ];

    public function loadConfiguration()
    {
        $this->validateConfig($this->defaults);

        $builder = $this->getContainerBuilder();
        $builder->addDefinition($this->prefix("database"))
            ->setFactory($this->loaders[$this->config["database"]["loader"]])
            ->addSetup("setupDatabase", [$this->config["database"]]);

        $builder->addDefinition($this->prefix("booking"))
            ->setFactory("LZaplata\Booking\Booking");

        $builder->addDefinition($this->prefix("room"))
            ->setFactory("LZaplata\Booking\Components\RoomControl")
            ->setImplement("LZaplata\Booking\Components\IRoomControlFactory");

        $builder->addDefinition($this->prefix("bookingForm"))
            ->setFactory("LZaplata\Booking\Forms\BookingForm")
            ->setImplement("LZaplata\Booking\Forms\IBookingFormFactory");

        $builder->addDefinition($this->prefix("mail"))
            ->setFactory("LZaplata\Booking\Mail");

        $builder->addDefinition($this->prefix("cancel"))
            ->setFactory("LZaplata\Booking\Components\CancelControl")
            ->setImplement("LZaplata\Booking\Components\ICancelControlFactory");
    }
}
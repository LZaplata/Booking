<?php

namespace LZaplata\Booking\Loaders;


interface IDatabaseLoader
{
    public function setupDatabase(array $config);

    public function getBookingTable();

    public function getBookingByHash($hash);
}
<?php

namespace LZaplata\Booking\Loaders;


use Nette\Database\Context;

class NetteDbLoader implements IDatabaseLoader
{
    /** @var Context */
    private $database;

    /**
     * NetteDbLoader constructor.
     * @param Context $context
     * @return void
     */
    public function __construct(Context $context)
    {
        $this->database = $context;
    }

    /**
     * @param array $config
     * @return void
     */
    public function setupDatabase(array $config)
    {
        $tables = $this->database->getStructure()->getTables();

        if (array_search($config["prefix"], array_column($tables, "name")) === false) {
            $this->database->query("CREATE TABLE " . $config["prefix"] . " (
                id INT(11) NOT NULL AUTO_INCREMENT,
                year INT(4) NOT NULL,
                week INT(2) NOT NULL,
                day_of_week INT(1) NOT NULL,
                hour CHAR(2) NOT NULL,
                minute CHAR(2) NOT NULL,       
                room_name VARCHAR(255) NOT NULL,
                room_id VARCHAR(255) NOT NULL,       
                amount int(11) NOT NULL DEFAULt 1,
                name VARCHAR(255) NOT NULL,
                surname VARCHAR(255) NOT NULL,
                street VARCHAR(255) NOT NULL,
                street_no VARCHAR(255) NOT NULL,
                city VARCHAR(255) NOT NULL,
                zip VARCHAR(255) NOT NULL,
                mail VARCHAR(255) NOT NULL,
                text text COLLATE utf8_czech_ci NOT NULL,
                hash text COLLATE utf8_czech_ci NOT NULL, 
                PRIMARY KEY (id),
                KEY year (year),
                KEY week (week),
                KEY room_id (room_id)
            )");

            $this->database->getStructure()->rebuild();
        }
    }

    /**
     * @return \Nette\Database\Table\Selection
     */
    public function getBookingTable()
    {
        return $this->database->table("booking");
    }

    /**
     * @param string $hash
     * @return bool|mixed|\Nette\Database\Table\IRow
     */
    public function getBookingByHash($hash)
    {
        return $this->getBookingTable()->where("hash", $hash)->fetch();
    }
}
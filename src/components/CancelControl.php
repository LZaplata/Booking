<?php

namespace LZaplata\Booking\Components;


use LZaplata\Booking\Loaders\IDatabaseLoader;
use Nette\Application\UI\Control;
use Nette\Database\Table\ActiveRow;

class CancelControl extends Control
{
    /** @var IDatabaseLoader */
    private $databaseLoader;

    /** @var ActiveRow */
    private $booking;

    /**
     * CancelControl constructor.
     * @param IDatabaseLoader $databaseLoader
     * @return void
     */
    public function __construct(IDatabaseLoader $databaseLoader)
    {
        $this->databaseLoader = $databaseLoader;

        if (isset($_GET["hash"])) {
            $this->booking = $this->getBooking($_GET["hash"]);
        }
    }

    /**
     * @param string $hash
     * @return ActiveRow|bool
     */
    private function getBooking($hash)
    {
        return $this->databaseLoader->getBookingTable()->where("hash", $hash)->fetch();
    }

    /**
     * @param string $hash
     * @param bool $value
     * @throws \Nette\Application\AbortException
     * @return void
     */
    public function handleConfirm($hash, $value)
    {
        if ($value) {
            $this->getBooking($hash)->delete();

            $this->redirect("this", ["canceled" => true]);
        } else {
            $this->redirect("this");
        }
    }

    /**
     * @return void
     */
    public function render()
    {
        $this->template->setFile(__DIR__ . "/templates/cancel.latte");
        $this->template->booking = $this->booking;
        $this->template->canceled = $this->getParameter("canceled");
        $this->template->render();
    }
}

interface ICancelControlFactory
{
    /** @return CancelControl */
    public function create();
}
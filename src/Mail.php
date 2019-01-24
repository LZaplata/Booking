<?php

namespace LZaplata\Booking;


use http\Exception\BadMessageException;
use Nette\Application\UI\ITemplateFactory;
use Nette\Bridges\ApplicationLatte\Template;
use Nette\Database\Table\ActiveRow;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Mail\SendException;
use Nette\SmartObject;

class Mail
{
    use SmartObject;

    /** @var IMailer */
    private $mailer;

    /** @var Message */
    private $message;

    /** @var Template */
    private $template;

    /** @var ITemplateFactory */
    private $templateFactory;

    /** @var ActiveRow */
    private $params;

    /** @var string */
    private $cancelLink;

    /**
     * Mail constructor.
     * @param IMailer $mailer
     * @param ITemplateFactory $templateFactory
     * @return void
     */
    public function __construct(IMailer $mailer, ITemplateFactory $templateFactory)
    {
        $this->mailer = $mailer;
        $this->templateFactory = $templateFactory;
    }

    /**
     * @param Message $message
     * @return void
     */
    public function setMessage(Message $message)
    {
        $this->message = $message;
    }

    /**
     * @param Template $template
     * @return void
     */
    public function setTemplate(Template $template)
    {
        $this->template = $template;
    }

    /**
     * @return \Nette\Application\UI\ITemplate|Template
     */
    private function getTemplate()
    {
        if (!$this->template instanceof Template) {
            $this->template = $this->templateFactory->createTemplate();
            $this->template->setFile(__DIR__ . "/mail.latte");
        }

        $params = $this->params->toArray();
        $params["cancelLink"] = $this->getCancelLink() . "?hash=" . $params["hash"];

        $this->template->setParameters($params);

        return $this->template;
    }

    /**
     * @param ActiveRow $params
     */
    public function setParams(ActiveRow $params)
    {
        $this->params = $params;
    }

    /**
     * @return string
     */
    public function getCancelLink()
    {
        return $this->cancelLink;
    }

    /**
     * @param string $cancelLink
     */
    public function setCancelLink($cancelLink)
    {
        $this->cancelLink = $cancelLink;
    }

    /**
     * @throws SendException
     * @return void
     */
    public function send()
    {
        if (!$this->message->getHeader("To")) {
            $this->message->addTo($this->params->mail);
        }

        if (!$this->message->getFrom()) {
            $this->message->setFrom($this->params->mail);
        }

        $this->message->setHtmlBody($this->getTemplate());

        $this->mailer->send($this->message);
    }
}
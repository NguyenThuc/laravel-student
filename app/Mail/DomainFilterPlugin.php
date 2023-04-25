<?php

namespace App\Mail;

use Swift_Events_SendEvent;
use Swift_Events_SendListener;

class DomainFilterPlugin implements Swift_Events_SendListener
{

    /**
     * The domains allowed sending mail.
     *
     * @var array
     */
    protected array $domains = [];


    /**
     * Create a new DomainFilterPlugin transport instance.
     *
     * @param array $domains
     */
    public function __construct(array $domains=[])
    {
        $this->domains = $domains;

    }//end __construct()


    public function beforeSendPerformed(Swift_Events_SendEvent $evt)
    {
        if (empty($this->domains)) {
            return;
        }

        $recipients = array_merge(
            (array) $evt->getMessage()->getTo(),
            (array) $evt->getMessage()->getCc(),
            (array) $evt->getMessage()->getBcc()
        );
        foreach ($recipients as $email => $name) {
            $userNameAndDomain = explode('@', $email);
            $domain = array_pop($userNameAndDomain);
            if (in_array($domain, $this->domains, true) === false) {
                $evt->cancelBubble();
            }
        }

    }//end beforeSendPerformed()


    public function sendPerformed(Swift_Events_SendEvent $evt)
    {

    }//end sendPerformed()


}//end class

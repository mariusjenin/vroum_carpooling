<?php

namespace Vroum\Controler;

use Sendpulse\RestApi\ApiClient;
use Sendpulse\RestApi\Storage\FileStorage;

class MailManager {
    private static $_instance;

    private $mailAPI;

    private function __construct() {
        include_once __DIR__ . '/../../config/config.php';

        $this->mailAPI = new \SendGrid(API_MAIL_KEY);
    }

    public static function getInstance() {
        return self::$_instance = (self::$_instance ?? new MailManager);
    }

    public function sendFromNoReply($to, $subject, $message, $messageAlt = '') {
        $email = new \SendGrid\Mail\Mail();
        $email->setFrom("vroum.platform@gmail.com", "Vroum Platform");
        $email->setSubject("$subject");
        $email->addTo("$to", "");
        $email->setReplyTo("no-reply+vroum.platform@gmail.com", "Vroum Platform");
        $email->addContent("text/html", "$message");
        if (!empty($messageAlt))
            $email->addContent("text/plain", "$messageAlt");

        return $this->mailAPI->send($email);
    }
}

?>

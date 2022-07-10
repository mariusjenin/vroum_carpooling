<?php
namespace Vroum\Observer;

use Vroum\Model\Notification;
use Vroum\Model\User;

class NotificationObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param Notification $n
     * @return void
     */
    public function created(Notification $n)
    {
        $destinataire = User::find($n->destinataire);
        if($destinataire->recoit_email){
            $n->sendNotifByMail();
        }
    }
}
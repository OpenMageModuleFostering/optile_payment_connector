<?php
class Optile_Payment_Block_Adminhtml_Notification_View extends Mage_Core_Block_Template {


    private $notification = null;

    public function getNotification(){
        if($this->notification == null){
            $this->notification = Mage::getModel('optile/notification')->load($this->getNotificationId());
        }
        return $this->notification;
    }

}
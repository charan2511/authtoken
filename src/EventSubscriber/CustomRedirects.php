<?php

namespace Drupal\custom_authtoken\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Component\Utility\Html;
use Drupal\user\Entity\User;

class CustomRedirects implements EventSubscriberInterface {

  public function __construct() {
    $this->account = \Drupal::currentUser();
  }

  public function checkForRedirection(GetResponseEvent $event) {
    
    $getauthtoken = $event->getRequest()->query->get('authtoken');
    if (!empty($getauthtoken)) {
        if($this->account->id() > 0){
            $user = User::load($this->account->id());
            $authtoken = $user->field_auth_token->value;
            if($authtoken != $getauthtoken){
                $response = new RedirectResponse('/access-denied', 302);
                $response->send();
                return;
            }
        }
    }

    return;
    
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {

    $events[KernelEvents::REQUEST][] = array('checkForRedirection');
    return $events;
  }

}

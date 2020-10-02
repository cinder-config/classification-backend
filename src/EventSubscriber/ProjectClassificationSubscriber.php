<?php

// api/src/EventSubscriber/BookMailSubscriber.php

namespace App\EventSubscriber;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\ProjectClassification;
use App\Handler\ProjectClassificationNextHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class ProjectClassificationSubscriber implements EventSubscriberInterface
{
    private SessionInterface $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['addSession', EventPriorities::POST_WRITE],
        ];
    }

    public function addSession(ViewEvent $event): void
    {
        return;

        $classification = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$classification instanceof ProjectClassification || Request::METHOD_POST !== $method) {
            return;
        }

        $sessionClassifications = $this->session->get(ProjectClassificationNextHandler::MY_CLASSIFICATIONS);
        $sessionClassifications[] = $classification->getProject()->getId();

        $this->session->set(ProjectClassificationNextHandler::MY_CLASSIFICATIONS, $sessionClassifications);
    }
}

<?php

namespace AppBundle\EventDispatcher;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AuctionSubscriber implements EventSubscriberInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public static function getSubscribedEvents()
    {
        return [
            Events::AUCTION_ADD => "log",
            Events::AUCTION_EXPIRE => "logExpire"
        ];
    }

    public function log(AuctionEvent $event)
    {
        $auction = $event->getAuction();

        $this->logger->info("Aukcja {$auction->getId()} została dodana");
    }

    public function logExpire(AuctionEvent $event)
    {
        $auction = $event->getAuction();

        $this->logger->info("Aukcja {$auction->getId()} wygasła automatycznie");
    }
}
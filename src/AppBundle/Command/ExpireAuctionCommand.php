<?php

namespace AppBundle\Command;

use AppBundle\Entity\Auction;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExpireAuctionCommand extends Command
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName("app:expire_auction")
            ->setDescription("Komenda do wygaszania aukcji");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $auctions = $this->entityManager->getRepository(Auction::class)->findActiveExpired();

        $output->writeln(sprintf("Znaleziono <info>%d</info> aukcji do wygaszenia", count($auctions)));

        foreach ($auctions as $auction) {
            $auction->setStatus(Auction::STATUS_FINISHED);
            $this->entityManager->persist($auction);
        }

        $this->entityManager->flush();

        $output->writeln("Udało się zaktualizować aukcje!");
    }
}
<?php

namespace AppBundle\Command;

use AppBundle\Entity\Auction;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExpireAuctionCommand extends ContainerAwareCommand
{
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
        $entityManager = $this->getContainer()->get("doctrine.orm.entity_manager");
        $auctions = $entityManager->getRepository(Auction::class)->findActiveExpired();

        $output->writeln(sprintf("Znaleziono <info>%d</info> aukcji do wygaszenia", count($auctions)));

        foreach ($auctions as $auction) {
            $auction->setStatus(Auction::STATUS_FINISHED);
            $entityManager->persist($auction);
        }

        $entityManager->flush();

        $output->writeln("Udało się zaktualizować aukcje!");
    }
}
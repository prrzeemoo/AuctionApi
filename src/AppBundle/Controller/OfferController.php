<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Auction;
use AppBundle\Entity\Offer;
use AppBundle\Form\BidType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OfferController extends Controller
{
    /**
     * @Route("/auction/buy/{id}", name="offer_buy", methods={"POST"})
     *
     * @param Auction $auction
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function buyAction(Auction $auction)
    {
        $offer = new Offer();
        $offer
            ->setAuction($auction)
            ->setType(Offer::TYPE_BUY)
            ->setPrice($auction->getPrice());

        $auction->setStatus(Auction::STATUS_FINISHED)
            ->setExpiresAt(new \DateTime());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($auction);
        $entityManager->persist($offer);
        $entityManager->flush();

        $this->addFlash("success", "Kupiłeś przedmiot {$auction->getTitle()} za kwotę {$offer->getPrice()} zł");

        return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
    }

    /**
     * @Route("/auction/bid/{id}", name="offer_bid", methods={"POST"})
     *
     * @param Request $request
     * @param Auction $auction
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function bidAction(Request $request, Auction $auction)
    {
        $offer = new Offer();
        $bidForm = $this->createForm(BidType::class, $offer);

        $bidForm->handleRequest($request);

        $offer
            ->setType(Offer::TYPE_BID)
            ->setAuction($auction);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($offer);
        $entityManager->flush();

        $this->addFlash(
            "success",
            "Złożyłeś ofertę na przedmiot {$auction->getTitle()} za kwotę {$offer->getPrice()} zł"
        );

        return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
    }
}
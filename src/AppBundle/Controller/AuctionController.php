<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Auction;
use AppBundle\Form\AuctionType;
use AppBundle\Form\BidType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


class AuctionController extends Controller
{
    /**
     * @Route("/", name="auction_index")
     *
     * @return Response
     */
    public function indexAction()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $auctions = $entityManager->getRepository(Auction::class)->findBy(["status" => Auction::STATUS_ACTIVE]);

        return $this->render("Auction/index.html.twig", ["auctions" => $auctions]);
    }

    /**
     * @Route("/auction/details/{id}", name="auction_details")
     *
     * @param Auction $auction
     * @return Response
     */
    public function detailsAction(Auction $auction)
    {
        //        $entityManager = $this->getDoctrine()->getManager();
        //        $auction = $entityManager->getRepository(Auction::class)->findOneBy(["id" => $id]);

        if ($auction->getStatus() === Auction::STATUS_FINISHED) {
            return $this->render("Auction/finished.html.twig", ["auction" => $auction]);
        }

        $buyForm = $this->createFormBuilder()
            ->setAction($this->generateUrl("offer_buy", ["id" => $auction->getId()]))
            ->add("submit", SubmitType::class, ["label" => "Kup"])
            ->getForm();

        $bidForm = $this->createForm(
            BidType::class,
            null,
            ["action" => $this->generateUrl("offer_bid", ["id" => $auction->getId()])]
        );

        return $this->render(
            "Auction/details.html.twig",
            [
                "auction" => $auction,
                "buyForm" => $buyForm->createView(),
                "bidForm" => $bidForm->createView(),
            ]
        );
    }

    /**
     * @Route("/auction/add", name="auction_add")
     *
     * @param Request $request
     * @return Response
     */
    public function addAction(Request $request)
    {
        /* KAŻDA Z PONIŻSZYCH TRZECH LINII KODU ODPOWIADA ZA SPRAWDZENIE, CZY UŻYTKOWNIK JEST ZALOGOWANY
        if ($this->getUser() instanceof User)
        if ($this->isGranted("ROLE_USER"))
        */
        $this->denyAccessUnlessGranted("ROLE_USER");

        $auction = new Auction();

        $form = $this->createForm(AuctionType::class, $auction);

        if ($request->isMethod("post")) {
            $form->handleRequest($request);

            if ($auction->getStartingPrice() >= $auction->getPrice()) {
                $form->get("startingPrice")->addError(new FormError("Cena wywoławcza nie może być wyższa od ceny kup teraz"));
            }

            if ($form->isValid()) {
                $auction
                    ->setStatus(Auction::STATUS_ACTIVE)
                    ->setOwner($this->getUser());

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($auction);
                $entityManager->flush();

                $this->addFlash("success", "Aukcja {$auction->getTitle()} została dodana");

                return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
            }

            $this->addFlash("error", "Nie udało się dodać aukcji!");
        }

        return $this->render("Auction/add.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/auction/edit/{id}", name="auction_edit")
     *
     * @param Request $request
     * @param Auction $auction
     * @return Response
     */
    public function editAction(Request $request, Auction $auction)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if ($this->getUser() !== $auction->getOwner()) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(AuctionType::class, $auction);

        if ($request->isMethod("post")) {
            $form->handleRequest($request);

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($auction);
            $entityManager->flush();

            $this->addFlash("success", "Aukcja {$auction->getTitle()} została zaktualizowana");

            return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
        }

        return $this->render("Auction/edit.html.twig", ["form" => $form->createView()]);
    }

    /**
     * @Route("/auction/delete/{id}", name="auction_delete", methods={"DELETE"})
     *
     * @param Auction $auction
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Auction $auction)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if ($this->getUser() !== $auction->getOwner()) {
            throw new AccessDeniedException();
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($auction);
        $entityManager->flush();

        $this->addFlash("success", "Aukcja {$auction->getTitle()} została usunięta");

        return $this->redirectToRoute("auction_index");
    }

    /**
     * @Route("/auction/finish/{id}", name="auction_finish", methods={"POST"})
     *
     * @param Auction $auction
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function finishAction(Auction $auction)
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        if ($this->getUser() !== $auction->getOwner()) {
            throw new AccessDeniedException();
        }

        $auction
            ->setExpiresAt(new \DateTime())
            ->setStatus(Auction::STATUS_FINISHED);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($auction);
        $entityManager->flush();

        $this->addFlash("success", "Aukcja {$auction->getTitle()} została zakończona");

        return $this->redirectToRoute("auction_details", ["id" => $auction->getId()]);
    }
}
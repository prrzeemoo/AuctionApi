<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 14.01.2018
 * Time: 19:51
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Auction;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MyAuctionController extends Controller
{
    /**
     * @Route("/my", name="my_auction_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted("ROLE_USER");

        $entityManager = $this->getDoctrine()->getManager();
        $auctions = $entityManager
            ->getRepository(Auction::class)
            ->findBy(["owner" => $this->getUser()]);

        return $this->render("MyAuction/index.html.twig", ["auctions" => $auctions]);
    }

    /**
     * @Route("/my/auction/details/{id}", name="my_auction_details")
     *
     * @param Auction $auction
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailsAction(Auction $auction)
    {
        if ($auction->getStatus() === Auction::STATUS_FINISHED) {
            return $this->render("Auction/finished.html.twig", ["auction" => $auction]);
        }

        $deleteForm = $this->createFormBuilder()
            ->setAction($this->generateUrl("auction_delete", ["id" => $auction->getId()]))
            ->setMethod(Request::METHOD_DELETE)
            ->add("submit", SubmitType::class, ["label" => "Usuń"])
            ->getForm();

        $finishForm = $this->createFormBuilder()
            ->setAction($this->generateUrl("auction_finish", ["id" => $auction->getId()]))
            /*PONIŻSZĄ METODĘ MOŻNA DODAĆ LUB POMINĄĆ, GDYŻ DOMYŚLNIE USTAWIANA JEST W ADNOTACJI ROUTY nazwanej auction_finish
            ->setMethod(Request::METHOD_POST)*/
            ->add("submit", SubmitType::class, ["label" => "Zakończ"])
            ->getForm();

        return $this->render(
            "MyAuction/details.html.twig",
            [
                "auction" => $auction,
                "deleteForm" => $deleteForm->createView(),
                "finishForm" => $finishForm->createView(),
            ]
        );
    }
}
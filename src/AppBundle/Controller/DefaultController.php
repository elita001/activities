<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Order;
use AppBundle\Repository\OrderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="main")
     */
    public function indexAction()
    {
        //TODO move to config
        $daysCount = 3;
        $ordersList = array();
        /** @var OrderRepository $orderRep */
        $orderRep = $this->getDoctrine()->getRepository(Order::class);
        for ($i = 0; $i < 3; $i++) {
            $day = date('Y-m-d', strtotime("+{$i} day"));
            $ordersList[$day] = $orderRep->findByDay($day);
        }

        return $this->render('pages/main.html.twig', array('ordersList' => $ordersList));
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction()
    {
        return $this->render('pages/about.html.twig');
    }
}

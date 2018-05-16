<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Order;
use AppBundle\Form\SupportType;
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

    /**
     * @Route("/support", name="support")
     */
    public function supportAction(Request $request)
    {
        $form = $this->createForm(SupportType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $email = empty($formData['email']) ? '' : $formData['email'];
            $text = empty($formData['content']) ? '' : $formData['content'];
            if ($text && $email) {
                $mailer = $this->get('mailer');
                $message = \Swift_Message::newInstance()
                    ->setSubject('Support mail: ' . $email)
                    ->setFrom('san4o582@mail.ru')
                    ->setTo('san4o528@gmail.com')
                    ->setBody($text);
                $mailer->send($message);
            }

            return $this->redirectToRoute('support');
        }
        return $this->render(
            'pages/support.html.twig',
            array('form' => $form->createView())
        );
    }
}

<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Order;
use AppBundle\Entity\UserOrder;
use AppBundle\Form\OrderType;
use AppBundle\Repository\OrderRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends Controller
{
    /**
     * @Route("/order", name="order_save")
     */
    public function orderAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $user = $this->getUser();
        $order = null;
        if ($id = $request->get('id')) {
            $orderRep = $this->getDoctrine()->getRepository(Order::class);
            $order = $orderRep->findOneBy(array('id' => $id, 'creator' => $user));
            $order->setLogo(
                new File($this->getParameter('order_logo_directory').'/'.$order->getLogo())
            );
        }
        // 1) build the form
        if (!$order) {
            $order = new Order();
        }
        $form = $this->createForm(OrderType::class, $order);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $file stores the uploaded image
            /** @var UploadedFile $file */
            $file = $order->getLogo();
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
            // moves the file to the directory where brochures are stored
            $file->move(
                $this->getParameter('order_logo_directory'),
                $fileName
            );
            $order->setLogo($fileName);

            $order->setCreator($user);
            // 4) save the Order!
            $em = $this->getDoctrine()->getManager();
            $em->persist($order);
            $em->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('orders_list');
        }

        return $this->render(
            'order/order.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * @Route("/orders", name="orders_list")
     */
    public function ordersAction(Request $request = null)
    {
        $acceptable = false;
        $header = 'Список мероприятий';
        $type = $request->query->get('type');
        $rawDate = $request->query->get('date');
        if ($rawDate) {
            $header .= sprintf(' (%s)', $rawDate);
        }
        $orders = array();
        switch ($type) {
            case 'participate':
                if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                    throw $this->createAccessDeniedException();
                }
                $header = 'Мероприятия, в которых я участвую';
                $userOrderRep = $this->getDoctrine()->getRepository(UserOrder::class);
                $userOrders = $userOrderRep->findBy(
                    array('user' => $this->getUser())
                );
                /** @var UserOrder $order */
                foreach ($userOrders as $userOrder) {
                    $order = $userOrder->getOrder();
                    if (!$rawDate) {
                        $orders[] = $order;
                    }
                    if ($rawDate) {
                        if ($order->getDateStart()
                            && $order->getDateEnd()
                            && $order->getDateStart()->format("Y-m-d") <= $rawDate
                            && $order->getDateEnd()->format("Y-m-d") >= $rawDate
                        ) {
                            $orders[] = $order;
                        }

                    }
                }
                break;
            case 'created':
                if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
                    throw $this->createAccessDeniedException();
                }
                $header = 'Созданные мной мероприятия';
                /** @var Order[] $orders */
                $orders = $this->getUser()->getOrders();
                if ($rawDate) {
                    foreach ($orders as $key => $order) {
                        if (!$order->getDateStart()
                            || !$order->getDateEnd()
                            || $order->getDateStart()->format("Y-m-d") > $rawDate
                            || $order->getDateEnd()->format("Y-m-d") < $rawDate
                        ) {
                            unset($orders[$key]);
                        }
                    }
                }
                break;
            default:
                $acceptable = true;
                /** @var OrderRepository $orderRep */
                $orderRep = $this->getDoctrine()->getRepository(Order::class);
                if (!$rawDate) {
                    $orders = $orderRep->findAll();
                }
                if ($rawDate) {
                    $orders = $orderRep->findByDay($rawDate);
                }
        }
        return $this->render('order/orders_list.html.twig', array(
            'orders' => $orders,
            'acceptable' => $acceptable,
            'header' => $header,
            'type' => $type,
        ));
    }

    /**
     * @Route("/order/participate/{id}", name="order_participate")
     */
    public function orderParticipateAction($id)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }
        $orderRep = $this->getDoctrine()->getRepository(Order::class);
        /** @var Order $order */
        $order = $orderRep->find($id);
        $user = $this->getUser();
        $userOrder = new UserOrder();
        $userOrder->setOrder($order);
        $userOrder->setUser($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($userOrder);
        $em->flush();
        return $this->redirectToRoute('orders_list', array('type' => 'participate'));
    }

    /**
     * @Route("/order/view/{id}", name="order_view")
     */
    public function orderViewAction($id)
    {
        $orderRep = $this->getDoctrine()->getRepository(Order::class);
        /** @var Order $order */
        $order = $orderRep->find($id);
        return $this->render('order/order_view.html.twig', array(
            'order' => $order,
        ));
    }

    /**
     * @return string
     */
    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}
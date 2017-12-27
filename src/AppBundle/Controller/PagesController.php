<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class PagesController extends Controller
{
    /**
     * @Route("/pages/about/{text}", name="pages_about")
     */
    public function aboutAction($text = 'Текст страницы о нас!')
    {
        return $this->render('pages/about.html.twig', array(
            'text' => $text,
        ));
    }

    /**
     * @Route("/pages/main/{text}", name="pages_main")
     */
    public function mainAction($text = 'Текст главной страницы!')
    {
        return $this->render('pages/main.html.twig', array(
            'text' => $text,
        ));
    }
}

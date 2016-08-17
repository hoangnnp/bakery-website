<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Form\ContactType;
use AppBundle\Entity\Contact;
use AppBundle\Entity\Product;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        $res_array = array();
        $repository=$this->getDoctrine()->getRepository('AppBundle:Product');
        $products=$repository->findAll();
        $res_array['products']=$products;
        return $this->render('default/index.html.twig',$res_array);
    }

    /**
     * @Route("/lang/{name}", name="changeLang")
     */
    public function changeLangAction($name, Request $request)
    {
        $se = $request->getSession();
        $se->set('locate',$name);
        return $this->redirectToRoute('homepage');

    }
    /**
     * @Route("/contact", name="contactpage")
     */
    public function ContactAction(Request $request)
    {
        $form=$this->createForm(ContactType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()) {
            $create = $form->getData();
            $contact = new Contact();
            $contact->setName($create['name']);
            $contact->setEmail($create['email']);
            $contact->setMobile($create['mobile']);
            $contact->setSubject($create['subject']);
            $em=$this->getDoctrine()->getManager();
            $em->persist($contact);
            $em->flush();
            return $this->redirectToRoute("homepage");
        }
        $res_array=array('form'=>$form->createView());
        return $this->render('default/contact.html.twig',$res_array);
    }
    /**
     * @Route("/about", name="aboutpage")
     */
    public function aboutAction(Request $request)
    {
        return $this->render('default/about.html.twig');
    }
}

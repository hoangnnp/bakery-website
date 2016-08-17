<?php

namespace AppBundle\Controller;

use AppBundle\Form\EditType;
use AppBundle\Entity\User;
use AppBundle\Entity\Product;
use AppBundle\Form\LoginType;
use AppBundle\Form\CreateType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Gedmo\Translatable\TranslationListener;

class AdminController extends Controller
{
    /**
     * @Route("/login", name="loginpage")
     */
    public function loginAction(Request $request)
    {
        $form=$this->createForm(LoginType::class);
        $form->handleRequest($request);
        if($form-> isSubmitted()){
            $login=$form->getData();
            $repository = $this->getDoctrine()->getRepository('AppBundle:Admin');
            $admin=$repository->findOneBy(array('username'=>$login['username'],'password'=>$login['password']));
            if($admin!=null)
            {
                $session=$request->getSession();
                $session->set('username',$admin->getUsername());
                $session->set('fullname',$admin->getFullname());
                $session->set('image',$admin->getImage());
                $session->set('login',true);
                $session->set('role',$admin->getRole());
                $session->set('id',$admin->getID());
                if($admin->getRole()=="Admin")
                    return $this->redirectToRoute('adminpage');
            }
        }
        return $this->render('admin/login.html.twig', [
            'form1'=>$form->createView(),
        ]);
    }
    /**
     * @Route("/logout", name="logoutpage")
     */
    public function logoutAction(Request $request)
    {
        $session=$request->getSession();
        $session->clear();
        return $this->redirectToRoute('homepage');
    }
    /**
     * @Route("/admin/contact", name="admincontactpage")
     */
    public function admincontactAction(Request $request)
    {
        $res_array = array();
        $se = $request->getSession();
        if(!$se->has('login'))
        {
            return $this->render('error/errorlogin.html.twig');
        }
        $res_array['image']=$se->get('image');
        $res_array['fullname']=$se->get('fullname');
        $repository=$this->getDoctrine()->getRepository('AppBundle:Contact');
        $contacts=$repository->findAll();
        $res_array['contacts']=$contacts;
        return $this->render('admin/contactadmin.html.twig',$res_array);
    }
    /**
     * @Route("/admin/contact/delete/{id}",name="deletecontactpage")
     */
    public function deletecontactAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $contact = $em->getPartialReference('AppBundle\Entity\Contact', array('id' => $id));
        $em->remove($contact);
        $em->flush();
        return $this->redirectToRoute("admincontactpage");
    }
    /**
     * @Route("/admin", name="adminpage")
     */
    public function adminAction(Request $request)
    {
        $res_array = array();
        $se = $request->getSession();
        if(!$se->has('login'))
        {
            return $this->render('error/errorlogin.html.twig');
        }
        $res_array['image']=$se->get('image');
        $res_array['fullname']=$se->get('fullname');
        $repository=$this->getDoctrine()->getRepository('AppBundle:Product');
        $products=$repository->findAll();
        $res_array['products']=$products;
        return $this->render('admin/admin.html.twig',$res_array);
    }
    /**
     * @Route("/admin/delete/{id}",name="deletepage")
     */
    public function deleteAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $product = $em->getPartialReference('AppBundle\Entity\Product', array('id' => $id));
        $em->remove($product);
        $em->flush();
        return $this->redirectToRoute("adminpage");
    }
    /**
     * @Route("/admin/edit/{id}",name="editpage")
     */
    public function editAction($id, Request $request)
    {
        $se = $request->getSession();
        if(!$se->has('login'))
        {
            return $this->render('error/errorlogin.html.twig');
        }
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
        $product=$repository->findOneBy(
            array('id'=>$id)
        );
        $translationRespo = $em->getRepository('Gedmo\Translatable\Entity\Translation');
        $translations = $translationRespo->findTranslations($product);
        $form=$this->createForm(CreateType::class);
        $form->get('id')->setData($product->getId());
        $form->get('price')->setData($product->getPrice());
        $form->get('image')->setData($product->getImage());
        #$form->get('description')->setData($product->getDescription());
        if(isset($translations['vi']['description']))
            $form->get('description_vi')->setData($translations['vi']['description']);
        if(isset($translations['en']['description']))
            $form->get('description_en')->setData($translations['en']['description']);
        if(isset($translations['fr']['description']))
            $form->get('description_fr')->setData($translations['fr']['description']);
        if(isset($translations['vi']['name']))
            $form->get('name_vi')->setData($translations['vi']['name']);
        if(isset($translations['en']['name']))
            $form->get('name_en')->setData($translations['en']['name']);
        if(isset($translations['fr']['name']))
            $form->get('name_fr')->setData($translations['fr']['name']);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $create = $form->getData();
            $product->setId($create['id']);
            $product->setPrice($create['price']);
            $product->setImage($create['image']);
            #$product->setDescription($create['description']);
            $translationRespo->translate($product, 'description', 'vi', $create['description_vi'])
                ->translate($product, 'description', 'fr', $create['description_fr'])
                ->translate($product, 'description', 'en', $create['description_en']);
            $translationRespo->translate($product, 'name', 'vi', $create['name_vi'])
                ->translate($product, 'name', 'fr', $create['name_fr'])
                ->translate($product, 'name', 'en', $create['name_en']);
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute("adminpage");
        }
        $res_array=array('form'=>$form->createView());
        $res_array['image']=$se->get('image');
        $res_array['fullname']=$se->get('fullname');
        $res_array['title']='Edit Product';
        return $this->render('admin/create.html.twig',$res_array);
    }
    /**
     * @Route("/admin/create",name="createpage")
     */
    public function createAction(Request $request)
    {
        $se = $request->getSession();
        if(!$se->has('login'))
        {
            return $this->render('error/errorlogin.html.twig');
        }
        $form=$this->createForm(CreateType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()) {
            $create = $form->getData();
            $product = new Product();
            $product->setPrice($create['price']);
            $product->setImage($create['image']);
            //$product->setDescription($create['description']);
            $em=$this->getDoctrine()->getManager();
            $repository = $em->getRepository('Gedmo\Translatable\Entity\Translation');
            $repository->translate($product,'description','vi',$create['description_vi'])
                ->translate($product,'description','en',$create['description_en'])
                ->translate($product,'description','fr',$create['description_fr']);
            $repository->translate($product, 'name', 'vi', $create['name_vi'])
                ->translate($product, 'name', 'en', $create['name_en'])
                ->translate($product, 'name', 'fr', $create['name_fr']);
            $em->persist($product);
            $em->flush();
            return $this->redirectToRoute("adminpage");
        }
        $res_array=array('form'=>$form->createView());
        $res_array['image']=$se->get('image');
        $res_array['fullname']=$se->get('fullname');
        $res_array['title']='Create Product';
        return $this->render('admin/create.html.twig',$res_array);
    }
}
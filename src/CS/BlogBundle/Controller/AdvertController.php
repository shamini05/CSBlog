<?php



namespace CS\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use CS\BlogBundle\Entity\Advert;
use CS\BlogBundle\Entity\Image;

class AdvertController extends Controller
{ // ************************* INDEX ACTION ***************************************************************************

    public function indexAction($page)
    {
        $listAdverts= $this->getAll();
        return $this->render('CSBlogBundle:Advert:index.html.twig', array(
            'listAdverts' =>  $listAdverts
        ));
    }

    private function getAll(){
        $em=$this->getDoctrine()
            ->getManager();
        $repository = $em->getRepository('CSBlogBundle:Advert')
        ;
        $listAdverts= $repository->findAll();
        return $listAdverts;
    }

    private function getAdvert($id){
        $repository = $this->getDoctrine()
            ->getManager()
            ->getRepository('CSBlogBundle:Advert')
        ;
        $advert = $repository->find($id);
    return $advert;
    }
    // ************************* VIEW ACTION ***************************************************************************

    public function viewAction($id)
    {
        $advert=$this->getAdvert($id);
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id ".$id." n'existe pas.");
        }
        return $this->render('CSBlogBundle:Advert:view.html.twig', array(
            'advert' => $advert
        ));
    }

// ************************* ADD ACTION ***************************************************************************


        public function addAction(Request $request)
        {
            // Création de l'entité Advert
            $advert = new Advert();
            $formBuilder = $this->get('form.factory')->createBuilder('form', $advert);
            $formBuilder
                ->add('date','date')
                ->add('title','text')
                ->add('content','textarea')
                ->add('author','text')
                ->add('published','checkbox')
                ->add('save','submit');
            $form = $formBuilder->getForm();
            $formBuilder->add('published', 'checkbox', array('required' => false));
            $form->handleRequest($request);
            // On récupère l'EntityManager
            $em = $this->getDoctrine()->getManager();
            if ($form->isValid()) {
                // Création de l'entité Image
                $image = new Image();
                $image->setUrl('http://sdz-upload.s3.amazonaws.com/prod/upload/job-de-reve.jpg');
                $image->setAlt('Job de rêve');
                // On lie l'image à l'annonce
                $advert->setImage($image);
                $em->persist($image);
                $em->persist($advert);
                $em->flush();
                $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');
                return $this->redirect($this->generateUrl('cs_blog_view', array('id' => $advert->getId())));
            }
            return $this->render('CSBlogBundle:Advert:add.html.twig', array('form' => $form->createView()));



        }

    // ************************* EDIT ACTION ***************************************************************************

    public function editAction($id, Request $request)
    {

        $advert =$this->getAdvert($id);
            $formBuilder=$this->get('form.factory')->createBuilder('form', $advert);
        $formBuilder
            ->add('title','text')
            ->add('content','textarea')
            ->add('published','checkbox')
            ->add('save','submit');
        $form = $formBuilder->getForm();
        $formBuilder->add('published', 'checkbox', array('required' => false));
        $form->handleRequest($request);
        if ($form->isValid()) {
            $em=$this->getDoctrine()
                ->getManager();
            $em->persist($advert);
            $em->flush();
            $request->getSession()->getFlashBag()->add('notice', 'Le post a bien été enregistrée.');
            return $this->redirect($this->generateUrl('cs_blog_view', array('id' => $advert->getId())));
        }
        return $this->render('CSBlogBundle:Advert:edit.html.twig', array(
            'advert' => $advert,'form' => $form->createView()
        ));
    }



//************************************************************************************************/
    public function deleteAction($id)
    {
        $advert=$this->getAdvert($id);
        $em=$this->getDoctrine()
            ->getManager();
        $em->remove($advert);
        $em->flush();
        return $this->render('CSBlogBundle:Advert:delete.html.twig');

    }
    public function menuAction($limit)
    {
        $listAdverts=  $this->getAll();
        return $this->render('CSBlogBundle:Advert:menu.html.twig',
                array('listAdverts' => $listAdverts));
    }
}
<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Form\ProductType;

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ProductController extends AbstractController
{
    /**
     * @Route("/products", name="product_index")
     */
    public function index(ProductRepository $product, PaginatorInterface $paginator, Request $request)
    {
        $products = $product->findAll();

        $productsWithPagination = $paginator->paginate($products, $request->query->getInt('page', 1), 6);
        
        return $this->render('product/index.html.twig', [
            'title' => 'Les produits',
            'products' => $productsWithPagination,
        ]);
    }

    /**
     * @Route("/product/show/{id}", name="product_show")
     */
    public function show(Product $product)
    {
        
        return $this->render('product/show.html.twig', [
            'title' => 'La bière : '. $product->getName(),
            'product' => $product
        ]);
    }

    /**
     * @Route("/product/new", name="product_new")
     * @Route("/product/edit/{id}", name="product_edit")
     * @Security("is_granted('ROLE_USER')", statusCode=404)
     */
    public function form(Request $request, ObjectManager $manager, Product $product = null)
    {
        if (!$this->getUser()) {
            $this->addFlash('error', 'Cette fonction est uniquement reservé aux administrateurs.');
            return $this->redirectToRoute('product_index');
        }
        
        if (!$product) {
            $product = new Product();
            $title = "Créer un nouveau produit";
        }else{
            $title = "Editer le produit : ". $product->getName();
        }

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$product->getId()) {
                $product->setCreatedAt(new \DateTime());
            }

            $manager->persist($product);
            $manager->flush();

            $this->addFlash('success', 'Produit enregistré !');

            return $this->redirectToRoute('product_show', ['id' => $product->getId()]);
        }

        return $this->render('product/form.html.twig', [
            'title' => $title,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/product/delete/{id}", name="product_delete")
     */
    public function delete(Product $product, ObjectManager $manager)
    {
        $manager->remove($product);
        $manager->flush();

        $this->addFlash('success', 'Produit supprimé !');

        return $this->redirectToRoute('product_index');
    }
}

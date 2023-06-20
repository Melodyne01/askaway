<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Section;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;

class DeleteController extends AbstractController
{
    #[Route('/admin/deleteArticle/{id}', name: 'deleteArticle')]
    public function deleteArticle(Article $article, EntityManagerInterface $em)
    {
        if($article->getImage()){
            unlink($this->getParameter('images_directory').'/'. $article->getImage());
        }
        $em->remove($article);
        $em->flush();
        $this->addFlash("danger", "Le produit à bien été supprimé");
        return $this->redirectToRoute("admin");
    }

    #[Route('/admin/deleteArticleImage/{id}', name: 'deleteArticleImage')]
    public function deleteArticleImage(Article $article, EntityManagerInterface  $em, Environment $twig)
    {
        unlink($this->getParameter('images_directory').'/'. $article->getImage());
        $article->setImage("");
        $em->persist($article);
        $em->flush();
 
        $this->addFlash("danger", "L'image à bien été supprimée");
        return $this->redirectToRoute('admin_sections', [
            "id" => $article->getId(),
            "slug" => $twig->getFilter('slugify')->getCallable()($article->getTitle())

        ]);
    }

    #[Route('/admin/deleteSection/{id}', name: 'deleteSection')]
    public function deleteSection(Section $section, EntityManagerInterface  $em, Environment $twig)
    {
        $article = $section->getArticle();
        if($section->getImage()){
            unlink($this->getParameter('images_directory').'/'. $section->getImage());
        }
        $em->remove($section);
        $em->flush();
 
        $this->addFlash("danger", "L'section à bien été supprimée");
        return $this->redirectToRoute('admin_sections', [
            "id" => $article->getId(),
            "slug" => $twig->getFilter('slugify')->getCallable()($article->getTitle())

        ]);
    }

    #[Route('/admin/deleteSectionImage/{id}', name: 'deleteSectionImage')]
    public function deleteSectionImage(Section $section, EntityManagerInterface  $em, Environment $twig)
    {
        $article = $section->getArticle();
        unlink($this->getParameter('images_directory').'/'. $section->getImage());
        $section->setImage("");
        $em->persist($section);
        $em->flush();
 
        $this->addFlash("danger", "L'image à bien été supprimée");
        return $this->redirectToRoute('admin_section', [
            "id" => $section->getId()

        ]);
    }

    #[Route('/admin/deleteCategorie/{id}', name: 'deleteCategorie')]
    public function deleteCategorie(Categorie $categorie, EntityManagerInterface  $em)
    {
        $em->remove($categorie);
        $em->flush();
 
        $this->addFlash("danger", "La categorie à bien été supprimée");
        return $this->redirectToRoute('admin_categories');
    }

}

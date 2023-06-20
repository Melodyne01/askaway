<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use Twig\Environment;
use App\Entity\Article;
use App\Entity\Section;
use App\Entity\Categorie;
use App\Form\AddArticleType;
use App\Form\AddSectionType;
use Intervention\Image\Image;
use App\Form\AddCategorieType;
use App\Form\EditArticleType;
use App\Form\RegistrationType;
use App\Twig\SlugifyExtension;
use App\Service\ChatGptService;
use App\Repository\ArticleRepository;
use App\Repository\SectionRepository;
use App\Repository\VisitorRepository;
use App\Repository\CategorieRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminController extends AbstractController
{
    #[Route('admin/', name: 'admin')]
    public function admin(Request $request, ManagerRegistry $manager, ArticleRepository $articleRepo): Response
    {
        $article = new Article();

        $form = $this->createForm(AddArticleType::class, $article);

        $form->handleRequest($request);
        //Vérification de la conformité des données entrées par l'utilisateur
        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if($image){
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                
            $article->setImage($fichier);
            }
            $article->setOnline(false);
            $article->setCreatedAt(new DateTime('Europe/Paris'));
            $manager->getManager()->persist($article);
            $manager->getManager()->flush();
            return $this->redirectToRoute('admin',[
            'id' => $this->getUser()->getUserIdentifier()
        ]);
        }

        $articles = $articleRepo->findAllByIDDesc();

        return $this->render('admin/admin.html.twig', [
            'user' => $this->getUser(),
            'form' => $form->createView(),
            'articles' => $articles
        ]);
    }
    #[Route('admin/profile/{id}', name: 'admin_profile')]
    public function adminProfile(User $user, Request $request, ManagerRegistry $manager, UserPasswordHasherInterface $passwordHasher): Response
    {
        if ($this->getUser()->getUserIdentifier() != strval($user->getId())) {
            throw $this->createAccessDeniedException();
        }
        //Creation du formulaire sur base d'un formulaire crée au préalable 
        $form = $this->createForm(RegistrationType::class, $user);

        $form->handleRequest($request);
        //Vérification de la conformité des données entrées par l'utilisateur
        if ($form->isSubmitted() && $form->isValid()) {
            //Chiffrement du mot de passe selon l'algorytme Bcrypt
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hashedPassword);
            $manager->getManager()->persist($user);
            //Envoie des données vers la base de données
            $manager->getManager()->flush();

            $this->addFlash("success", "Le compte à bien été créé");
            return $this->redirectToRoute('login');
        }

        return $this->render('admin/adminProfile.html.twig', [

            'form' => $form->createView(),
        ]);
    }
    
    #[Route('admin/article/{id}/{slug}', name: 'admin_sections')]
    public function sections(Article $article, Request $request, ManagerRegistry $manager, SectionRepository $sectionRepo, Environment $twig): Response
    {
        $articleForm = $this->createForm(EditArticleType::class, $article);
        $articleForm->handleRequest($request);
        //Vérification de la conformité des données entrées par l'utilisateur
        if ($articleForm->isSubmitted() && $articleForm->isValid()) {
            $image = $articleForm->get('image')->getData();
            if($image){
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                    $image->move(
                        $this->getParameter('images_directory'),
                        $fichier
                    );
                $article->setImage($fichier);
            }
            $manager->getManager()->persist($article);
            $manager->getManager()->flush();
            return $this->redirectToRoute('admin'); 
        }

        $sections = $sectionRepo->findAllByArticle($article);
        $section = new Section();
        $sectionForm = $this->createForm(AddSectionType::class, $section);
        $sectionForm->handleRequest($request);
        //Vérification de la conformité des données entrées par l'utilisateur
        if ($sectionForm->isSubmitted() && $sectionForm->isValid()) {
            $image = $sectionForm->get('image')->getData();
            if($image){
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                    $image->move(
                        $this->getParameter('images_directory'),
                        $fichier
                    );
                $section->setImage($fichier);
            }

            $section->setArticle($article);
            $section->setCreatedAt(new DateTime('Europe/Paris'));
            $section->setCreatedBy($this->getUser());
            $manager->getManager()->persist($section);
            $manager->getManager()->flush();
            return $this->redirectToRoute('admin_sections', [
                "id" => $article->getId(),
                "slug" => $twig->getFilter('slugify')->getCallable()($article->getTitle())

            ]);
        }

        return $this->render('admin/sections.html.twig', [
            'sectionForm' => $sectionForm->createView(),
            'articleForm' => $articleForm->createView(),
            'sections' => $sections,
            'article' => $article
        ]);
    }
    #[Route('admin/section/{id}', name: 'admin_section')]
    public function section(Section $section, Request $request, ManagerRegistry $manager, Environment $twig): Response
    {
        $form = $this->createForm(AddSectionType::class, $section);
        $form->handleRequest($request);
        //Vérification de la conformité des données entrées par l'utilisateur
        if ($form->isSubmitted() && $form->isValid()) {
            $image = $form->get('image')->getData();
            if($image){
                $fichier = md5(uniqid()) . '.' . $image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory'),
                    $fichier
                );
                $section->setImage($fichier);
            }
            $manager->getManager()->persist($section);
            $manager->getManager()->flush();
            $article = $section->getArticle();
            return $this->redirectToRoute('admin_sections', [
                "id" => $article->getId(),
                "slug" => $twig->getFilter('slugify')->getCallable()($article->getTitle())

            ]);
        }

        return $this->render('admin/section.html.twig', [
            'section' => $section,
            'form' => $form->createView(),
            'article' => $section->getArticle()
        ]);
    }
    #[Route('admin/categories', name: 'admin_categories')]
    public function categories(Request $request, ManagerRegistry $manager, CategorieRepository $categorieRepo): Response
    {
        $categories = $categorieRepo->findAllOrderByNameASC();
        $categorie = new Categorie();

        $form = $this->createForm(AddCategorieType::class, $categorie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $manager->getManager()->persist($categorie);
            $manager->getManager()->flush();
            return $this->redirectToRoute('admin_categories');
        }

        return $this->render('admin/categories.html.twig', [
            'form' => $form->createView(),
            'categories' => $categories
        ]);
    }
    #[Route('admin/categorie/{name}', name: 'admin_categorie')]
    public function categorie(Categorie $categorie, ArticleRepository $articleRepo, Request $request, ManagerRegistry $manager): Response
    {

        $form = $this->createForm(AddCategorieType::class, $categorie);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $manager->getManager()->persist($categorie);
            $manager->getManager()->flush();
            return $this->redirectToRoute('admin_categories');
        }

        $articles = $articleRepo->findAllByCategory($categorie);
        return $this->render('admin/categorie.html.twig', [
            'form' => $form->createView(),
            'categorie' => $categorie,
            'articles' => $articles,
        ]);
    }
    #[Route('/admin/stats', name: 'admin_stats')]
    public function stats(VisitorRepository $visitorRepo): Response
    {
        $PageListToStringList = [];
        $nbrOfVisitList = [];
        $pageList = [];

        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');
        $hour = $date->format('H');
        $visitors = $visitorRepo->findAllByDESC();
        
        $dailyVisitors = count($visitorRepo->findVisitsByDate($year, $month, $day, ""));
        $monthlyVisitors = count($visitorRepo->findVisitsByDate($year, $month, "", ""));
        $yearlyVisitors = count($visitorRepo->findVisitsByDate($year, "", "", ""));

        $pageListFromRepo = $visitorRepo->findVisitsByPage();
        
        foreach ($pageListFromRepo as $page) {
        $PageListToStringList[] = $page['page'];

        
        }
        if($PageListToStringList){
            $nbrOfVisitList = array_count_values($PageListToStringList);
            $pageList = array_keys($nbrOfVisitList);
        }
        return $this->render('admin/adminStats.html.twig', [
            "visitors" => $visitors,
            "nbrOfVisitList" => $nbrOfVisitList,
            "pageList" => $pageList,
            "dailyVisitors" => $dailyVisitors,
            "monthlyVisitors" => $monthlyVisitors,
            "yearlyVisitors" => $yearlyVisitors,
        ]);
    }
}
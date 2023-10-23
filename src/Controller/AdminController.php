<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use Twig\Environment;
use App\Entity\Domain;
use App\Entity\Article;
use App\Entity\Section;
use App\Entity\Categorie;
use App\Form\AddArticleType;
use App\Form\AddSectionType;
use App\Form\EditArticleType;
use Intervention\Image\Image;
use App\Form\AddCategorieType;
use App\Form\AddDomainType;
use App\Form\RegistrationType;
use App\Twig\SlugifyExtension;
use App\Service\ChatGptService;
use App\Repository\ArticleRepository;
use App\Repository\SectionRepository;
use App\Repository\VisitorRepository;
use App\Repository\CategorieRepository;
use App\Repository\DomainRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\ParameterBag;
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
            $article->setUpdatedAt(new DateTime('Europe/Paris'));
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
            $article->setUpdatedAt(new DateTime('Europe/Paris'));
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
    #[Route('/admin/domains', name: 'admin_domains')]
    public function domains( Request $request, ManagerRegistry $manager, DomainRepository $domainRepo): Response
    {
        $domain = new Domain();
        $form = $this->createForm(AddDomainType::class, $domain);

        $domains = $domainRepo->findAll();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $manager->getManager()->persist($domain);
            $manager->getManager()->flush();
            
            return $this->redirectToRoute('admin_domains');
        }

        return $this->render('admin/adminDomains.html.twig', [
            'form' => $form->createView(),
            'domains' => $domains
        ]);
    }

    #[Route('/admin/domain/{name}', name: 'admin_domain')]
    public function domain(Domain $domain, Request $request, ManagerRegistry $manager, DomainRepository $domainRepo): Response
    {
        $form = $this->createForm(AddDomainType::class, $domain);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $manager->getManager()->persist($domain);
            $manager->getManager()->flush();
            return $this->redirectToRoute('admin_domains');
        }

        return $this->render('admin/adminDomain.html.twig', [
            'form' => $form->createView(),
            'domain' => $domain
        ]);
    }

    #[Route('/admin/stats/', name: 'admin_stats')]
    public function stats(VisitorRepository $visitorRepo): Response
    {
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');

        $visitors = $visitorRepo->findVisitsByDate($year, $month, $day, "");

        $limit = 1;
        $countries = $visitorRepo->findOccurrencesByVisitorParam('country', null, $limit);
        $articles = $visitorRepo->findAllOccurrencesByArticle($limit);
        $categories = $visitorRepo->findOccurrencesByCategoryAndRatio($limit);

        $i = 1;
        while ($i < 31){
            if($i < 24){
            $dailyVisitorsGraph[] = count($visitorRepo->findVisitsByDate($year, $month, $day, strval($i)));
            }
            if ($i < 13){
            $yearlyVisitorsGraph[] = count($visitorRepo->findVisitsByDate($year, strval($i), "", ""));
            }
            $monthlyVisitorsGraph[] = count($visitorRepo->findVisitsByDate($year, $month, strval($i), ""));
            $i++;
        }

        $dailyVisitors = count($visitors);
        $monthlyVisitors = count($visitorRepo->findVisitsByDate($year, $month, "", ""));
        $yearlyVisitors = count($visitorRepo->findVisitsByDate($year, "", "", ""));

        return $this->render('admin/adminStats.html.twig', [
            "visitors" => $visitors,
            "countries" => $countries,
            "categories" => $categories,
            "articles" => $articles,
            "dailyVisitors" => $dailyVisitors,
            "monthlyVisitors" => $monthlyVisitors,
            "yearlyVisitors" => $yearlyVisitors,
            "dailyVisitorsGraph" => json_encode($dailyVisitorsGraph),
            "monthlyVisitorsGraph" => json_encode($monthlyVisitorsGraph),
            "yearlyVisitorsGraph" => json_encode($yearlyVisitorsGraph)
        ]);
    }

    #[Route('/admin/stats/articles', name: 'admin_stats_articles')]
    public function stat_articles(VisitorRepository $visitorRepo, Request $request,): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 15;

        $articles = $visitorRepo->findAllOccurrencesByArticle();

        return $this->render('admin/adminStatsArticles.html.twig', [
            "articles" => $articles,
            'totalPages' => ceil(count($articles) / $limit) + 1,
            'limit' => $limit
        ]);
    }

    #[Route('/admin/stats/article/{id}/{slug}', name: 'admin_stats_article')]
    public function stat_article(Article $article, VisitorRepository $visitorRepo): Response
    {
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');

        $visits = $visitorRepo->findVisitsByPage($article->getId());

        $dailyVisitors = count($visitorRepo->findVisitsByDateWithParam($year, $month, $day, "",'page',$article->getId()));
        $monthlyVisitors = count($visitorRepo->findVisitsByDateWithParam($year, $month, "", "", 'page',$article->getId()));
        $yearlyVisitors = count($visitorRepo->findVisitsByDateWithParam($year, "", "", "",'page',$article->getId()));

        $countries = $visitorRepo->findCountriesByArticle($article);


        $i = 1;
        while ($i < 31){
            if($i < 24){
            $dailyVisitorsGraph[] = count($visitorRepo->findVisitsByDateWithParam($year, $month, $day, strval($i),'page',$article->getId()));
            }
            if ($i < 13){
            $yearlyVisitorsGraph[] = count($visitorRepo->findVisitsByDateWithParam($year, strval($i), "", "",'page',$article->getId()));
            }
            $monthlyVisitorsGraph[] = count($visitorRepo->findVisitsByDateWithParam($year, $month, strval($i), "", 'page',$article->getId()));
            $i++;
        }

        return $this->render('admin/adminStatsArticle.html.twig', [
            "visits" => $visits,
            "dailyVisitors" => $dailyVisitors,
            "countries" => $countries,
            "monthlyVisitors" => $monthlyVisitors,
            "yearlyVisitors" => $yearlyVisitors,
            "dailyVisitorsGraph" => json_encode($dailyVisitorsGraph),
            "monthlyVisitorsGraph" => json_encode($monthlyVisitorsGraph),
            "yearlyVisitorsGraph" => json_encode($yearlyVisitorsGraph)
        ]);
    }

    #[Route('/admin/stats/graphs', name: 'admin_stats_graphs')]
    public function stat_graphs(VisitorRepository $visitorRepo): Response
    {

        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');

        $visitors = $visitorRepo->findVisitsByDate($year, $month, $day, "");    

        $i = 1;
        while ($i < 31){
            if($i < 24){
            $dailyVisitorsGraph[] = count($visitorRepo->findVisitsByDate($year, $month, $day, strval($i)));
            $dailyPreviousVisitorsGraph[] = count($visitorRepo->findVisitsByDate($year, $month, $day-1, strval($i)));
            }
            if ($i < 13){
            $yearlyVisitorsGraph[] = count($visitorRepo->findVisitsByDate($year, strval($i), "", ""));
            $yearlyPreviousVisitorsGraph[] = count($visitorRepo->findVisitsByDate($year-1, strval($i), "", ""));
            }
            $monthlyVisitorsGraph[] = count($visitorRepo->findVisitsByDate($year, $month, strval($i), ""));
            $monthlyPreviousVisitorsGraph[] = count($visitorRepo->findVisitsByDate($year, $month-1, strval($i), ""));
            $i++;
        }

        $dailyVisitors = count($visitors);
        $dailyPreviousVisitors = count($visitorRepo->findVisitsByDate($year, $month, $day-1, ""));
        $monthlyVisitors = count($visitorRepo->findVisitsByDate($year, $month, "", ""));
        $monthlyPreviousVisitors = count($visitorRepo->findVisitsByDate($year, $month-1, "", ""));
        $yearlyVisitors = count($visitorRepo->findVisitsByDate($year, "", "", ""));
        $yearlyPreviousVisitors = count($visitorRepo->findVisitsByDate($year-1, "", "", ""));


        return $this->render('admin/adminStatsGraphs.html.twig', [
            "dailyPreviousVisitors" => $dailyPreviousVisitors,
            "monthlyPreviousVisitors" => $monthlyPreviousVisitors,
            "yearlyPreviousVisitors" => $yearlyPreviousVisitors,
            "dailyVisitors" => $dailyVisitors,
            "monthlyVisitors" => $monthlyVisitors,
            "yearlyVisitors" => $yearlyVisitors,
            "dailyVisitorsGraph" => json_encode($dailyVisitorsGraph),
            "monthlyVisitorsGraph" => json_encode($monthlyVisitorsGraph),
            "yearlyVisitorsGraph" => json_encode($yearlyVisitorsGraph),
            "dailyPreviousVisitorsGraph" => json_encode($dailyPreviousVisitorsGraph),
            "monthlyPreviousVisitorsGraph" => json_encode($monthlyPreviousVisitorsGraph),
            "yearlyPreviousVisitorsGraph" => json_encode($yearlyPreviousVisitorsGraph)

        ]);
    }

    #[Route('/admin/stats/categories', name: 'admin_stats_categories')]
    public function stat_categories(VisitorRepository $visitorRepo): Response
    {
        $categories = $visitorRepo->findOccurrencesByCategoryAndRatio();

        return $this->render('admin/adminStatsCategories.html.twig', [
            "categories" => $categories
        ]);
    }

    #[Route('/admin/stats/category/{name}', name: 'admin_stats_category')]
    public function stat_category(Categorie $categorie, VisitorRepository $visitorRepo): Response
    {
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');

        $visits = $visitorRepo->findAllVisitsbyCategory($categorie->getName());

        $articles = $visitorRepo->findOccurrencesByArticleInCategoryAndRatio($categorie->getName(), 3);

        $dailyVisitors = count($visitorRepo->findVisitsByDateByArticleCategory($year, $month, $day, "",$categorie->getName()));
        $monthlyVisitors = count($visitorRepo->findVisitsByDateByArticleCategory($year, $month, "", "",$categorie->getName()));
        $yearlyVisitors = count($visitorRepo->findVisitsByDateByArticleCategory($year, "", "", "",$categorie->getName()));

        $i = 1;
        while ($i < 31){
            if($i < 24){
            $dailyVisitorsGraph[] = count($visitorRepo->findVisitsByDateByArticleCategory($year, $month, $day, strval($i),$categorie->getName()));
            }
            if ($i < 13){
            $yearlyVisitorsGraph[] = count($visitorRepo->findVisitsByDateByArticleCategory($year, strval($i), "", "",$categorie->getName()));
            }
            $monthlyVisitorsGraph[] = count($visitorRepo->findVisitsByDateByArticleCategory($year, $month, strval($i), "",$categorie->getName()));
            $i++;
        }

        return $this->render('admin/adminStatsCategory.html.twig', [
            "visits" => $visits,
            "articles" => $articles,
            "category" => $categorie,
            "dailyVisitors" => $dailyVisitors,
            "monthlyVisitors" => $monthlyVisitors,
            "yearlyVisitors" => $yearlyVisitors,
            "dailyVisitorsGraph" => json_encode($dailyVisitorsGraph),
            "monthlyVisitorsGraph" => json_encode($monthlyVisitorsGraph),
            "yearlyVisitorsGraph" => json_encode($yearlyVisitorsGraph)
        ]);
    }

    #[Route('/admin/stats/countries', name: 'admin_stats_countries')]
    public function stat_countries(VisitorRepository $visitorRepo): Response
    {
        $countries = $visitorRepo->findOccurrencesByVisitorParam('country');
        foreach($countries as $country){
            $countryGraph[] = $country["country"];
            $numberGraph[] = $country["number"];
        }
        
        return $this->render('admin/adminStatsCountries.html.twig', [
            "countries" => $countries,
            "country" => json_encode($countryGraph),
            "number" => json_encode($numberGraph),
        ]);
    }

    #[Route('/admin/stats/country/{country}', name: 'admin_stats_country')]
    public function stat_country(string $country, VisitorRepository $visitorRepo): Response
    {
        $date = new DateTime();
        $year = $date->format('Y');
        $month = $date->format('m');
        $day = $date->format('d');

        $dailyVisitors = count($visitorRepo->findVisitsByDateWithParam($year, $month, $day, "",'country',$country));
        $monthlyVisitors = count($visitorRepo->findVisitsByDateWithParam($year, $month, "", "", 'country',$country));
        $yearlyVisitors = count($visitorRepo->findVisitsByDateWithParam($year, "", "", "",'country',$country));

        $i = 1;
        while ($i < 31){
            if($i < 24){
            $dailyVisitorsGraph[] = count($visitorRepo->findVisitsByDateWithParam($year, $month, $day, strval($i),'country',$country));
            }
            if ($i < 13){
            $yearlyVisitorsGraph[] = count($visitorRepo->findVisitsByDateWithParam($year, strval($i), "", "",'country',$country));
            }
            $monthlyVisitorsGraph[] = count($visitorRepo->findVisitsByDateWithParam($year, $month, strval($i), "", 'country',$country));
            $i++;
        }
       
        $visits = $visitorRepo->findVisitsByCountry($country);

        return $this->render('admin/adminStatsCountry.html.twig', [
            "visits" => $visits,
            "dailyVisitors" => $dailyVisitors,
            "monthlyVisitors" => $monthlyVisitors,
            "yearlyVisitors" => $yearlyVisitors,
            "dailyVisitorsGraph" => json_encode($dailyVisitorsGraph),
            "monthlyVisitorsGraph" => json_encode($monthlyVisitorsGraph),
            "yearlyVisitorsGraph" => json_encode($yearlyVisitorsGraph)
        ]);
    }
}
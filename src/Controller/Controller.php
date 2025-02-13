<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use Twig\Environment;
use App\Entity\Article;
use App\Entity\Visitor;
use App\Entity\Categorie;
use App\Repository\ArticleRepository;
use App\Repository\CategorieRepository;
use App\Repository\SectionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;

class Controller extends AbstractController
{
    private $manager;
    public function __construct(ManagerRegistry $manager)
    {
        $this->manager = $manager;
    }

    #[Route('/sitemap.xml', name: 'sitemap')]
    public function sitemap(ArticleRepository $articleRepo, Environment $twig): Response
    {
        $articles = $articleRepo->findAllOnline();
        $urls = [];

        foreach ($articles as $article){
            array_push($urls, "https://askaway.fr/article/" . $article->getId() . "/" . $twig->getFilter('slugify')->getCallable()($article->getTitle()));
        }
        // Ajoutez ici d'autres URLs de votre projet Symfony en utilisant une boucle for ou en récupérant les données dynamiquement
        $response = $this->render('sitemap.xml.twig', [
            'urls' => $urls,
        ]);

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
    #[Route('/22735fbd-b59f-4664-9f86-7b1d44da830b.html', name: 'netlink')]
    public function netlinkdeal(): Response
    {
        return $this->render('22735fbd-b59f-4664-9f86-7b1d44da830b.html');
    }

    #[Route('/', name: 'home')]
    public function index(Request $request, ArticleRepository $articleRepo, CategorieRepository $categorieRepo): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 15;
        
        $articles = $articleRepo->findPaginatedArticles($page, $limit);
        $categories = $categorieRepo->findAllOrderByNameASC();
        $lastArticle = $articles[0];
        unset($articles[0]);
        return $this->render('/index.html.twig', [
            "articles" => $articles,
            "lastArticle" => $lastArticle,
            "categories" => $categories,
            'currentPage' => $page,
            'totalPages' => ceil(count($articleRepo->findAllOnline()) / $limit) + 1,
            'limit' => $limit
        ]);
    }
    #[Route('/articles/{name}', name: 'categorie')]
    public function articles(Categorie $categorie, ArticleRepository $articleRepo, CategorieRepository $categorieRepo): Response
    {
        $articles = $articleRepo->findAllOnlineByCategory($categorie);
        $categories = $categorieRepo->findAllOrderByNameASC();

        return $this->render('/categorie.html.twig', [
            'articles' =>$articles,
            'currentCategory' => $categorie,
            'categories' => $categories

        ]);
    }
    #[Route('/article/{id}/{slug}', name: 'article')]
    public function article(Article $article, SectionRepository $sectionRepo, ArticleRepository $articleRepo, CategorieRepository $categorieRepo): Response
    {
        if(!$article->isOnline()){
            return $this->createAccessDeniedException();
        }
        $categories = $categorieRepo->findAllOrderByNameASC();
        $limit = 10;
        $this->addVisit($article);
        $lastArticles = $articleRepo->find10LastArticles();
        $otherArticleByCateorgy = $articleRepo->findLastOnlineByCategory($article->getCategorie(), $limit);

        $sectionByArticle = $sectionRepo->findAllByArticle($article);
        $metaDesc = substr($sectionRepo->findOneByArticle($article)[0]["body"], 0, 150);

        return $this->render('/article.html.twig', [
            'article' =>$article,
            'lastArticles' =>$lastArticles,
            'otherArticleByCateorgy' =>$otherArticleByCateorgy,
            'sections' => $sectionByArticle,
            'metaDesc' => $metaDesc,
            'categories' => $categories
        ]);
    }
    //Search bar Axios route
    #[Route('/search/suggestions', name: 'search_suggestions')]
    public function suggestions(Request $request, ArticleRepository $articleRepo, SerializerInterface $serializer): Response
    {
        $keyword = $request->query->get('keyword');

        $suggestions = $articleRepo->findSuggestionsByKeyword($keyword);

        return new JsonResponse($suggestions);
    }
    
    #[Route('/search/loadMore', name: 'loadMore')]
    public function loadMore(Request $request, ArticleRepository $articleRepo)
    {
        $page = $request->query->getInt('page');
        $limit = 15; // Nombre d'éléments par page

        $items = $articleRepo->findPaginatedArticles($page, $limit);

        return new JsonResponse($items);
    }

    public function addVisit(Article $page)
    {
       if ($this->getUser() == null) {
            $visitor = new Visitor();
            $request = Request::createFromGlobals();
            $ip = $request->getClientIp();
            //$ip = "2a02:a03f:600f:a800:ac20:d559:f642:6c17";
            $details = json_decode(file_get_contents("http://ip-api.com/json/{$ip}"));
            $visitor->setIp(substr($ip, -9));
            $visitor->setPage($page);
            $visitor->setCity($details->city);
            $visitor->setRegion($details->region);
            $visitor->setCountry($details->country);
            $visitor->setVisitedAt(new DateTime('Europe/Paris'));

            $this->manager->getManager()->persist($visitor);
            $this->manager->getManager()->flush();
        }
    }
    
}

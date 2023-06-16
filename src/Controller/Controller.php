<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use Twig\Environment;
use App\Entity\Article;
use App\Entity\Categorie;
use App\Entity\Visitor;
use App\Repository\ArticleRepository;
use App\Repository\SectionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

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


    #[Route('/', name: 'home')]
    public function index(Request $request, ArticleRepository $articleRepo): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 15;
        
        $articles = $articleRepo->findPaginatedArticles($page, $limit);
        
        return $this->render('/index.html.twig', [
            "articles" => $articles,
            'currentPage' => $page,
            'totalPages' => ceil(count($articleRepo->findAll()) / $limit),
        ]);
    }
    #[Route('/articles/{name}', name: 'categorie')]
    public function articles(Categorie $categorie, ArticleRepository $articleRepo): Response
    {
        $articles = $articleRepo->findAllOnlineByCategory($categorie);

        return $this->render('/categorie.html.twig', [
            'articles' =>$articles,
            'categorie' => $categorie
        ]);
    }
    #[Route('/article/{id}/{slug}', name: 'article')]
    public function article(Article $article, SectionRepository $sectionRepo, ArticleRepository $articleRepo): Response
    {
        $this->addVisit($article);
        $lastArticles = $articleRepo->find10LastArticles();
        $otherArticleByCateorgy = $articleRepo->findAllOnlineByCategory($article->getCategorie());

        $sectionByArticle = $sectionRepo->findAllByArticle($article);
        $metaDesc = substr($sectionRepo->findOneByArticle($article)[0]["body"], 0, 150);

        return $this->render('/article.html.twig', [
            'article' =>$article,
            'lastArticles' =>$lastArticles,
            'otherArticleByCateorgy' =>$otherArticleByCateorgy,
            'sections' => $sectionByArticle,
            'metaDesc' => $metaDesc
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

    public function addVisit(string $page)
    {
       if ($this->getUser() == null) {
            $visitor = new Visitor();
            $request = Request::createFromGlobals();
            //$ip = $request->getClientIp();
            $ip = "2a02:a03f:600f:a800:ac20:d559:f642:6c17";
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

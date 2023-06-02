<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Article;
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
    #[Route('/', name: 'home')]
    public function index(ArticleRepository $articleRepo): Response
    {
        $articles = $articleRepo->findAll();
        
        return $this->render('/index.html.twig', [
            "articles" => $articles
        ]);
    }
    #[Route('/articles', name: 'articles')]
    public function articles(): Response
    {
        return $this->render('/articles.html.twig', [
            
        ]);
    }
    #[Route('/article/{id}/{slug}', name: 'article')]
    public function article(Article $article, SectionRepository $sectionRepo): Response
    {
        $this->addVisit($article);

        $sectionByArticle = $sectionRepo->findAllByArticle($article);
        $metaDesc = substr($sectionRepo->findOneByArticle($article)[0]["body"], 0, 150);

        return $this->render('/article.html.twig', [
            'article' =>$article,
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
    public function addVisit(string $page)
    {
       if ($this->getUser() == null) {
            $visitor = new Visitor();
            //$ip = $_SERVER['HTTP_X_FORWARED_FOR'];
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
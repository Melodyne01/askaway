<?php

namespace App\Controller;

use DateTime;
use App\Entity\Domain;
use App\Entity\Visitor;
use App\Repository\VisitorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    #[Route('/api/login', name: 'api_login', methods:['POST'])]
    public function login()
    {
       $user = $this->getUser();
       return $this->json([
        'id' => $user->getUserIdentifier(),
        'roles' => $user->getRoles()
       ]);
    }
    #[Route("/api/images/{imageName}", name: "api_image_get", methods: ["GET"])]
    public function getImage(string $imageName): BinaryFileResponse
    {
        $filePath = 'public/upload/' . $imageName;

        // Vérifiez que le fichier existe avant de le renvoyer
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException('Image not found');
        }

        // Utilisez BinaryFileResponse pour renvoyer l'image avec le bon type de contenu (Content-Type)
        return new BinaryFileResponse($filePath);
    }
    #[Route('/admin/api/get/{domain}', name: 'api_articles')]
    public function apiGetArticles(Domain $domain, ManagerRegistry $manager)
    {
        // Créez une instance de HttpClient
        $client = HttpClient::create();
        try {
            // Effectuez l'appel à l'API externe
            /*$response = $client->request('GET', 'https://127.0.0.1:8000/api/articles');
            //$response = $client->request('GET', $domain->getUrl().'api/articles');

            // Décodez le contenu JSON de la réponse
            $data = json_decode($response->getContent(), true);

            $articles = $data["hydra:member"];

            */
            $visitorRepo = $manager->getRepository(VisitorRepository::class);
            $visits = $visitorRepo->findAll();

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


            // Retournez les données dans une réponse JSON
            return $this->render('admin/api/articles.html.twig', [
                "visitors" => $visitors,
                "countries" => $countries,
                "categories" => $categories,
                "articles" => $articles,
                "dailyVisitors" => $dailyVisitors,
                "monthlyVisitors" => $monthlyVisitors,
                "yearlyVisitors" => $yearlyVisitors,
                "dailyVisitorsGraph" => json_encode($dailyVisitorsGraph),
                "monthlyVisitorsGraph" => json_encode($monthlyVisitorsGraph),
                "yearlyVisitorsGraph" => json_encode($yearlyVisitorsGraph),
                "domain" =>$domain
            ]);
        } catch (\Throwable $e) {
            // Gérez les erreurs d'appel API
            // Vous pouvez renvoyer un message d'erreur approprié ou effectuer d'autres actions
            
            // Par exemple, renvoyer une réponse JSON avec un message d'erreur
            return new JsonResponse(['error' => $e], 500);
        }
    }
}

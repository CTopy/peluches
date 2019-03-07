<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Psr\Log\LoggerInterface;

use Doctrine\ORM\EntityManagerInterface;

use ApaiIO\Configuration\GenericConfiguration;
use ApaiIO\ApaiIO;
use ApaiIO\Operations\Search;

use DeezerAPI\Search as DeezerSearch ;

use AppBundle\Entity\Catalogue\Livre;
use AppBundle\Entity\Catalogue\Musique;
use AppBundle\Entity\Catalogue\Piste;
use AppBundle\Entity\Catalogue\Peluche;

class RechercheController extends Controller
{
	private $entityManager;
	
	public function __construct(EntityManagerInterface $entityManager)  {
		$this->entityManager = $entityManager;
	}
	
    /**
     * @Route("/afficheRecherche", name="afficheRecherche")
     */
    public function afficheRechercheAction(Request $request, LoggerInterface $logger)
    {
		$this->initBdd() ;
		$query = $this->entityManager->createQuery("SELECT a FROM AppBundle\Entity\Catalogue\Article a");
		$articles = $query->getResult();
		return $this->render('recherche.html.twig', [
            'articles' => $articles,
        ]);
    }
	
    /**
     * @Route("/afficheRechercheParMotCle", name="afficheRechercheParMotCle")
     */
    public function afficheRechercheParMotCleAction(Request $request, LoggerInterface $logger)
    {
		$this->initAmazon() ;
		//$query = $this->entityManager->createQuery("SELECT a FROM AppBundle\Entity\Catalogue\Article a "
		//										  ." where a.titre like :motCle");
		//$query->setParameter("motCle", "%".$request->query->get("motCle")."%") ;
		$query = $this->entityManager->createQuery("SELECT a FROM AppBundle\Entity\Catalogue\Article a "
												  ." where a.titre like '%".addslashes($request->query->get("motCle"))."%'");
		$articles = $query->getResult();
		return $this->render('recherche.html.twig', [
            'articles' => $articles,
        ]);
    }
    
    private function initBdd() {
        if (count($this->entityManager->getRepository("AppBundle\Entity\Catalogue\Article")->findAll()) == 0) {
            //modele : [titre, prix, disponibilite, image, [hauteur, largeur, longueur], texture]
            $articles = [
                ["Pusheen le Chat", 30, 100, "https://images-na.ssl-images-amazon.com/images/I/81yoVFBAxpL._SL1500_.jpg", [20, 10, 10], "Squishy", "avec Cookie"],
                ["Nahyë le Narval", 5, 100, "https://i.ebayimg.com/images/g/rrMAAOSw1TFb2Jt3/s-l640.jpg", [5, 5, 5], "Squishy", "Standard"]
            ];
            
            $index = 1;
            foreach ($articles as $article) {
                $entity = new Peluche();
                $entity->setRefArticle($index);
                
                $entity->setTitre($article[0]);
                $entity->setPrix($article[1]);
                $entity->setDisponibilite($article[2]);
                $entity->setImage($article[3]);
                
                $entity->setHauteur($article[4][0]);
                $entity->setLargeur($article[4][1]);
                $entity->setLongueur($article[4][2]);
                
                $entity->setTexture($article[5]);
                $entity->setColoris($article[6]);
                
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                $index++;
            }
        }
    }
}

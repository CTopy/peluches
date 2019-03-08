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

use Doctrine\ORM\Query\ResultSetMapping;

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
}

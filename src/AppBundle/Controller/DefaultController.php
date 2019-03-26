<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Psr\Log\LoggerInterface;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;

use AppBundle\Entity\Catalogue\Livre;
use AppBundle\Entity\Panier\Panier;
use AppBundle\Entity\Panier\LignePanier;
use AppBundle\Entity\Catalogue\Peluche;

use Doctrine\ORM\EntityManagerInterface;

class DefaultController extends Controller
{
	private $entityManager;
	private $logger;
	
	/*
	Pb with codeanywhere.com
	public function __construct(EntityManagerInterface $entityManager)  {
		$this->entityManager = $entityManager;
	}*/
	
	public function init()  {
		$this->entityManager = $this->container->get('doctrine')->getEntityManager();
		$this->logger = $this->container->get('monolog.logger.php');
	}

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
		$this->init() ;
        $this->initBdd() ;
		$query = $this->entityManager->createQuery("SELECT a FROM AppBundle\Entity\Catalogue\Article a GROUP BY a.titre")->setMaxResults(6);
		$articles = $query->getResult();
		return $this->render('home.html.twig', [
            'articles' => $articles,
        ]);
    }
    
    /**
    * @Route("/article", name="article")
    */
    public function article(Request $request) {
        $titre = $request->query->get('titre');
        
        //Création de la requête
        $query = $this->entityManager->createQuery('
            SELECT a FROM AppBundle\Entity\Catalogue\Article a
            WHERE a.titre = (
                SELECT b FROM AppBundle\Entity\Catalogue\Article b
                WHERE a.id = ?1
            )
        ')
           ->setParameter(1, $titre);
        
        //Executer la requête
        $query = $query->getResult();
        $articles = $query->getArrayResult();
        
        return $this->render('home.html.twig', [
            'articles' => $articles
        ]);
    }
    
    private function initBdd() {
        if (count($this->entityManager->getRepository("AppBundle\Entity\Catalogue\Article")->findAll()) == 0) {
            //modele : [titre, prix, disponibilite, image, [hauteur, largeur, longueur], texture]
            $articles = [
                ['Pusheen',30,100,'https://images-na.ssl-images-amazon.com/images/I/81yoVFBAxpL._SL1500_.jpg',[20,10,10],'Squishy','Cookie'],
                ['Pusheen',28,100,'https://m.media-amazon.com/images/I/61BGsbnzNVL._SR500,500_.jpg',[20,10,10],'Squishy','Glace'],
                ['Pusheen',35,100,'https://http2.mlstatic.com/peluche-pusheen-gato-unicornio-original-gund-envio-incluido-D_NQ_NP_889370-MLM26715198905_012018-F.jpg',[20,10,20],'Squishy','Unicorn'],
                ['Pusheen',24,100,'https://images-na.ssl-images-amazon.com/images/I/61tJW-CtdKL._SX466_.jpg',[20,10,20],'Doux','Standard'],
                ['Pusheen',26,100,'http://image.littleboss.com/10852/20170519/20170519230211-8115b9.jpg',[20,10,20],'Doux','Anniversaire'],
                ['Squeezamals',5,100,'https://i.ebayimg.com/images/g/rrMAAOSw1TFb2Jt3/s-l640.jpg',[5,5,5],'Squishy','Standard'],
                ['Rilakkuma',14,100,'https://www.chezfee.com/images/stories/virtuemart/product/boutique-kawaii-shop-chezfee-peluche-sanx-rilakkuma-miel-abeille-1.jpg',[35,25,16],'Doux','Abeille'],
                ['Paresseux',10,100,'http://www.airbrushkustom.fr/images/category_11/09B44621%20Katara%201829%20Jouet%20paresseux%20en%20peluche%20un%20doudou%20pelucheux%20pour%20b%20b%20ou%20cadeau%20de%20No%20l%20ou%20anniversaire%20pour%20enfants%20et%20adultes%20un%20paressuex%20mignon%2050%20cm.jpg',[35,20,20],'Fluffy','Standard'],
                ['Shiba Inu',8,100,'https://i2.cdscdn.com/pdt2/5/9/7/1/700x700/auc0659473238597/rw/anime-shiba-inu-peluche-sotf-oreiller-poupee-carto.jpg',[10,15,30],'Squishy','Standard'],
                ['Morse',8,100,'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ9JIUVwCX-yLrvtq-HrmxZlV1Wu5stdDE_XC0It5u86mwSir4s0g',[10,15,30],'Billes','Standard'],
                ['Hamster',9,100,'https://media.takealot.com/covers_tsins/55703998/MPTAL00600579-1-pdpxl.jpg',[20,15,15],'Doux','Rose'],
                ['Gudetama',25,100,'https://images-na.ssl-images-amazon.com/images/I/71urDnLO48L._SX425_.jpg',[25,20,15],'Dur','Sushi'],
                ['Gudetama',18,100,'https://images-na.ssl-images-amazon.com/images/I/61E1QgxY1FL._SY355_.jpg',[25,25,15],'Dur','Guerrier'],
                ['Gudetama',15,100,'https://i.ebayimg.com/images/g/-30AAOSwTO9Z7ij3/s-l300.jpg',[15,15,10],'Dur','Chat'],
                ['Gudetama',10,100,'https://sanrio-production-weblinc.netdna-ssl.com/product_images/gudetama-6-plush-hula/5761027169702d20ef000df3/zoom.jpg?c=1465975409',[10,10,10],'Dur','Hawaï'],
                ['Gudetama',8,100,'https://http2.mlstatic.com/D_NP_628969-MLM27987195323_082018-Q.jpg',[10,10,10],'Dur','Saucisse'],
                ['Gudetama',15,100,'https://images-na.ssl-images-amazon.com/images/I/61a3i%2BzRjBL._SX425_.jpg',[15,15,15],'Dur','Chef'],
                ['Gudetama',8,100,'https://images-na.ssl-images-amazon.com/images/I/71M3KxyOFlL._SX425_.jpg',[10,10,15],'Dur','Bacon'],
                ['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/51LAdAnhGIL.jpg',[5,5,5],'Squishy','Licorne'],
                ['Squeezamals',5,100,'https://cdn.shopify.com/s/files/1/0746/4219/products/fiona.jpg?v=1521916203',[5,5,5],'Squishy','Renard'],
                ['Squeezamals',5,100,'https://www.farmers.co.nz/INTERSHOP/static/WFS/Farmers-Shop-Site/-/Farmers-Shop/en_NZ/product/63/50/193/1/6350193_00_W460_H600.jpg',[5,5,5],'Squishy','Lapin'],
                ['Squeezamals',5,100,'https://top-toy.com/wp-content/uploads/2018/07/Squishimal.jpg',[5,5,5],'Squishy','Mouton Blanc'],
                ['Squeezamals',5,100,'https://quickbutik.imgix.net/3391j/products/5c00394453831.png',[5,5,5],'Squishy','Paresseux Marron'],
                ['Squeezamals',5,100,'https://cdn.shopify.com/s/files/1/0746/4219/products/pip.jpg?v=1521915519',[5,5,5],'Squishy','Panda'],
                ['Squeezamals',5,100,'https://www.toysrus.co.za/media/catalog/product/cache/1/image/600x778/e9c3970ab036de70892d86c6d221abfe/1/1/1169879---squeezamals-medium_monkey.jpg',[5,5,5],'Squishy','Singe'],
                ['Squeezamals',5,100,'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSom6p-FYhvMocHbTif98KwID6DhRSOG-3vqBm_r2UFP3bg0BAQRw',[5,5,5],'Squishy','Poussin'],
                ['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/61HMcnAsc6L._SX425_.jpg',[5,5,5],'Squishy','Niglo Marron'],
                ['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/516fh-Q%2B86L._SX425_.jpg',[5,5,5],'Squishy','Chien'],
                ['Squeezamals',5,100,'https://cdn3.volusion.com/lvpmx.fjznp/v/vspfiles/photos/SQS-S2-004-1.jpg',[5,5,5],'Squishy','Narval Rose'],
                ['Squeezamals',5,100,'https://cdn11.bigcommerce.com/s-0kvv9/images/stencil/1280x1280/products/275764/385326/sques2graciegiraffe__10144.1540308106.jpg?c=2&imbypass=on',[5,5,5],'Squishy','Girafe'],
                ['Squeezamals',5,100,'https://mmtcdn.blob.core.windows.net/084395e6770c4e0ebc5612f000acae8f/mmtcdn/Products22752-640x640-340078641.jpg',[5,5,5],'Squishy','Pingouin'],
                ['Squeezamals',5,100,'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBAPhrvJoyzXWtwWhkcfcqV49zdoYJ05GLYau9dOD6lbbCL2M7',[5,5,5],'Squishy','Mouton Rose'],
                ['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/61XdzQJp3IL._SX425_.jpg',[5,5,5],'Squishy','Niglo Rose'],
                ['Squeezamals',5,100,'https://cdn.shopify.com/s/files/1/1165/0750/products/Squeezamals_Series_2_Samantha_Sloth_3.5-Inch_Plush_200x200@2x.jpg?v=1541306912',[5,5,5],'Squishy','Paresseux bleu'],
                ['Squeezamals',5,100,'https://cdn11.bigcommerce.com/s-0kvv9/images/stencil/1280x1280/products/275760/385322/sques2beatuni__10952.1540307140.jpg?c=2&imbypass=on',[5,5,5],'Squishy','Licorne Bleu'],
                ['Squeezamals',5,100,'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQU6olnTdVZBOuRxIOS_zugTF6t6_Wz4ubcMF8a5UFxsULa8e0H',[5,5,5],'Squishy','Coccinelle'],
                ['Squeezamals',5,100,'https://cdn11.bigcommerce.com/s-0kvv9/images/stencil/1280x1280/products/254688/354577/603154006173__47986.1521590897.jpg?c=2&imbypass=on',[5,5,5],'Squishy','Pingouin Rose'],
                ['Squeezamals',5,100,'https://www.claires.com/dw/image/v2/BBTK_PRD/on/demandware.static/-/Sites-master-catalog/default/dw082ca4df/images/hi-res/29184_1.jpg?sw=734&sh=734&sm=fit',[5,5,5],'Squishy','Chat Rouge'],
                ['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/61pBhvXCGXL._SL1000_.jpg',[5,5,5],'Squishy','Chat Blanc'],
                ['Squeezamals',5,100,'https://www.claires.com/dw/image/v2/BBTK_PRD/on/demandware.static/-/Sites-master-catalog/default/dw77858162/images/hi-res/29173_1.jpg?sw=734&sh=734&sm=fit',[5,5,5],'Squishy','Pingouin Renne'],
                ['Squeezamals',5,100,'https://www.claires.com/dw/image/v2/BBTK_PRD/on/demandware.static/-/Sites-master-catalog/default/dw5970939e/images/hi-res/89646_3.jpg?sw=2000&sh=2000&sm=fit',[5,5,5],'Squishy','Chauve-souris'],
                ['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/51%2BO8scmXPL._SX425_.jpg',[5,5,5],'Squishy','Zèbre'],
                ['Squeezamals',5,100,'https://www.picclickimg.com/d/l400/pict/372584101633_/Squeezamals-Series-2-Medium-Plush-Nadia-the.jpg',[5,5,5],'Squishy','Narval Bleu'],
                ['Molang',20,100,'https://images-na.ssl-images-amazon.com/images/I/71kGSIoejSL._SX466_.jpg',[20,15,15],'Squishy','Standard'],
                ['Doraemon',35,100,'https://shop.r10s.jp/grengren/cabinet/01509976/02126982/02661380/img61096487.jpg',[30,25,20],'Dur','Standard'],
                ['Alpacasso',30,100,'https://d3ieicw58ybon5.cloudfront.net/ex/350.350/shop/product/dc0ac784dc884266a03c95c8c43468b8.jpg',[30,25,20],'Fluffy','CMJN'],
                ['Alpacasso',30,100,'https://www.dhresource.com/webp/m/0x0s/f2-albu-g4-M01-12-73-rBVaEFnPJMuACxFCAAFX8jAN0r8944.jpg/rainbow-alpacasso-plush-13cm-cute-kawaii.jpg',[30,25,20],'Fluffy','RVB'],
                ['Alpacasso',18,100,'http://stores-fast-infrastructure.com/content/3/81006_Super-mignon-35-cm-alpacasso-cheval-debout-topper-chapeau-alpaga-en-peluche-mouton-animal-jouet-poup%C3%A9e-cadeau-danniversaire-de-no%C3%ABl-denfants-dans-en.jpg',[20,15,15],'Fluffy','Standard'],
                ['Cthulhu',15,100,'https://www.geekoupop.com/wp-content/uploads/2018/01/CTHULHU_Peluche_Super_Cute_Cthulhu_Dark_Green_30_cm.jpg',[15,10,10],'Doux','Standard']
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

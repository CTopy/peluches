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
        $this->init();
        $this->initBdd();
        $ref = $request->query->get('ref');
        $query = $this->entityManager->createQuery('
        SELECT a FROM AppBundle\Entity\Catalogue\Article a
        WHERE a.titre = (
            SELECT b.titre FROM AppBundle\Entity\Catalogue\Article b WHERE b.refArticle = ?1
        )
        ')
       ->setParameter(1, $ref);
        
        //Executer la requête
        $articles = $query->getResult();
        
        return $this->render('article.html.twig', [
            'articles' => $articles
        ]);
    }
    
    /**
     * @Route("/categories", name="categories")
     */
    public function categories(Request $request)
    {
		$this->init();
        $this->initBdd();
		$query = $this->entityManager->createQuery('
        SELECT a
        FROM AppBundle\Entity\Catalogue\Peluche a
        WHERE a.refArticle IN (
                SELECT b.refArticle
                FROM AppBundle\Entity\Catalogue\Peluche b
                GROUP BY b.texture
             )
        ');
		$articles = $query->getResult();
		return $this->render('categories.html.twig', [
            'articles' => $articles,
        ]);
    }
    
    /**
     * @Route("/recherche-categorie", name="rechercheParCategorie")
     */
    public function rechercheParCategorie(Request $request)
    {
		$this->init();
        $this->initBdd();
		$query = $this->entityManager->createQuery('
        SELECT a
        FROM AppBundle\Entity\Catalogue\Peluche a
        WHERE a.texture = ?1 GROUP BY a.titre
        ');
        $query->setParameter(1, $request->query->get('cat'));
		$articles = $query->getResult();
		return $this->render('recherche.html.twig', [
            'articles' => $articles,
        ]);
    }
    
    /**
     * @Route("/recherche-motcles", name="rechercheParMotcles")
     */
    public function rechercheParMotcles(Request $request)
    {
		$this->init();
        $this->initBdd();
        $motcles = "%".strtolower($request->request->get('motcles'))."%";
        
        $query = $this->entityManager->createQuery('
            SELECT a
            FROM AppBundle\Entity\Catalogue\Peluche a
            WHERE LOWER(a.titre) LIKE ?1
            OR LOWER(a.description) LIKE ?1
            OR LOWER(a.texture) LIKE ?1 
            OR LOWER(a.coloris) LIKE ?1
            GROUP BY a.titre
        ');
        $query->setParameter(1, strtolower($motcles));
        $articles = $query->getResult();
        return $this->render('recherche.html.twig', [
            'articles' => $articles,
        ]);
    }
    
    private function initBdd() {
        if (count($this->entityManager->getRepository("AppBundle\Entity\Catalogue\Article")->findAll()) == 0) {
            //modele : [titre, prix, disponibilite, image, [hauteur, largeur, longueur], texture]
            $articles = [
['Pusheen',30,100,'https://images-na.ssl-images-amazon.com/images/I/81yoVFBAxpL._SL1500_.jpg',[20,10,10],'Squishy','Cookie','Peluche à l\'effigie de Pusheen, un chat dodu gris qui adore les câlins et les collations. Plus paresseux que lui, ça n\'existe pas ! Cette peluche d\'environ 24 cm de hauteur représente Pusheen avec sa friandise préférée : un cookie aux pépites de chocolat.La peluche est fabriquée avec des matériaux doux et souples. Elle se nettoie en surface uniquement.',["gris"]],
['Pusheen',28,100,'https://m.media-amazon.com/images/I/61BGsbnzNVL._SR500,500_.jpg',[20,10,10],'Squishy','Glace','Pusheen est une chatte toute mignonne et potelée de dessin animé et qui a fait l\'objet de bandes dessinées et d\'autocollants sur Facebook. Aujourd’hui, grâce à Internet, on peut utiliser Pusheen pour exprimer un sentiment, une émotion, une idée à ses messages. Aujourd’hui, on retrouve Pusheen dans la pop culture, comme par exemple ces peluches toutes douces.Peluche sous licence officielle Pusheen. Cette peluche fabriquée à partir d\'un matériau souple représente Pusheen avec un cornet de crème glacée à la main. Elle mesure environ 24 cm de hauteur.Conseil d\'entretien : lavable en surface avec un chiffon humide.',["gris"]],
['Pusheen',35,100,'https://http2.mlstatic.com/peluche-pusheen-gato-unicornio-original-gund-envio-incluido-D_NQ_NP_889370-MLM26715198905_012018-F.jpg',[20,10,20],'Squishy','Licorne','Pusheen un chat tigré gris et grassouillet qui aime les câlins, les collations et les déguisements. En tant que bande dessinée Web populaire, Pusheen apporte luminosité et gloussement à des millions de fans dans sa base de fans en croissance rapide. Cette peluche de 7 po donne vie à la magie Pusheen Mermaid dans des couleurs pastel accrocheuses! Comprend une queue brodée ainsi qu\'un arc en étoile de mer. Lavable en surface pour un nettoyage facile. ',["gris"]],
['Pusheen',24,100,'https://images-na.ssl-images-amazon.com/images/I/61tJW-CtdKL._SX466_.jpg',[20,10,20],'Doux','Classique','Vous connaissez sans doute cette mignonne petite boule de poils qui a envahi internet sous le nom de Pusheen ! C\'est un chat dont la vie trépidante est relatée sur un blog, sous forme de gifs animés. Elle nous fait partager ses passions et aventures quotidiennes : manger, dormir, escalader des meubles montagnes, faire une pizza manger, rentrer dans un carton, se déguiser en R2D2... Tout ça avec les pattes qui gigotent, la queue qui remue et les moustaches au vent.Pusheen the Cat fut créé en mai 2010 par Claire Belton et Andrew Duff sur leur blog BD, « Everyday Cute ». Cette mignonne petite chatte a rapidement envahi internet par tous les moyens disponibles, en smileys, gifs, fonds d’écran puis sur Facebook, Twitter...Pusheen est devenu le chat le plus connu de la toile !',["gris"]],
['Pusheen',26,100,'http://image.littleboss.com/10852/20170519/20170519230211-8115b9.jpg',[20,10,20],'Doux','Anniversaire','Le cadeau d\'anniversaire idéal pour les fans de Pusheen ! Ton chat tigré préféré porte un chapeau de fête et souffle dans une langue de belle-mère pour célébrer ton anniversaire !',["gris"]],
['Squeezamals',5,100,'https://i.ebayimg.com/images/g/rrMAAOSw1TFb2Jt3/s-l640.jpg',[5,5,5],'Squishy','Narval Bleu','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["bleu"]],
['Rilakkuma',14,100,'https://www.chezfee.com/images/stories/virtuemart/product/boutique-kawaii-shop-chezfee-peluche-sanx-rilakkuma-miel-abeille-1.jpg',[35,25,16],'Doux','Abeille','C\'est un jouet en peluche des personnages les plus populaires de San-X Rilakkuma Korilakkuma Kiiroitori Chairo Koguma',["marron", "noir", "jaune"]],
['Paresseux',10,100,'http://www.airbrushkustom.fr/images/category_11/09B44621%20Katara%201829%20Jouet%20paresseux%20en%20peluche%20un%20doudou%20pelucheux%20pour%20b%20b%20ou%20cadeau%20de%20No%20l%20ou%20anniversaire%20pour%20enfants%20et%20adultes%20un%20paressuex%20mignon%2050%20cm.jpg',[35,20,20],'Fluffy','','Ce paresseux est bien alangui au soleil, et magnifique avec ses reflets dorés et ses bras accrocheurs !',["marron"]],
['Shiba Inu',8,100,'https://i2.cdscdn.com/pdt2/5/9/7/1/700x700/auc0659473238597/rw/anime-shiba-inu-peluche-sotf-oreiller-poupee-carto.jpg',[10,15,30],'Squishy','','Ce Shiba Inu légèrement potelé et tout doux est LE compagnon de choix pour petits et grands. Son agréable sourire et ses petites pattes toutes mimi égaieront votre journée. Imaginez vous, le matin, au réveil, ouvrant les yeux, déposant votre regard sur cette délicate peluche : bonne humeur garantie !Cette peluche est remplie avec du coton de première qualité, ce qui la rend terriblement moelleuse ! Inutile de préciser que ce petit animal croquignolet aime qu’on lui porte de l’attention : pressez le dans vos bras, câlinez le, endormez vous avec, il en raffole !',["blanc", "orange"]],
['Morse',8,100,'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ9JIUVwCX-yLrvtq-HrmxZlV1Wu5stdDE_XC0It5u86mwSir4s0g',[10,15,30],'Billes','','Le morse est le roi de la banquise. Son poil épais lui tient chaud dans le froid de l\'Arctique et il fait rêver de nombreux enfants à de belles aventures au Pôle Nord. Le joyeux aime qu\'on le prenne dans ses bras et veille au sommeil paisible des petits comme des grands. Le morse accompagnera chaque enfant dans ses aventures tout en dispensant une saveur de lavande. ',["marron"]],
['Hamster',9,100,'https://media.takealot.com/covers_tsins/55703998/MPTAL00600579-1-pdpxl.jpg',[20,15,15],'Doux','Rose','Peluche toute douce trop kawaii hamster trop mignon dans sa petite fraise. Excellent cadeau de Noël à faire ou à vous faire qui ravira les rands comme les petits.',["rose"]],
['Gudetama',15,100,'https://images-na.ssl-images-amazon.com/images/I/71VardFQCtL._SX466_.jpg',[20,15,30],'Dur','','Peluche sous licence officielle à l\'effigie du personnage de bande dessinée : Gudetama. Retrouvez l\'oeuf paresseux dans sa position préférée, la position allongée. Cette peluche en polyester mesure environ 9 cm de hauteur. Gudetama est représenté en train de lézarder sur son blanc, pour ne pas changer !',["jaune", "blanc"]],
['Gudetama',25,100,'https://images-na.ssl-images-amazon.com/images/I/71urDnLO48L._SX425_.jpg',[25,20,15],'Dur','Sushi','Peluche sous licence officielle à l\'effigie du personnage de bande dessinée : Gudetama. Retrouvez l\'oeuf paresseux dans sa position préférée, la position allongée. Cette peluche en polyester mesure environ 9 cm de hauteur. Gudetama est représenté en train de lézarder sur son blanc, pour ne pas changer !',["jaune", "blanc", "noir"]],
['Gudetama',18,100,'https://images-na.ssl-images-amazon.com/images/I/61E1QgxY1FL._SY355_.jpg',[25,25,15],'Dur','Guerrier','Peluche sous licence officielle à l\'effigie du personnage de bande dessinée : Gudetama. Retrouvez l\'oeuf paresseux dans sa position préférée, la position allongée. Cette peluche en polyester mesure environ 9 cm de hauteur. Gudetama est représenté en train de lézarder sur son blanc, pour ne pas changer !',["jaune", "blanc"]],
['Gudetama',15,100,'https://i.ebayimg.com/images/g/-30AAOSwTO9Z7ij3/s-l300.jpg',[15,15,10],'Dur','Chat','Peluche sous licence officielle à l\'effigie du personnage de bande dessinée : Gudetama. Retrouvez l\'oeuf paresseux dans sa position préférée, la position allongée. Cette peluche en polyester mesure environ 9 cm de hauteur. Gudetama est représenté en train de lézarder sur son blanc, pour ne pas changer !',["jaune", "blanc"]],
['Gudetama',10,100,'https://sanrio-production-weblinc.netdna-ssl.com/product_images/gudetama-6-plush-hula/5761027169702d20ef000df3/zoom.jpg?c=1465975409',[10,10,10],'Dur','Hawaï','Peluche sous licence officielle à l\'effigie du personnage de bande dessinée : Gudetama. Retrouvez l\'oeuf paresseux dans sa position préférée, la position allongée. Cette peluche en polyester mesure environ 9 cm de hauteur. Gudetama est représenté en train de lézarder sur son blanc, pour ne pas changer !',["jaune", "blanc"]],
['Gudetama',8,100,'https://http2.mlstatic.com/D_NP_628969-MLM27987195323_082018-Q.jpg',[10,10,10],'Dur','Saucisse','Peluche sous licence officielle à l\'effigie du personnage de bande dessinée : Gudetama. Retrouvez l\'oeuf paresseux dans sa position préférée, la position allongée. Cette peluche en polyester mesure environ 9 cm de hauteur. Gudetama est représenté en train de lézarder sur son blanc, pour ne pas changer !',["jaune", "blanc"]],
['Gudetama',15,100,'https://images-na.ssl-images-amazon.com/images/I/61a3i%2BzRjBL._SX425_.jpg',[15,15,15],'Dur','Chef','Peluche sous licence officielle à l\'effigie du personnage de bande dessinée : Gudetama. Retrouvez l\'oeuf paresseux dans sa position préférée, la position allongée. Cette peluche en polyester mesure environ 9 cm de hauteur. Gudetama est représenté en train de lézarder sur son blanc, pour ne pas changer !',["jaune", "blanc"]],
['Gudetama',8,100,'https://images-na.ssl-images-amazon.com/images/I/71M3KxyOFlL._SX425_.jpg',[10,10,15],'Dur','Bacon','Peluche sous licence officielle à l\'effigie du personnage de bande dessinée : Gudetama. Retrouvez l\'oeuf paresseux dans sa position préférée, la position allongée. Cette peluche en polyester mesure environ 9 cm de hauteur. Gudetama est représenté en train de lézarder sur son blanc, pour ne pas changer !',["jaune", "blanc"]],
['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/51LAdAnhGIL.jpg',[5,5,5],'Squishy','Licorne','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["blanc"]],
['Squeezamals',5,100,'https://cdn.shopify.com/s/files/1/0746/4219/products/fiona.jpg?v=1521916203',[5,5,5],'Squishy','Renard','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["orange"]],
['Squeezamals',5,100,'https://www.farmers.co.nz/INTERSHOP/static/WFS/Farmers-Shop-Site/-/Farmers-Shop/en_NZ/product/63/50/193/1/6350193_00_W460_H600.jpg',[5,5,5],'Squishy','Lapin','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["blanc"]],
['Squeezamals',5,100,'https://top-toy.com/wp-content/uploads/2018/07/Squishimal.jpg',[5,5,5],'Squishy','Mouton Blanc','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["blanc"]],
['Squeezamals',5,100,'https://quickbutik.imgix.net/3391j/products/5c00394453831.png',[5,5,5],'Squishy','Paresseux Marron','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["marron"]],
['Squeezamals',5,100,'https://cdn.shopify.com/s/files/1/0746/4219/products/pip.jpg?v=1521915519',[5,5,5],'Squishy','Panda','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["noir", "blanc"]],
['Squeezamals',5,100,'https://www.toysrus.co.za/media/catalog/product/cache/1/image/600x778/e9c3970ab036de70892d86c6d221abfe/1/1/1169879---squeezamals-medium_monkey.jpg',[5,5,5],'Squishy','Singe','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["marron"]],
['Squeezamals',5,100,'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSom6p-FYhvMocHbTif98KwID6DhRSOG-3vqBm_r2UFP3bg0BAQRw',[5,5,5],'Squishy','Poussin','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["jaune"]],
['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/61HMcnAsc6L._SX425_.jpg',[5,5,5],'Squishy','Niglo Marron','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["marron"]],
['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/516fh-Q%2B86L._SX425_.jpg',[5,5,5],'Squishy','Chien','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["marron", "noir"]],
['Squeezamals',5,100,'https://www.toysntuck.co.uk/image-cache/s/q/u/e/e/squeezamals-series-2-medium-plush---narcissa-the-narwhal-5867d1575e5cc1aa4c3cf20c38a1460bd4588c04.jpeg',[5,5,5],'Squishy','Narval Rose','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["rose"]],
['Squeezamals',5,100,'https://www.toysntuck.co.uk/image-cache/s/q/u/e/e/squeezamals-series-2-medium-plush---gracie-the-giraffe-b127b0a68a6a68b5a0690358a943e68783735f4a.jpeg',[5,5,5],'Squishy','Girafe','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["jaune", "orange"]],
['Squeezamals',5,100,'https://mmtcdn.blob.core.windows.net/084395e6770c4e0ebc5612f000acae8f/mmtcdn/Products22752-640x640-340078641.jpg',[5,5,5],'Squishy','Pingouin','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["noir", "blanc"]],
['Squeezamals',5,100,'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRBAPhrvJoyzXWtwWhkcfcqV49zdoYJ05GLYau9dOD6lbbCL2M7',[5,5,5],'Squishy','Mouton Rose','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["rose"]],
['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/61XdzQJp3IL._SX425_.jpg',[5,5,5],'Squishy','Niglo Rose','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["rose"]],
['Squeezamals',5,100,'https://cdn.shopify.com/s/files/1/1165/0750/products/Squeezamals_Series_2_Samantha_Sloth_3.5-Inch_Plush_200x200@2x.jpg?v=1541306912',[5,5,5],'Squishy','Paresseux bleu','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["bleu"]],
['Squeezamals',5,100,'https://cdn11.bigcommerce.com/s-0kvv9/images/stencil/1280x1280/products/275760/385322/sques2beatuni__10952.1540307140.jpg?c=2&imbypass=on',[5,5,5],'Squishy','Licorne Bleu','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["bleu"]],
['Squeezamals',5,100,'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQU6olnTdVZBOuRxIOS_zugTF6t6_Wz4ubcMF8a5UFxsULa8e0H',[5,5,5],'Squishy','Coccinelle','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["rouge", "noir"]],
['Squeezamals',5,100,'https://cdn11.bigcommerce.com/s-0kvv9/images/stencil/1280x1280/products/254688/354577/603154006173__47986.1521590897.jpg?c=2&imbypass=on',[5,5,5],'Squishy','Pingouin Rose','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["rose"]],
['Squeezamals',5,100,'https://www.claires.com/dw/image/v2/BBTK_PRD/on/demandware.static/-/Sites-master-catalog/default/dw082ca4df/images/hi-res/29184_1.jpg?sw=734&sh=734&sm=fit',[5,5,5],'Squishy','Chat Rouge','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["rouge"]],
['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/61pBhvXCGXL._SL1000_.jpg',[5,5,5],'Squishy','Chat Blanc','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["blanc"]],
['Squeezamals',5,100,'https://www.claires.com/dw/image/v2/BBTK_PRD/on/demandware.static/-/Sites-master-catalog/default/dw77858162/images/hi-res/29173_1.jpg?sw=734&sh=734&sm=fit',[5,5,5],'Squishy','Pingouin Renne','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["gris","marron"]],
['Squeezamals',5,100,'https://www.claires.com/dw/image/v2/BBTK_PRD/on/demandware.static/-/Sites-master-catalog/default/dw5970939e/images/hi-res/89646_3.jpg?sw=2000&sh=2000&sm=fit',[5,5,5],'Squishy','Chauve-souris','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["noir"]],
['Squeezamals',5,100,'https://images-na.ssl-images-amazon.com/images/I/51%2BO8scmXPL._SX425_.jpg',[5,5,5],'Squishy','Zèbre','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["noir","blanc"]],
['Squeezamals',5,100,'https://www.picclickimg.com/d/l400/pict/372584101633_/Squeezamals-Series-2-Medium-Plush-Nadia-the.jpg',[5,5,5],'Squishy','Narval Bleu','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["bleu"]],
['Molang',20,100,'https://images-na.ssl-images-amazon.com/images/I/71kGSIoejSL._SX466_.jpg',[20,15,15],'Squishy','','Les Squeezamals sont d’adorables peluches toutes douces et parfumées qui reprennent leur forme initiale une fois aplaties !',["blanc"]],
['Doraemon',35,100,'https://shop.r10s.jp/grengren/cabinet/01509976/02126982/02661380/img61096487.jpg',[30,25,20],'Dur','','Peluches Doraemon.Vendues au choix à l\'unité ou par lot des 4 peluches. En cas d\'achat du lot de 4 peluches bénéficiez d\'une réduction sur le prix de vente ainsi que des frais de livraison identiques à ceux de l\'envoi d\'une seule peluche. Dimension : environ 19 cm.Peluches Doraemon hautes qualités neuves sous licence officielle, avec un toucher doux.',["bleu"]],
['Alpacasso',30,100,'https://d3ieicw58ybon5.cloudfront.net/ex/350.350/shop/product/dc0ac784dc884266a03c95c8c43468b8.jpg',[30,25,20],'Fluffy','CMJN','Idée Cadeau pour les fans d\'Alpacasso !La nouvelle version de cette grande peluche CMJN Alpacasso de 40cm hauteur ! Très kawaii, plus douce et plus brillante ~C\'est une nouvelle collection officielle de la marque japonaise Amuse !',["rose","jaune","cyan"]],
['Alpacasso',30,100,'https://www.dhresource.com/webp/m/0x0s/f2-albu-g4-M01-12-73-rBVaEFnPJMuACxFCAAFX8jAN0r8944.jpg/rainbow-alpacasso-plush-13cm-cute-kawaii.jpg',[30,25,20],'Fluffy','RVB','Idée Cadeau pour les fans d\'Alpacasso !La nouvelle version de cette grande peluche RVB Alpacasso de 40cm hauteur ! Très kawaii, plus douce et plus brillante ~C\'est une nouvelle collection officielle de la marque japonaise Amuse !',["rouge","vert","bleu","jaune"]],
['Alpacasso',18,100,'http://stores-fast-infrastructure.com/content/3/81006_Super-mignon-35-cm-alpacasso-cheval-debout-topper-chapeau-alpaga-en-peluche-mouton-animal-jouet-poup%C3%A9e-cadeau-danniversaire-de-no%C3%ABl-denfants-dans-en.jpg',[20,15,15],'Fluffy','Standard','Idée Cadeau pour les fans d\'Alpacasso !La nouvelle version de cette grande peluche Rainbow Alpacasso de 40cm hauteur ! Très kawaii, plus douce et plus brillante ~C\'est une nouvelle collection officielle de la marque japonaise Amuse !',["rose"]],
['Cthulhu',15,100,'https://www.geekoupop.com/wp-content/uploads/2018/01/CTHULHU_Peluche_Super_Cute_Cthulhu_Dark_Green_30_cm.jpg',[15,10,10],'Doux','','Divinité chaotique, haut de centaines de mètres, l\'apocalypse tentaculaire personnifiée : une peluche beaucoup trop mignonne !',["vert"]]
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
                $entity->setDescription($article[7]);
                $entity->setCouleurs($article[8]);
                
                $this->entityManager->persist($entity);
                $this->entityManager->flush();
                $index++;
            }
        }
    }
    
    private function colorNameToColorCode($name) {
        $colors = [
            'rouge' => 'red',
            'gris' => 'grey',
            'bleu' => 'blue',
            'marron' => 'brown',
            'noir' => 'black',
            'jaune' => 'yellow',
            'blanc' => 'whitesmoke',
            'orange' => 'orange',
            'rose' => 'hotpink',
            'cyan' => 'cyan',
            'vert' => 'green'
        ];
        $cond = false;
        $correspondant = "";
        foreach($colors as $color) {
            $cond = $cond xor $color == $name;
            $correspondant = $color;
        }   
        
        if($cond)
            return $correspondant;
        else return false;
        
    }
}

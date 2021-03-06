<?php

namespace AppBundle\Entity\Catalogue;

use Doctrine\ORM\Mapping as ORM;

/**
 * Peluche
 *
 * @ORM\Entity
 */
class Peluche extends Article
{
    private $TEXTURES_ENUM = [
        "Squishy",
        "Fluffy",
        "Doux",
        "Dur",
        "Billes",
        "Bizarre",
        "Bouillotte"
    ];
    
    /**
     * @var string
     *
     * @ORM\Column(name="hauteur", type="float")
     */
    private $hauteur;
    
    public function getHauteur() {
        return($this->hauteur);
    }
    
    public function setHauteur($h) {
        $this->hauteur = $h;
    }
    
    /**
     * @var float
     *
     * @ORM\Column(name="longueur", type="float")
     */
    private $longueur;
    
    public function setLongueur($lo) {
        $this->longueur = $lo;
    }
       public function getLongueur() {
        return($this->longueur);
    }
    /**
     * @var float
     *
     * @ORM\Column(name="largeur", type="float")
     */
    private $largeur;
    
    public function getLargeur() {
        return($this->largeur);
    }
    
    public function setLargeur($la) {
        $this->largeur = $la;
    }
    
        
    public function getDimensions() {
        return ["hauteur" => $this->hauteur, 
                "largeur" => $this->largeur, 
                "longueur" => $this->longueur];
    }
    
    /**
     * @var string
     *
     * @ORM\Column(name="texture", type="string")
     */
    private $texture;
    
    public function getTexture() {
        return $this->texture;
    }
    
    public function setTexture($string) {
        $this->texture = $string;
    }
    
    /**
     * @var string
     *
     * @ORM\Column(name="coloris", type="string")
     */
    private $coloris;
    
    public function getColoris() {
        return $this->coloris;
    }
    
    public function setColoris($nvColor) {
        $this->coloris = $nvColor;
    }
    
    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string")
     */
    private $description;
    
    public function getDescription() {
        return $this->description;
    }
    
    public function setDescription($nvDescription) {
        $this->description = $nvDescription;
    }
    
    /**
    * @var array
    *
    * @ORM\Column(name="couleurs", type="array", nullable=true)
    */
    private $couleurs;
    
    public function getCouleurs() {
        return $this->couleurs;
    }
    
    public function setCouleurs($array) {
        $this->couleurs = $array;
    }
}


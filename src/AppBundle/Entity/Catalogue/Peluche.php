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
    
    /**
     * @var string
     *
     * @ORM\Column(name="longueur", type="float")
     */
    private $longueur;
    
    /**
     * @var string
     *
     * @ORM\Column(name="largeur", type="float")
     */
    private $largeur;
    
    /**
     * @var string
     *
     * @ORM\Column(name="texture", type="string")
     */
    private $texture;
    
    /**
    *   Hauteur-largeur-longueur
    */
    public function getDimensions() {
        return [$this->hauteur, $this->largeur, $this->longueur];
    }
    
    public function setDimensions($h, $la, $lo) {
        $this->hauteur = $h;
        $this->largeur = $la;
        $this->longueur = $lo;
    }
    
    public function setHauteur($h) {
        $this->hauteur = $h;
    }
    
    public function setLargeur($la) {
        $this->largeur = $la;
    }
    
    public function setLongueur($lo) {
        $this->longueur = $lo;
    }
    
    public function getTexture() {
        return $this->texture;
    }
    
    public function setTexture($string) {
        $cond = true;
        foreach ($this->TEXTURES_ENUM as $tex)
            $cond = $cond && (strcmp($tex, $string));
        
        if ($cond)
            $this->texture = $string;
    }
}


<?php
namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

class Product
{
    /**
     * @ORM\Column(type="string")
     *
     */
    private $brochure;

    public function getBrochure()
    {
        return $this->brochure;
    }

    public function setBrochure($brochure)
    {
        $this->brochure = $brochure;

        return $this;
    }
}
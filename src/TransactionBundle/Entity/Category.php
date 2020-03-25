<?php

namespace TransactionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Category
 *
 * @ORM\Table(name="category")
 * @ORM\Entity(repositoryClass="TransactionBundle\Repository\CategoryRepository")
 */
class Category
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToMany(targetEntity="TransactionBundle\Entity\Product", inversedBy="categories")
     * @ORM\JoinColumn(nullable=true)
     */
    private $products;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="displayed", type="boolean", nullable=true)
     */
    private $displayed;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function getSimpleArray()
    {
        $products = null;
        foreach ($this->getProducts() as $p){
            $products[] = $p;
        }
        
        return $products;
    }
    
    public function __construct() {
        $this->setCreatedAt(new \DateTime("now"));
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    public function __toString() {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Category
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set displayed
     *
     * @param boolean $displayed
     *
     * @return Category
     */
    public function setDisplayed($displayed)
    {
        $this->displayed = $displayed;

        return $this;
    }

    /**
     * Get displayed
     *
     * @return boolean
     */
    public function getDisplayed()
    {
        return $this->displayed;
    }

    /**
     * Add product
     *
     * @param \TransactionBundle\Entity\Product $product
     *
     * @return Category
     */
    public function addProduct(\TransactionBundle\Entity\Product $product)
    {
        $this->products[] = $product;

        return $this;
    }

    /**
     * Remove product
     *
     * @param \TransactionBundle\Entity\Product $product
     */
    public function removeProduct(\TransactionBundle\Entity\Product $product)
    {
        $this->products->removeElement($product);
    }

    /**
     * Get products
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProducts()
    {
        return $this->products;
    }
}

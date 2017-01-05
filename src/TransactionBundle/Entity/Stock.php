<?php

namespace TransactionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Stock
 *
 * @ORM\Table(name="stock")
 * @ORM\Entity(repositoryClass="TransactionBundle\Repository\StockRepository")
 */
class Stock
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;
    
    /**
     * @var string
     *
     * @ORM\Column(name="value", type="integer")
     */
    private $value;
    
    /**
     * @var string
     *
     * @ORM\Column(name="alertLevel", type="integer", nullable=true)
     */
    private $alertLevel;
    
    /**
     * @ORM\ManyToOne(targetEntity="Product", inversedBy="stocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;
    
    /**
     * @ORM\ManyToOne(targetEntity="KmBundle\Entity\Branch", inversedBy="stocks")
     * @ORM\JoinColumn(nullable=false)
     */
    private $branch;

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
    
    public function decreaseValue($quantity)
    {
        $this->value = $this->value - $quantity;
    }
    
    public function __toString() {
        
        if($this->name){
            return $this->name;
        }
        
        return 'Stock';
        
    }
    
    public function __construct() {
        $this->setCreatedAt(new \DateTime("now"));
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Stock
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
     * Set name
     *
     * @param string $name
     *
     * @return Stock
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
     * Set value
     *
     * @param integer $value
     *
     * @return Stock
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return integer
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set product
     *
     * @param \TransactionBundle\Entity\Product $product
     *
     * @return Stock
     */
    public function setProduct(\TransactionBundle\Entity\Product $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return \TransactionBundle\Entity\Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * Set branch
     *
     * @param \KmBundle\Entity\Branch $branch
     *
     * @return Stock
     */
    public function setBranch(\KmBundle\Entity\Branch $branch)
    {
        $this->branch = $branch;

        return $this;
    }

    /**
     * Get branch
     *
     * @return \KmBundle\Entity\Branch
     */
    public function getBranch()
    {
        return $this->branch;
    }

    /**
     * Set alertLevel
     *
     * @param integer $alertLevel
     *
     * @return Stock
     */
    public function setAlertLevel($alertLevel)
    {
        $this->alertLevel = $alertLevel;

        return $this;
    }

    /**
     * Get alertLevel
     *
     * @return integer
     */
    public function getAlertLevel()
    {
        return $this->alertLevel;
    }
}

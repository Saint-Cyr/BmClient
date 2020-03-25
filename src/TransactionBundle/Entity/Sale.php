<?php

namespace TransactionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Sale
 *
 * @ORM\Table(name="sale")
 * @ORM\Entity(repositoryClass="TransactionBundle\Repository\SaleRepository")
 */
class Sale
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
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime", nullable=true)
     */
    private $createdAt;
    
    /**
     * @var Integer
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;
    
    /**
     * @var float
     *
     * @ORM\Column(name="profit", type="float", nullable=true)
     */
    private $profit;
    
    /**
     * @Assert\Valid
     * @ORM\ManyToOne(targetEntity="TransactionBundle\Entity\STransaction", inversedBy="sales", cascade={"persist"})
     */
    private $stransaction;
    
    /**
     * @ORM\ManyToOne(targetEntity="TransactionBundle\Entity\Product", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $product;
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function __toString() {
        return $this->getProduct()->getName();
    }
    
    public function __construct() {
        
        if(!$this->createdAt){
            $this->setCreatedAt(new \DateTime("now"));
        }
        
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return Sale
     */
    public function setAmount($amount = null)
    {
        if(!$amount){
            $this->amount = $this->getQuantity() * $this->getProduct()->getUnitPrice();
        }else{
            $this->amount = $amount;
        }

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set stransaction
     *
     * @param \TransactionBundle\Entity\STransaction $stransaction
     *
     * @return Sale
     */
    public function setStransaction(\TransactionBundle\Entity\STransaction $stransaction = null)
    {
        $this->stransaction = $stransaction;

        return $this;
    }

    /**
     * Get stransaction
     *
     * @return \TransactionBundle\Entity\STransaction
     */
    public function getStransaction()
    {
        return $this->stransaction;
    }

    /**
     * Set profit
     *
     * @param float $profit
     *
     * @return Sale
     */
    public function setProfit()
    {
        //calculate the profit based on the unit & whole sale price of the product
        $profit = $this->getProduct()->getUnitPrice() - $this->getProduct()->getWholeSalePrice();
        $this->profit = $profit;
        
        return $this;
    }

    /**
     * Get profit
     *
     * @return float
     */
    public function getProfit()
    {
        return (($this->getProduct()->getUnitPrice() - $this->getProduct()->getWholeSalePrice()) * $this->getQuantity());
    }

    /**
     * Set product
     *
     * @param \TransactionBundle\Entity\Product $product
     *
     * @return Sale
     */
    public function setProduct(\TransactionBundle\Entity\Product $product = null)
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
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return Sale
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
        
        $this->setAmount();
        
        return $this;
    }

    /**
     * Get quantity
     *
     * @return integer
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Sale
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
}

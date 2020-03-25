<?php

namespace KmBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Expenditure
 *
 * @ORM\Table(name="expenditure")
 * @ORM\Entity(repositoryClass="KmBundle\Repository\ExpenditureRepository")
 */
class Expenditure
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
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;
    
    /**
     * @ORM\ManyToOne(targetEntity="Branch", inversedBy="expenditures")
     * @ORM\JoinColumn(nullable=false)
     */
    private $branch;

    public function __construct() {
        $this->setCreatedAt(new \DateTime("now"));
    }
    
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return Expenditure
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Expenditure
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
     * Set branch
     *
     * @param \KmBundle\Entity\Branch $branch
     *
     * @return Expenditure
     */
    public function setBranch(\KmBundle\Entity\Branch $branch = null)
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
}

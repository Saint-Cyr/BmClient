<?php

namespace KmBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Branch
 *
 * @ORM\Table(name="branch")
 * @ORM\Entity(repositoryClass="KmBundle\Repository\BranchRepository")
 */
class Branch
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
     * @var int
     * @ORM\Column(name="idSynchrone", type="string", nullable=true, unique=true)
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $idSynchrone;
    
    /**
     * @var float
     *
     * @ORM\Column(name="flySaleAmount", type="float", nullable=true)
     */
    private $flySaleAmount;
    
    /**
     * @var integer
     *
     * @ORM\Column(name="onlineId", type="integer", nullable=true)
     */
    private $onlineId;
    
    /**
     * @var float
     *
     * @ORM\Column(name="flyProfitAmount", type="float", nullable=true)
     */
    private $flyProfitAmount;
    
    /**
     * @var float
     *
     * @ORM\Column(name="flyExpenditureAmount", type="float", nullable=true)
     */
    private $flyExpenditureAmount;
    
    /**
     * @var float
     *
     * @ORM\Column(name="flyBalanceAmount", type="float", nullable=true)
     */
    private $flyBalanceAmount;
    
    /**
     * @ORM\OneToMany(targetEntity="UserBundle\Entity\User", mappedBy="branch")
     */
    private $users;
    
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="datetime")
     */
    private $createdAt;
    
    /**
     * @ORM\OneToMany(targetEntity="TransactionBundle\Entity\STransaction", mappedBy="branch")
     */
    private $stransactions;

    public function __construct() {
        $this->setCreatedAt(new \DateTime("now"));
    }
    
    public function __toString() {
        if(!$this->name){
            return 'New Branch';
        }
        
        return $this->name;
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
     * Set name
     *
     * @param string $name
     *
     * @return Branch
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
     * @return Branch
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
     * Add stransaction
     *
     * @param \TransactionBundle\Entity\STransaction $stransaction
     *
     * @return Branch
     */
    public function addStransaction(\TransactionBundle\Entity\STransaction $stransaction)
    {
        $this->stransactions[] = $stransaction;

        return $this;
    }

    /**
     * Remove stransaction
     *
     * @param \TransactionBundle\Entity\STransaction $stransaction
     */
    public function removeStransaction(\TransactionBundle\Entity\STransaction $stransaction)
    {
        $this->stransactions->removeElement($stransaction);
    }

    /**
     * Get stransactions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStransactions()
    {
        return $this->stransactions;
    }

    /**
     * Add user
     *
     * @param \UserBundle\Entity\User $user
     *
     * @return Branch
     */
    public function addUser(\UserBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \UserBundle\Entity\User $user
     */
    public function removeUser(\UserBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set flySaleAmount
     *
     * @param float $flySaleAmount
     *
     * @return Branch
     */
    public function setFlySaleAmount($flySaleAmount)
    {
        $this->flySaleAmount = $flySaleAmount;

        return $this;
    }

    /**
     * Get flySaleAmount
     *
     * @return float
     */
    public function getFlySaleAmount()
    {
        return $this->flySaleAmount;
    }

    /**
     * Set flyProfitAmount
     *
     * @param float $flyProfitAmount
     *
     * @return Branch
     */
    public function setFlyProfitAmount($flyProfitAmount)
    {
        $this->flyProfitAmount = $flyProfitAmount;

        return $this;
    }

    /**
     * Get flyProfitAmount
     *
     * @return float
     */
    public function getFlyProfitAmount()
    {
        return $this->flyProfitAmount;
    }

    /**
     * Set flyExpenditureAmount
     *
     * @param float $flyExpenditureAmount
     *
     * @return Branch
     */
    public function setFlyExpenditureAmount($flyExpenditureAmount)
    {
        $this->flyExpenditureAmount = $flyExpenditureAmount;

        return $this;
    }

    /**
     * Get flyExpenditureAmount
     *
     * @return float
     */
    public function getFlyExpenditureAmount()
    {
        return $this->flyExpenditureAmount;
    }

    /**
     * Set flyBalanceAmount
     *
     * @param float $flyBalanceAmount
     *
     * @return Branch
     */
    public function setFlyBalanceAmount($flyBalanceAmount)
    {
        $this->flyBalanceAmount = $flyBalanceAmount;

        return $this;
    }

    /**
     * Get flyBalanceAmount
     *
     * @return float
     */
    public function getFlyBalanceAmount()
    {
        return $this->flyBalanceAmount;
    }

    /**
     * Set idSynchrone
     *
     * @param integer $idSynchrone
     *
     * @return Branch
     */
    public function setIdSynchrone($idSynchrone)
    {
        $this->idSynchrone = $idSynchrone;

        return $this;
    }

    /**
     * Get idSynchrone
     *
     * @return integer
     */
    public function getIdSynchrone()
    {
        return $this->idSynchrone;
    }

    /**
     * Set onlineId
     *
     * @param integer $onlineId
     *
     * @return Branch
     */
    public function setOnlineId($onlineId)
    {
        $this->onlineId = $onlineId;

        return $this;
    }

    /**
     * Get onlineId
     *
     * @return integer
     */
    public function getOnlineId()
    {
        return $this->onlineId;
    }
}

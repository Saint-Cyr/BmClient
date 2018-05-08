<?php

namespace KmBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Setting
 *
 * @ORM\Table(name="setting")
 * @ORM\Entity(repositoryClass="KmBundle\Repository\SettingRepository")
 */
class Setting
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="appInstalled", type="boolean")
     */
    private $appInstalled;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    
    public function __construct() {
        $this->setName('bmClient');
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Setting
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
     * Set appInstalled
     *
     * @param boolean $appInstalled
     *
     * @return Setting
     */
    public function setAppInstalled($appInstalled)
    {
        $this->appInstalled = $appInstalled;

        return $this;
    }

    /**
     * Get appInstalled
     *
     * @return bool
     */
    public function getAppInstalled()
    {
        return $this->appInstalled;
    }
}


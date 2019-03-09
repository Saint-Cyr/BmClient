<?php namespace UserBundle\Entity;use Doctrine\ORM\Mapping as ORM;use FOS\UserBundle\Model\User as BaseUser;use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;use Symfony\Component\Validator\Constraints as Assert;use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * User
 *
 * @ORM\Table(name="user")
 * @ORM\Entity(repositoryClass="UserBundle\Repository\UserRepository")
 * @UniqueEntity("username", message="this username has been already used")
 * @UniqueEntity("email", message="this e-mail has been already used")
 * @ORM\HasLifecycleCallbacks
 */
class User extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    
    /**
     * @var string
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;
    
    /**
     * Unmapped property to handle file uploads
     */
    private $file;
    
    /**
     * @var string
     *
     * @ORM\Column(name="phoneNumber", type="string", length=255, nullable=true)
     */
    private $phoneNumber;
    
    /**
     * @var string
     *
     * @ORM\Column(name="image", type="string", length=255, nullable=true)
     */
    private $image;
    
    /**
     * @var string
     *
     * @ORM\Column(name="language", type="string", length=255, nullable=true)
     */
    private $language;
    
    private $type;
    
    /**
     * @ORM\ManyToOne(targetEntity="KmBundle\Entity\Branch", inversedBy="users", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=true)
     */
    private $branch;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Assert\Length(
     *      min = 5,
     *      max = 50,
     *      minMessage = "Your Name must be at least {{ limit }} characters long",
     *      maxMessage = "Your Name cannot be longer than {{ limit }} characters"
     * )
     */
    private $name;
    
    public function getType()
    {
        return $this->type;
    }
    
    public function setType($type)
    {
        $this->type = $type;
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
    * @ORM\PostPersist()
    * @ORM\PostUpdate()
    */
    public function lifecycleFileUpload()
    {
        $this->upload();
    }

    /**
     * @ORM\PreUpdate()
     */
    public function refreshUpdated()
    {
        $this->setUpdated(new \DateTime());
    }
    
    /**
     * @ORM\PreRemove()
     */
    public function removeUPdate()
    {
        //Check whether the file exists first
        if (file_exists(getcwd().'/upload/images/user/'.$this->getImage())){
            //Remove it
            @unlink(getcwd().'/upload/images/user/'.$this->getImage());
        }
        
        return;
    }
    
    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }
        // move takes the target directory and target filename as params
        $this->getFile()->move(getcwd().'/upload/images/user', $this->getId().'.'.$this->getFile()->guessExtension());
        // clean up the file property as you won't need it anymore
        $this->setFile(null);
    }
    
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     *
     * @return User
     */
    public function setImage($image)
    {
        if($this->getFile() !== null){
            $this->image = $this->getFile()->guessExtension();
        }
        
        return $this;
    }

    /**
     * Get image
     *
     * @return string
     */
    public function getImage()
    { 
        if((substr($this->image, -4) == 'jpeg')||(substr($this->image, -3) == 'jpg')||(substr($this->image, -3) == 'png')){
            return $this->getId().'.'.$this->image;
        }else{
            return null;
        }
        
    }
    
    /**
    * Sets file.
    *
    * @param UploadedFile $file
    */
    public function setFile(UploadedFile $file = null)
    {
        $this->file = $file;
    }

    /**
    * Get file.
    *
    * @return UploadedFile
    */
    public function getFile()
    {
        return $this->file;
    }
    
    /**
     * Set phoneNumber
     *
     * @param integer $phoneNumber
     *
     * @return User
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return integer
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }
    
    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return User
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return User
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
        if(!$this->name){
            return $this->username;
        }else{
            return $this->name;
        }
    }

    /**
     * Set branch
     *
     * @param \KmBundle\Entity\Branch $branch
     *
     * @return User
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

    /**
     * Set language
     *
     * @param string $language
     *
     * @return User
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language
     *
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }
}

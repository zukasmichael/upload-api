<?php
/**
 * cloudxxx-api (http://www.cloud.xxx)
 *
 * Copyright (C) 2014 Really Useful Limited.
 * Proprietary code. Usage restrictions apply.
 *
 * @copyright  Copyright (C) 2014 Really Useful Limited
 * @license    Proprietary
 */

namespace Cloud\Model;

use Doctrine\Common\Collections\ArrayCollection;

use Doctrine\ORM\Mapping as ORM;
use Cloud\Doctrine\Annotation as CX;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 */
class Company extends AbstractModel
{
    use Traits\IdTrait;
    use Traits\CreatedAtTrait;
    use Traits\UpdatedAtTrait;

    /**
     * @ORM\Column(type="string")
     * @JMS\Groups({"list.companies", "details.company", "details.session"})
     * @Assert\NotBlank
     */
    protected $title;

    /**
     * @ORM\OneToMany(
     *   targetEntity="User",
     *   mappedBy="company",
     *   cascade={"persist", "remove"}
     * )
     * @JMS\Groups({"details.company"})
     * @JMS\ReadOnly
     */
    protected $users;

    /**
     * @ORM\OneToMany(
     *   targetEntity="Site",
     *   mappedBy="company",
     *   cascade={"persist", "remove"}
     * )
     * @JMS\Groups({"details.company"})
     * @JMS\ReadOnly
     */
    protected $sites;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->sites = new ArrayCollection();
    }

    /**
     * Set the company name
     *
     * @param  string $title
     * @return Company
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the company name
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Add a user
     *
     * @param  User $user
     * @return Company
     */
    public function addUser(User $user)
    {
        $this->users->add($user);
        $user->setCompany($this);

        return $this;
    }

    /**
     * Remove a user
     *
     * @param  User $user
     * @return Company
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);

        return $this;
    }

    /**
     * Get the users
     *
     * @return Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add a site
     *
     * @param  Site $site
     * @return Company
     */
    public function addSite(Site $site)
    {
        $this->sites->add($site);
        $site->setCompany($this);

        return $this;
    }

    /**
     * Remove a site
     *
     * @param  Site $site
     * @return Company
     */
    public function removeSite(Site $site)
    {
        $this->sites->removeElement($site);

        return $this;
    }

    /**
     * Get the sites
     *
     * @return Collection
     */
    public function getSites()
    {
        return $this->sites;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getTitle();
    }
}

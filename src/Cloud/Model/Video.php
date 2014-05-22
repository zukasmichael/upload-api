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

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Exclude;
use JsonSerializable;

use Doctrine\ORM\Mapping as ORM;
use Cloud\Doctrine\Annotation as CX;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Video extends AbstractModel
{
    /*
     * php-resque status codes:
     *
    const STATUS_WAITING = 1;
    const STATUS_RUNNING = 2;
    const STATUS_FAILED = 3;
    const STATUS_COMPLETE = 4;
     */

    const STATUS_DRAFT    = 'draft';
    const STATUS_PENDING  = 'pending';
    const STATUS_WORKING  = 'working';
    const STATUS_COMPLETE = 'complete';

    use Traits\IdTrait;
    use Traits\SlugTrait;
    use Traits\CreatedAtTrait;
    use Traits\UpdatedAtTrait;
    use Traits\CompanyTrait;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Groups({"details.videos"})
     */
    protected $completedAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"list", "list.videos", "details.videos"})
     */
    protected $description;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Groups({"details.videos"})
     */
    protected $filename;

    /**
     * The overall processing status for this video by the worker system. To
     * query success or failure data, look at each individual inbound and
     * outbound and query their status.
     *
     * @see STATUS_DRAFT
     * @see STATUS_PENDING
     * @see STATUS_WORKING
     * @see STATUS_COMPLETE
     *
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"list", "list.videos", "details.videos"})
     */
    protected $filesize;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Groups({"details.videos"})
     */
    protected $filetype;

    /**
     * Inbound files: user upload from browser
     *
     * @ORM\OneToMany(
     *   targetEntity="VideoInbound",
     *   mappedBy="video",
     *   cascade={"persist", "remove"}
     * )
     * @JMS\Groups({"details.videos"})
     */
    protected $inbounds;

    /**
     * @ORM\Column(type="boolean")
     * @JMS\Accessor(getter="isDraft")
     * @JMS\Groups({"list", "list.videos", "details.videos"})
     */
    protected $isDraft = true;

    /**
     * Orientation of video. Example: Straight, solo, gay.
     */
    protected $orientation;

    /**
     * Outbound files: worker publish to tubesite
     *
     * @ORM\OneToMany(
     *   targetEntity="VideoOutbound",
     *   mappedBy="video",
     *   cascade={"persist", "remove"}
     * )
     * @JMS\Groups({"details.videos"})
     */
    protected $outbounds;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @JMS\Groups({"details.videos"})
     */
    protected $publishedAt;

    /**
     * The overall processing status for this video by the worker system. To
     * query success or failure data, look at each individual inbound and
     * outbound and query their status.
     *
     * @see STATUS_DRAFT
     * @see STATUS_PENDING
     * @see STATUS_WORKING
     * @see STATUS_COMPLETE
     *
     * @ORM\Column(type="string")
     * @JMS\Groups({"list", "list.videos", "details.videos"})
     */
    protected $status = self::STATUS_DRAFT;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Groups({"list", "list.videos", "details.videos"})
     */
    protected $title;

    /**
     * @ORM\OneToMany(targetEntity="Stat", mappedBy="stats")
     * @ORM\JoinColumn(name="id", referencedColumnName="id")
     */
    protected $stats;

    /**
     * @ORM\ManyToMany(targetEntity="Tag")
     * @JMS\Groups({"list", "list.videos", "details.videos"})
     */
    protected $tags;

    /**
     * @JMS\Groups({"list", "list.videos", "details.videos"})
     */
    protected $thumbnail;

    /**
     * @ORM\Column(type="integer")
     * @ORM\Version
     */
    protected $version = 1;

    //////////////////////////////////////////////////////////////////////////

    /**
     * Constructor
     */
    public function __construct($user)
    {
        $this->tags = new ArrayCollection();
        $this->inbounds = new ArrayCollection();
        $this->outbounds = new ArrayCollection();
        $this->created_by = $user;
        $this->updated_by = $user;
        $this->setCompany($user->getCompany());
    }

    /**
     * Get the entity revision
     *
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set the creating user
     *
     * @param  User $created_by
     * @return Video
     */
    public function setCreatedBy(User $created_by)
    {
        $this->created_by = $created_by;
        return $this;
    }

    /**
     * Get the creating user
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->created_by;
    }

    /**
     * Get the orientation of the video
     *
     * @return string
     */
    public function getOrientation()
    {
      return $this->orientation;
    }

    /**
     * Set the updating user
     *
     * @param  User $updated_by
     * @return Video
     */
    public function setUpdatedBy(User $updated_by)
    {
        $this->updated_by = $updated_by;
        return $this;
    }

    /**
     * Get the updating user
     *
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updated_by;
    }

    /**
     * Set the parent company
     *
     * @param  Company $company
     * @return Video
     */
    public function setCompany(Company $company)
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Set the parent company
     *
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set the video title
     *
     * @param  string $title
     * @return Video
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the video title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set the video description
     *
     * @param  string $description
     * @return Video
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the video description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add a tag
     *
     * @param  Tag $tag
     * @return Video
     */
    public function addTag(Tag $tag)
    {
        $this->tags->add($tag);
        return $this;
    }

    /**
     * Remove a tag
     *
     * @param  Tag $tag
     * @return Video
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
        return $this;
    }

    /**
     * Get the tags
     *
     * @return Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set the processing status
     *
     * @param  string $status
     * @return Video
     */
    public function setStatus($status)
    {
        if (!in_array($status, [
            self::STATUS_DRAFT,
            self::STATUS_PENDING,
            self::STATUS_WORKING,
            self::STATUS_COMPLETE
        ])) {
            throw new \InvalidArgumentException("Invalid status");
        }

        $this->status = $status;
        $this->isDraft = ($status == self::STATUS_DRAFT);

        return $this;
    }

    /**
     * Get the processing status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Check if the video is a draft and can be edited
     *
     * @return bool
     */
    public function isDraft()
    {
        return $this->isDraft === true;
    }

    /**
     * Get the inbound file transfers for this video
     *
     * @return Collection
     */
    public function getInbounds()
    {
        return $this->inbounds;
    }

    /**
     * Get the outbound file transfers for this video
     *
     * @return Collection
     */
    public function getOutbounds()
    {
        return $this->outbounds;
    }

    /**
     * Set the filename
     *
     * @param  string $filename
     * @return Video
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set the filesize in bytes
     *
     * @param  int $filesize
     * @return Video
     */
    public function setFilesize($filesize)
    {
        $this->filesize = $filesize;
        return $this;
    }

    /**
     * Get the filesize in bytes
     *
     * @return int
     */
    public function getFilesize()
    {
        return $this->filesize;
    }

    /**
     * Set the file mimetype
     *
     * @param  string $filetype
     * @return VideoOutbound
     */
    public function setFiletype($filetype)
    {
        $this->filetype = $filetype;
        return $this;
    }

    /**
     * Get the file mimetype
     *
     * @return string
     */
    public function getFiletype()
    {
        return $this->filetype;
    }

    /**
     * Returns the associated inbounds
     *
     * @return array
     */
    public function getVideoInbounds()
    {
        return $this->inbounds;
    }

    /**
     * Returns the associated outbounds
     *
     * @return array
     */
    public function getVideoOutbounds()
    {
        return $this->outbounds;
    }

    /**
     * Returns the video thumbnail image
     *
     * @return string
     */
    public function getThumbnail()
    {
      $thumbnails = ['tri', 'squ', 'sta', 'env',];
      $thumbnail = $thumbnails[rand(0, count($thumbnails)-1)] . '.png';

      return $thumbnail;
    }

    /**
     * Sets the video thumbnail
     *
     * @param string $thumbnail
     *
     * @return $this
     */
    public function setThumbnail($thumbnail)
    {
      $this->thumbnail = $thumbnail;

      return $this;
    }

    /**
     * @return DateTime
     */
    public function getPublishedAt()
    {
      return $this->publishedAt;
    }

    /**
     * Sets the published date
     *
     * @param  DateTime $date
     *
     * @return Video
     */
    public function setPublishedAt(\DateTime $date)
    {
      $this->publishedAt = $date;

      return $this;
    }

    /**
     * @return DateTime
     */
    public function getCompletedAt()
    {
      return $this->completedAt;
    }

    /**
     * @param DateTime $date the date of completion
     *
     * @return Video
     */
    public function setCompletedAt(\DateTime $date)
    {
      $this->completedAt = $date;
      return $this;
    }

    /**
     * @param string $tags a json encoded array of tags
     *
     * @return Video
     */
    public function addTags($tags)
    {
      foreach ($tags as $tag) {
        $this->tags[] = $tag;
      }

      return $this;
    }

    //////////////////////////////////////////////////////////////////////////

    /**
     * @return array
     */
    protected function getSlugFields()
    {
        return ['id', 'title'];
    }

    /**
     * @return bool
     */
    protected function shouldRegenerateSlugOnUpdate()
    {
        return $this->isDraft();
    }

}

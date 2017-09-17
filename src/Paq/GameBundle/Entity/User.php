<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table
 */
class User extends BaseUser
{
    const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';
    
    const ROLE_ADMIN = 'ROLE_ADMIN';
    
    const ROLE_USER = 'ROLE_USER';
    
    /**
     * This role is given to all users that have been created to allow creating and joining "anonymous" game
     */
    const ROLE_TEMPORAL = 'ROLE_TEMPORAL';

    /**
     * This role is given to users that have access to Weemto Pro version
     */
    const ROLE_PRO_VERSION = 'ROLE_PRO_VERSION';
    
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    protected $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @var string
     */
    private $displayName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $facebookId;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $facebookAccessToken;

    /**
     * WARNING: Use for testing purposes ONLY!
     *
     * @param int $id
     * @deprecated
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param string $name
     */
    public function setDisplayName($name)
    {
        $this->displayName = $name;
    }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return isset($this->displayName) ? $this->displayName : $this->getUsername();
    }

    /**
     * @return bool
     */
    public function hasDisplayName()
    {
        return isset($this->displayName);
    }

    /**
     * Set facebookId
     *
     * @param string $facebookId
     * @return User
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;

        return $this;
    }

    /**
     * Get facebookId
     *
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * Set facebookAccessToken
     *
     * @param string $facebookAccessToken
     * @return User
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;

        return $this;
    }

    /**
     * Get facebookAccessToken
     *
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }

    /**
     * @param bool $hasProVersion
     */
    public function setHasProVersion($hasProVersion)
    {
        if ($hasProVersion) {
            $this->addRole(self::ROLE_PRO_VERSION);
        } else {
            $this->removeRole(self::ROLE_PRO_VERSION);
        }
    }

    /**
     * @return bool
     */
    public function hasProVersion()
    {
        return $this->hasRole(self::ROLE_PRO_VERSION);
    }

}

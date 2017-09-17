<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Paq\GameBundle\Service;

/**
 * Helps in proper co-operation with Android app
 */
class AndroidBridge
{
    /**
     * @var string
     */
    private $secret;

    /**
     * @param string $secret secret shared with Android App
     */
    public function __construct($secret)
    {
        if (!isset($secret) || !is_string($secret)) {
            throw new \InvalidArgumentException('Android Bridge requires a "secret"');
        }

        $this->secret = $secret;
    }

    /**
     * Creates a checksum for given string
     *
     * @param $string
     * @return string
     */
    public function createChecksum($string)
    {
        return md5(sprintf('%s-%s-%s', $this->secret, $string, $this->secret));
    }

    /**
     * @param string $email
     * @return string
     */
    public function getUsernameFromEmail($email)
    {
        $at = stripos($email, '@');
        if (false === $at) {
            throw new \InvalidArgumentException("Email expected. Got '$email'");
        }

        return substr($email, 0, $at);
    }

    /**
     * @param string $email
     * @return string
     */
    public function getDefaultPasswordForEmail($email)
    {
        return substr(sha1(sprintf('DefaultPasswordFor:%s:%s', $email, $this->secret)), 0, 6);
    }
}
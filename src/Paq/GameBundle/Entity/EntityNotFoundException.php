<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Paq\GameBundle\Entity;

class EntityNotFoundException extends \Doctrine\ORM\EntityNotFoundException
{
    /**
     * @param $message
     */
    public function __construct($message)
    {
        parent::__construct();
        $this->message = $message;
    }
}
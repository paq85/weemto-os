<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */
namespace Paq\GameBundle\Model;

use Paq\GameBundle\Entity\UserScoreLog;

interface UserScoreLogListenerInterface
{
    /**
     * @param UserScoreLog[] $logs
     */
    public function onNewUserScoreLogs($logs);
}
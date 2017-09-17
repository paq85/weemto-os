<?php
/**
 * (c) Damian Sromek <damian.sromek@gmail.com>
 *
 * Do not redistribute.
 */

namespace Paq\GameBundle\Service;


use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

class Platform
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var string
     */
    private $platformPath;

    /**
     * @var string cached platform version
     */
    private $version;

    /**
     * @param string $platformPath path to folder with "version.txt" file
     */
    public function __construct(Filesystem $filesystem, $platformPath)
    {
        $this->filesystem = $filesystem;
        $this->platformPath = realpath($platformPath . '/../');
        if (false === $this->platformPath) {
            throw new \LogicException('Platform Path does not exist at ' . $platformPath . '/../');
        }
    }

    /**
     * @return string version of PaqGame platform
     */
    public function getVersion()
    {
        if (null !== $this->version) {
            return $this->version;
        }

        $versionTxtPath = $this->platformPath . '/version.txt';

        if (! $this->filesystem->exists($versionTxtPath)) {
            throw new \RuntimeException('version.txt file not found at ' . $versionTxtPath);
        }

        $version = file_get_contents($versionTxtPath);

        if (false === $version) {
            throw new \RuntimeException('Could not read version.txt file at ' . $versionTxtPath);
        }

        $this->version = $version . '+' . $this->getGitRevision();

        return $this->version;
    }

    /**
     * @return string
     */
    public function getGitRevision()
    {
        $process = new Process('git log --pretty=format:\'%h@%ct\' -n 1');
        $process->setWorkingDirectory($this->platformPath);
        $process->run();

        if ($process->isSuccessful()) {
            $output = explode('@', $process->getOutput());
            if (count($output) < 2) {
                return '?';
            }

            return date('YmdHi', $output[1]) .' (' . $output[0] . ')';
        } else {
            throw new \RuntimeException($process->getErrorOutput());
        }
    }
}
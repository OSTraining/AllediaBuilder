<?php
/**
 * @package   AllediaBuilder
 * @contact   www.ostraining.com, support@ostraining.com
 * @copyright 2014 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

require_once 'library/FileSystemTask.php';
require_once 'library/Spyc.php';

class UnlinkSymlinksTask extends FileSystemTask
{
    /**
     * File with the symlinks map
     *
     * @var string
     */
    protected $mapFile;

    /**
     * The symlinks destination base path
     *
     * @var string
     */
    protected $basepath;

    /**
     * The phing directory
     *
     * @var string
     */
    protected $phingdir;

    /**
     * The setter for the attribute "mapFile". It should point to
     * a .yml file
     *
     * @param string $path The path for the symlinks map file
     * @return void
     */
    public function setMapFile($path)
    {
        if (empty($path) || ! file_exists(realpath($path))) {
            throw new Exception("Invalid symlink map file", 1);
        }

        $this->mapFile = $path;
    }

    /**
     * The setter for the attribute phingDir
     *
     * @param string $dir The path for phing dir
     * @return void
     */
    public function setPhingdir($dir)
    {
        $this->phingdir = $dir;
    }

    /**
     * The setter for the attribute basePath
     *
     * @param string $path The base path of the project
     * @return void
     */
    public function setBasePath($path)
    {
        $this->basepath = $path;
    }

    /**
     * The init method
     *
     * @return void
     */
    public function init()
    {
    }

    /**
     * The method that runs the task
     *
     * @return void
     */
    public function main()
    {
        $map = Spyc::YAMLLoad($this->mapFile);

        if (!isset($map['symlinks'])) {
            throw new Exception("Invalid symlinks map file", 1);
        }

        // Do we need to remove created directories?
        if (isset($map['mkdir'])) {
            foreach ($map['mkdir'] as $dir) {
                $path = realpath($this->basepath . '/' . $dir);

                if (file_exists($path)) {
                    $this->remove($path);
                }

                $path = $this->basepath . '/' . $dir;
                $this->log('Directory unlinked: ' . $path);
            }
        }

        foreach ($map['symlinks'] as $item) {
            $source      = array_keys($item)[0];
            $destination = array_values($item)[0];

            // Normalise paths
            $sourcePath      = realpath($this->phingdir . '/../' . $source);
            $destinationPath = rtrim($this->basepath, '/') . '/' . $destination;

            if (!file_exists($sourcePath)) {
                throw new Exception("Symlink target not found: " . $this->phingdir . '/../' . $source, 1);
            }

            // Check if the destination exists and remove it
            if (file_exists($destinationPath)) {
                $destinationPath = rtrim($destinationPath, '/');

                $this->remove($destinationPath);
            }

            $this->log('Symlink unlinked: ' . $destinationPath);
        }
    }
}

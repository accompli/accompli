<?php

namespace Accompli\Test;

/**
 * WorkspaceUtility.
 *
 * @author Niels Nijens <nijens.niels@gmail.com>
 */
class WorkspaceUtility
{
    /**
     * The path to the workspace.
     *
     * @var string
     */
    private $workspacePath;

    /**
     * Constructs a new WorkspaceUtility.
     *
     * @param string|null $workspacePath
     */
    public function __construct($workspacePath = null)
    {
        if ($workspacePath === null) {
            $workspacePath = __DIR__.'/../test-workspace/';
        }

        $this->workspacePath = $workspacePath;
    }

    /**
     * Returns the path to the workspace.
     *
     * @return string
     */
    public function getWorkspacePath()
    {
        return $this->workspacePath;
    }

    /**
     * Creates the workspace directory.
     */
    public function create()
    {
        if (is_dir($this->workspacePath) === false) {
            mkdir($this->workspacePath);
        }
    }

    /**
     * Creates a file with the workspace.
     *
     * @param string $filename
     * @param string $data
     */
    public function createFile($filename, $data = '')
    {
        file_put_contents($this->workspacePath.$filename, $data);
    }

    /**
     * Creates a directory with the workspace.
     *
     * @param string $directory
     * @param bool   $recursive
     */
    public function createDirectory($directory, $recursive = false)
    {
        mkdir($this->workspacePath.$directory, 0770, $recursive);
    }

    /**
     * Removes the workspace.
     */
    public function remove()
    {
        $this->removeItem($this->workspacePath);
    }

    /**
     * Removes an item by $path.
     *
     * @param string $path
     */
    private function removeItem($path)
    {
        if (is_dir($path)) {
            $directoryItems = array_diff(scandir($path), array('.', '..'));
            foreach ($directoryItems as $directoryItem) {
                $this->removeItem($path.'/'.$directoryItem);
            }

            rmdir($path);

            return;
        }

        unlink($path);
    }
}

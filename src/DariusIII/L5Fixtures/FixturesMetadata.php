<?php namespace DariusIII\L5Fixtures;

use League\Flysystem\FileAttributes;
use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;

class FixturesMetadata
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $fixtures;

    /**
     * FixturesMetadata constructor.
     *
     * @param string $path The path where the fixtures are stored.
     * @param bool $autoload If the fixtures metadata is to be automatically loaded.
     */
    public function __construct($path, $autoload = true)
    {
        $this->path = $path;

        if ($autoload) {
            $this->load();
        }
    }

    /**
     * Load the fixtures from the informed path.
     */
    public function load()
    {
        $this->fixtures = [];
        $files = $this->getFilesystem()->listContents('.');

        foreach ($files as $file) {
            if ($file instanceof FileAttributes) {
                $fixture = new \stdClass();
                $fixture->table  = (string) pathinfo($file->path(), PATHINFO_FILENAME);
                $fixture->format = (string) pathinfo($file->path(), PATHINFO_EXTENSION);
                $fixture->path   = $file->path();

                $this->fixtures[$fixture->table] = $fixture;
            }
        }
    }

    /**
     * @return array
     */
    public function getFixtures()
    {
        return $this->fixtures;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return Filesystem
     */
    public function getFilesystem()
    {
        if ($this->filesystem === null) {
            $this->filesystem = new Filesystem(new LocalFilesystemAdapter($this->path));
        }

        return $this->filesystem;
    }

}

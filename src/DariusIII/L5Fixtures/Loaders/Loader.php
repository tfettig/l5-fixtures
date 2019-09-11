<?php namespace DariusIII\L5Fixtures\Loaders;

use DariusIII\L5Fixtures\FixturesMetadata;

interface Loader
{
    /**
     * Initialize the loader.
     *
     * @param FixturesMetadata $metadata
     * @return void
     */
    public function initialize(FixturesMetadata $metadata);

    /**
     * Load data from file path and return an array with parsed data.
     *
     * @param  string $path
     * @return array
     */
    public function load($path);
}
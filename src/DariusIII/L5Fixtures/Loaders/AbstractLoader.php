<?php namespace DariusIII\L5Fixtures\Loaders;

use DariusIII\L5Fixtures\FixturesMetadata;

abstract class AbstractLoader implements Loader
{
    /**
     * @var FixturesMetadata
     */
    protected $metadata;

    public function initialize(FixturesMetadata $metadata)
    {
        $this->metadata = $metadata;
    }
}
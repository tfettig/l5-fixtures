<?php namespace DariusIII\L5Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

use DariusIII\L5Fixtures\Exceptions\DirectoryNotFoundException;
use DariusIII\L5Fixtures\Exceptions\InvalidDataSchemaException;
use DariusIII\L5Fixtures\Exceptions\NotDirectoryException;
use DariusIII\L5Fixtures\Loaders\LoaderFactory;

/**
 * Class Fixtures
 * @package DariusIII\L5Fixtures
 */
class Fixtures
{
    /**
     * @var array
     */
    protected $config;

    /**
     * @var FixturesMetadata
     */
    protected $metadata;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @param null $location
     * @throws DirectoryNotFoundException
     * @throws NotDirectoryException
     */
    public function setUp($location = null)
    {
        if ($location === null) {
            $location = Arr::get($this->config, 'location');
        }

        if (!is_dir($location) || !is_readable($location)) {
            throw new NotDirectoryException($location);
        }

        if (!file_exists($location)) {
            throw new DirectoryNotFoundException($location);
        }

        $this->metadata = new FixturesMetadata($location);
    }

    /**
     * @param null $fixtures
     */
    public function up($fixtures = null)
    {
        $this->loadFixtures($fixtures);
    }

    /**
     * @param null $fixtures
     */
    public function down($fixtures = null)
    {
        $this->unloadFixtures($fixtures);
    }

    /**
     * @param null $allowed
     */
    protected function unloadFixtures($allowed = null)
    {
        $fixtures = $this->getFixtures($allowed);

        Model::unguard();
        $this->setForeignKeyChecks(false);

        $command = $this->truncateCommand();

        foreach ($fixtures as $fixture)
        {
            DB::table($fixture->table)->$command();
        }

        $this->setForeignKeyChecks(true);
    }

    /**
     * @param null $allowed
     * @throws InvalidDataSchemaException
     */
    protected function loadFixtures($allowed = null)
    {
        $fixtures = $this->getFixtures($allowed);

        Model::unguard();
        $this->setForeignKeyChecks(false);

        foreach ($fixtures as $fixture)
        {
            $this->loadFixture($fixture);
        }

        $this->setForeignKeyChecks(true);
    }

    /**
     * @param $fixture
     * @throws Exceptions\UnsupportedFormatException
     * @throws InvalidDataSchemaException
     */
    protected function loadFixture($fixture)
    {
        $rows = LoaderFactory::create($fixture->format, $this->metadata)->load($fixture->path);

        if (!is_array($rows)) {
            throw new InvalidDataSchemaException($fixture);
        }

        $columnCount  = count($rows[0]);
        $chunkSize    = Arr::get($this->config, 'chunk_size', 500);
        $rowsPerChunk = (integer) ($chunkSize / $columnCount);

        // Convert the string "null" into null
        array_walk_recursive($rows, function(&$item, $key) {
            if (!is_array($item) && strcasecmp($item, "null") === 0) {
                $item = null;
            }
        });

        // Insert the chunks in the database
        foreach (array_chunk($rows, $rowsPerChunk) as $chunk) {
            DB::table($fixture->table)->insert($chunk);
        }
    }

    /**
     * @param null $allowed
     * @return array
     * @throws DirectoryNotFoundException
     * @throws NotDirectoryException
     */
    public function getFixtures($allowed = null)
    {
        if ($this->metadata == null) {
            $this->setUp();
        }

        if ($allowed === null) {
            $fixtures = $this->metadata->getFixtures();
        } else {
            if (!is_array($allowed)) {
                $allowed = [$allowed];
            }
            $fixtures = array_intersect_key($this->metadata->getFixtures(), array_flip($allowed));
        }

        return $fixtures;
    }

    /**
     * @param bool $enable
     */
    protected function setForeignKeyChecks ($enable = false)
    {
        switch(DB::getDriverName()) {
            case 'mysql':
                $status = $enable ? 1 : 0;
                DB::statement("SET FOREIGN_KEY_CHECKS=$status;");
                break;

            case 'sqlite':
                $status = $enable ? 'ON' : 'OFF';
                DB::statement("PRAGMA foreign_keys = $status");
                break;

            case 'pgsql':
                $status = $enable ? 'DEFAULT' : 'replica';
                DB::statement("SET session_replication_role = $status");
                break;
        }
    }

    /**
     * @return string
     */
    protected function truncateCommand ()
    {
        $i = DB::getDriverName();
        if ($i === 'pgsql') {
            return 'delete';
        } else {
            return 'truncate';
        }
    }
}

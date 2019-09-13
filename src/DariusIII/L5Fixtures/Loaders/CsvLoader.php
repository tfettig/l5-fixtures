<?php namespace DariusIII\L5Fixtures\Loaders;

use League\Csv\Reader;
use function League\Csv\delimiter_detect;

class CsvLoader extends AbstractLoader
{
    /**
     * @param string $path
     * @return array
     * @throws \League\Csv\Exception
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function load($path): array
    {
        $data = $this->metadata->getFilesystem()->read($path);
        $this->getReader($data)->setHeaderOffset(0);
        return iterator_to_array($this->getReader($data)->getRecords(), false);
    }

    /**
     * @param $data
     * @return Reader
     * @throws \League\Csv\Exception
     */
    protected function getReader($data): Reader
    {
        $csv = Reader::createFromString($data);
        $delimiters = delimiter_detect($csv, [' ', '|', ',', ';'], 10);

        if (count($delimiters) > 0) {
            $csv->setDelimiter(array_keys($delimiters)[0]);
        }

        return $csv;
    }
}
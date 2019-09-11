<?php namespace DariusIII\L5Fixtures\Loaders;

use League\Csv\Reader;

class CsvLoader extends AbstractLoader
{
    /**
     * @param string $path
     * @return array
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function load($path): array
    {
        $data = $this->metadata->getFilesystem()->read($path);
        return iterator_to_array($this->getReader($data)->fetchAssoc(), false);
    }

    /**
     * @param string $data
     * @return Reader
     */
    protected function getReader($data): Reader
    {
        $csv = Reader::createFromString($data);
        $delimiters = $csv->fetchDelimitersOccurrence([' ', '|', ',', ';'], 10);

        if (count($delimiters) > 0) {
            $csv->setDelimiter(array_keys($delimiters)[0]);
        }

        return $csv;
    }
}
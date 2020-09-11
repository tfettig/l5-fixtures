<?php namespace DariusIII\L5Fixtures\Loaders;


class PhpLoader extends AbstractLoader
{

    /**
     * @param string $path
     * @return array|mixed
     */
    public function load($path)
    {
        $path = $this->metadata->getPath() . '/' . $path;
        $data = include $path;
        return $data;
    }
}
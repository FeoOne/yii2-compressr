<?php

namespace nabu\yii2\compressr\compress;

use yii\base\BaseObject;

/**
 * Class CssCompressor
 * @package nabu\yii2\compressr\resource
 */
class CssCompressor extends BaseObject
{
    /**
     * @var string
     */
    public $dirName = 'css';

    /**
     * @var bool
     */
    public $enableCache;

    /**
     * @param array $files
     * @return array
     */
    public function compressFiles(array $files) : array
    {
        return (new CssFilesCompressor([
            'dirName' => $this->dirName,
        ]))->compress($files);
    }
}

<?php

namespace nabu\yii2\compressr\compress;

use Yii;

use JShrink\Minifier;

use yii\base\Exception;
use yii\helpers\FileHelper;

/**
 * Class JsFilesCompressor
 * @package nabu\yii2\compressr\resource
 */
class JsFilesCompressor extends BaseFilesCompressor
{
    /**
     * @var string
     */
    public $dirName;
    /**
     * @var bool
     */
    public $flaggedComments;

    /**
     * @param array $files
     * @return array
     */
    public function compress(array $files) : array
    {
        $internalFiles = [];
        $externalFiles = [];

        $fileName = '';
        $assetsDirPath = '';
        $assetsFilePath = '';

        $this->processFiles($files, $internalFiles, $externalFiles);
        $this->makeAssetsPaths($internalFiles, 'js', $this->dirName, $fileName, $assetsDirPath, $assetsFilePath);

        if (file_exists($assetsFilePath)) {
            return $this->makeResultFiles($this->dirName,
                $fileName,
                $assetsFilePath,
                $externalFiles,
                ['yii\helpers\Html', 'jsFile']);
        }

        if (!$this->bakeAndWriteContent($internalFiles, $assetsDirPath, $assetsFilePath)) {
            return $files;
        }

        return $this->makeResultFiles($this->dirName,
            $fileName,
            $assetsFilePath,
            $externalFiles,
            ['yii\helpers\Html', 'jsFile']);
    }

    /**
     * @param array $internalFiles
     * @param string $assetsDirPath
     * @param string $assetsFilePath
     * @return bool
     */
    private function bakeAndWriteContent(array $internalFiles, string $assetsDirPath, string $assetsFilePath) : bool
    {
        if (empty($internalFiles)) {
            return true;
        }

        $content = [];
        foreach ($internalFiles as $relativePath) {
            $absolutePath = Yii::getAlias('@webroot') . $relativePath;
            $content[] = trim(file_get_contents($absolutePath));
        }

        $content = implode(";\n", $content);

        try {
            $content = Minifier::minify($content, ['flaggedComments' => $this->flaggedComments]);
        }
        catch (\Exception $e) {
            Yii::error("Can't minify javascript: {$e->getMessage()}", self::class);
            Yii::error($e->getMessage(), self::class);
            return false;
        }

        if (!$this->createAssetsDirectoryIfNeeded($assetsDirPath)) {
            return false;
        }

        $result = file_put_contents($assetsFilePath, $content);
        if ($result === false) {
            Yii::error("Can't write content to '{$assetsFilePath}'", self::class);
            return false;
        }

        return true;
    }
}

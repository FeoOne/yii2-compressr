<?php

namespace nabu\yii2\compressr\compress;

use Minify_CSSmin;

use Yii;

/**
 * Class CssFilesCompressor
 * @package nabu\yii2\compressr\compress
 */
class CssFilesCompressor extends BaseFilesCompressor
{
    /**
     * @var string
     */
    public $dirName;

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
        $this->makeAssetsPaths($internalFiles, 'css', $this->dirName, $fileName, $assetsDirPath, $assetsFilePath);

        if (file_exists($assetsFilePath)) {
            return $this->makeResultFiles($this->dirName,
                $fileName,
                $assetsFilePath,
                $externalFiles,
                ['yii\helpers\Html', 'cssFile']);
        }

        if (!$this->bakeAndWriteContent($internalFiles, $assetsDirPath, $assetsFilePath)) {
            return $files;
        }

        return $this->makeResultFiles($this->dirName,
            $fileName,
            $assetsFilePath,
            $externalFiles,
            ['yii\helpers\Html', 'cssFile']);
    }

    /**
     * @param array $internalFiles
     * @param string $assetsDirPath
     * @param string $assetsFilePath
     * @return bool
     */
    private function bakeAndWriteContent(array $internalFiles, string $assetsDirPath, string $assetsFilePath) : bool
    {
        $content = [];
        foreach ($internalFiles as $relativePath) {
            $absolutePath = Yii::getAlias('@webroot') . $relativePath;
            $content[] = trim(file_get_contents($absolutePath));
        }

        $content = implode("\n", $content);

        $content = Minify_CSSmin::minify($content, [
            'compress'         => true,
            'removeCharsets'   => true,
            'preserveComments' => true,
        ]);

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

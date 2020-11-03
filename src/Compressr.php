<?php

namespace nabu\yii2\compressr;

use Yii;

use yii\web\View;
use yii\web\Response;
use yii\base\Event;
use yii\base\Component;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;

use nabu\yii2\compressr\js\JsCompressor;
use nabu\yii2\compressr\css\CssCompressor;
use nabu\yii2\compressr\html\HtmlCompressStrategy;
use nabu\yii2\compressr\html\TylerHtmlCompressor;
use nabu\yii2\compressr\html\MrclayHtmlCompressor;

/**
 * Class Compressr
 * @package nabu\yii2\compressr
 *
 * todo: implement 'combineJsParts' and 'combineJsFiles'. now it's combined by default
 * todo: implement 'combineCssParts' and 'combineCssFiles'. now it's combined by default
 */
class Compressr extends Component implements BootstrapInterface
{
    /**
     * @var bool
     */
    public $enabled = true;

    /**
     * @var bool
     */
    public $compressJs = false;
    /**
     * @var bool
     */
    public $jsCutComments = true;
    /**
     * @var bool
     */
    public $jsCacheInlineParts = false;

    /**
     * @var bool
     */
    public $compressCss = false;
    /**
     * @var bool
     */
    public $cssCacheInlineParts = false;

    /**
     * @var bool
     */
    public $compressHtml = false;
    /**
     * @var int
     */
    public $htmlCompressStrategy = HtmlCompressStrategy::MRCLAY;

    /**
     * @param Application $app
     */
    public function bootstrap($app)
    {
        if (!($app instanceof \yii\web\Application) || !$this->enabled) {
            return;
        }

        if ($this->compressJs || $this->compressCss) {
            $app->view->on(View::EVENT_END_PAGE, function (Event $event) use ($app) {
                $this->onEndPage($app, $event);
            });
        }

        if ($this->compressHtml) {
            $app->response->on(Response::EVENT_BEFORE_SEND, function (Event $event) use ($app) {
                $this->onBeforeSend($app, $event);
            });
        }
    }

    /**
     * @param Application $app
     * @param Event $event
     * @throws InvalidConfigException
     */
    private function onEndPage(Application $app, Event $event) : void
    {
        /** @var View $view */
        $view = $event->sender;

        if ($view instanceof View
            && $app->getResponse()->format === Response::FORMAT_HTML
            && !$app->getRequest()->isAjax
            && !$app->getRequest()->isPjax) {
            if ($this->compressJs) {
                $this->compressJavascript($view);
            }
            if ($this->compressCss) {
                $this->compressCascadingStyleSheets($view);
            }
        }
    }

    /**
     * @param Application $app
     * @param Event $event
     */
    private function onBeforeSend(Application $app, Event $event) : void
    {
        /** @var Response $response */
        $response = $event->sender;

        if ($app->getResponse()->format === Response::FORMAT_HTML
            && !$app->getRequest()->isAjax
            && !$app->getRequest()->isPjax) {
            $response->data = $this->compressHtml($response->data);
        }
    }

    /**
     * @param View $view
     * @throws InvalidConfigException
     */
    private function compressJavascript(View $view) : void
    {
        $jsCompressor = new JsCompressor([
            'cacheInlineParts' => $this->jsCacheInlineParts,
            'flaggedComments' => !$this->jsCutComments,
        ]);

        if (!empty($view->js)) {
            Yii::beginProfile('Compressing inline javascript.', self::class);
            foreach ($view->js as $pos => $parts) {
                if (empty($parts)) {
                    continue;
                }
                $view->js[$pos] = $jsCompressor->compressParts($parts);
            }
            Yii::endProfile('Compressing inline javascript.', self::class);
        }

        if (!empty($view->jsFiles)) {
            Yii::beginProfile('Compressing javascript files.', self::class);
            foreach ($view->jsFiles as $pos => $files) {
                if (empty($files)) {
                    continue;
                }
                $view->jsFiles[$pos] = $jsCompressor->compressFiles($files);
            }
            Yii::endProfile('Compressing javascript files.', self::class);
        }
    }

    /**
     * @param View $view
     * @throws InvalidConfigException
     */
    private function compressCascadingStyleSheets(View $view) : void
    {
        $cssCompressor = new CssCompressor([
            'cacheInlineParts' => $this->cssCacheInlineParts,
        ]);

        if (!empty($view->css)) {
            Yii::beginProfile('Compressing inline css.', self::class);
            $view->css = $cssCompressor->compressParts($view->css);
            Yii::endProfile('Compressing inline css.', self::class);
        }

        if (!empty($view->cssFiles)) {
            Yii::beginProfile('Compressing css files.', self::class);
            $view->cssFiles = $cssCompressor->compressFiles($view->cssFiles);
            Yii::endProfile('Compressing css files.', self::class);
        }
    }

    /**
     * @param string|null $content
     * @return string
     */
    private function compressHtml(?string $content) : ?string
    {
        Yii::beginProfile('Compressing html.', self::class);
        switch ($this->htmlCompressStrategy) {
            case HtmlCompressStrategy::MRCLAY: {
                $result = MrclayHtmlCompressor::compress($content);
                break;
            }
            case HtmlCompressStrategy::TYLER: {
                $result = TylerHtmlCompressor::compress($content);
                break;
            }
            default: {
                $result = $content;
            }
        }
        Yii::endProfile('Compressing html.', self::class);

        return $result;
    }
}

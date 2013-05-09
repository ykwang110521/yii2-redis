<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\widgets;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\CaptchaAction;

/**
 * Captcha renders a CAPTCHA image element.
 *
 * Captcha is used together with [[CaptchaAction]] provide [CAPTCHA](http://en.wikipedia.org/wiki/Captcha)
 * - a way of preventing Website spamming.
 *
 * The image element rendered by Captcha will display a CAPTCHA image generated by
 * an action whose route is specified by [[captchaAction]]. This action must be an instance of [[CaptchaAction]].
 *
 * When the user clicks on the CAPTCHA image, it will cause the CAPTCHA image
 * to be refreshed with a new CAPTCHA.
 *
 * You may use [[\yii\validators\CaptchaValidator]] to validate the user input matches
 * the current CAPTCHA verification code.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Captcha extends Widget
{
	/**
	 * @var string the route of the action that generates the CAPTCHA images.
	 * The action represented by this route must be an action of [[CaptchaAction]].
	 */
	public $captchaAction = 'site/captcha';
	/**
	 * @var array HTML attributes to be applied to the rendered image element.
	 */
	public $options = array();


	/**
	 * Renders the widget.
	 */
	public function run()
	{
		$this->checkRequirements();

		if (!isset($this->options['id'])) {
			$this->options['id'] = $this->getId();
		}
		$id = $this->options['id'];
		$options = Json::encode($this->getClientOptions());
		$this->view->registerAssetBundle('yii/captcha');
		$this->view->registerJs("jQuery('#$id').yiiCaptcha($options);");
		$url = Yii::$app->getUrlManager()->createUrl($this->captchaAction, array('v' => uniqid()));
		echo Html::img($url, $this->options);
	}

	/**
	 * Returns the options for the captcha JS widget.
	 * @return array the options
	 */
	protected function getClientOptions()
	{
		$options = array(
			'refreshUrl' => Html::url(array($this->captchaAction, CaptchaAction::REFRESH_GET_VAR => 1)),
			'hashKey' => "yiiCaptcha/{$this->captchaAction}",
		);
		return $options;
	}

	/**
	 * Checks if there is graphic extension available to generate CAPTCHA images.
	 * This method will check the existence of ImageMagick and GD extensions.
	 * @return string the name of the graphic extension, either "imagick" or "gd".
	 * @throws InvalidConfigException if neither ImageMagick nor GD is installed.
	 */
	public static function checkRequirements()
	{
		if (extension_loaded('imagick')) {
			$imagick = new \Imagick();
			$imagickFormats = $imagick->queryFormats('PNG');
			if (in_array('PNG', $imagickFormats)) {
				return 'imagick';
			}
		}
		if (extension_loaded('gd')) {
			$gdInfo = gd_info();
			if (!empty($gdInfo['FreeType Support'])) {
				return 'gd';
			}
		}
		throw new InvalidConfigException('GD with FreeType or ImageMagick PHP extensions are required.');
	}
}
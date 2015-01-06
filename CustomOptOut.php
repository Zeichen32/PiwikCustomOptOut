<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CustomOptOut
 */

namespace Piwik\Plugins\CustomOptOut;

use Piwik\Common;
use Piwik\Db;
use Piwik\Menu\MenuAdmin;
use Piwik\Piwik;
use Piwik\Translate;

/**
 * @package CustomOptOut
 */
class CustomOptOut extends \Piwik\Plugin {

	/**
	 * @see Piwik\Plugin::getListHooksRegistered
	 */
	public function getListHooksRegistered() {

		return array(
		    'Menu.Admin.addItems'               => 'addMenuItems',
		    'AssetManager.getJavaScriptFiles'   => 'getJsFiles',
		    'AssetManager.getStylesheetFiles'   => 'getStylesheetFiles',
		);

	}

	public function postLoad()
	{
		if(
			isset($GLOBALS['Piwik_translations'], $GLOBALS['Piwik_translations']['CustomOptOut'])
			&& is_array($GLOBALS['Piwik_translations']['CustomOptOut'])
		) {
			$currentLanguage = Translate::getLanguageLoaded();
			$fallbackLanguage = Translate::getLanguageDefault();

			$translations = array();

			if(is_file(__DIR__ . '/lang/sites/' . $currentLanguage . '.json')) {
				$translations = json_decode(file_get_contents(__DIR__ . '/lang/sites/' . $currentLanguage . '.json'), true);
			} elseif(is_file(__DIR__ . '/lang/sites/' . $fallbackLanguage . '.json')) {
				$translations = json_decode(file_get_contents(__DIR__ . '/lang/sites/' . $fallbackLanguage . '.json'), true);
			}

			$GLOBALS['Piwik_CustomOptOut_Translations'] = $translations;
		} else {
			$GLOBALS['Piwik_CustomOptOut_Translations'] = array();
		}

		return parent::postLoad();
	}

	private static function getSiteTranslations($siteId = null) {

		if(!isset($GLOBALS['Piwik_CustomOptOut_Translations'])) {
			return self::getDefaultSiteTranslations();
		}

		$isGlobalAvailable = array_key_exists('Global', $GLOBALS['Piwik_CustomOptOut_Translations']);

		$translation = self::getDefaultSiteTranslations();
		if(null !== $siteId && array_key_exists((string) $siteId, $GLOBALS['Piwik_CustomOptOut_Translations'])) {

			if($isGlobalAvailable) {
				$translation = array('CustomOptOut' =>
					array_merge($translation['CustomOptOut'], $GLOBALS['Piwik_CustomOptOut_Translations']['Global'])
				);
			}

			$translation = array('CustomOptOut' =>
				array_merge($translation['CustomOptOut'], $GLOBALS['Piwik_CustomOptOut_Translations'][$siteId])
			);
		} elseif($isGlobalAvailable) {
			$translation = array('CustomOptOut' =>
				array_merge($translation['CustomOptOut'], $GLOBALS['Piwik_CustomOptOut_Translations']['Global'])
			);
		}

		return $translation;
	}

	private static function getDefaultSiteTranslations() {
		return array('CustomOptOut' => array(
			'OptOutComplete' 	=> Piwik::translate('CoreAdminHome_OptOutComplete'),
			'OptOutCompleteBis' => Piwik::translate('CoreAdminHome_OptOutCompleteBis'),
			'YouAreOptedIn' 	=> Piwik::translate('CoreAdminHome_YouAreOptedIn'),
			'YouAreOptedOut' 	=> Piwik::translate('CoreAdminHome_YouAreOptedOut'),
			'YouMayOptOut' 		=> Piwik::translate('CoreAdminHome_YouMayOptOut'),
			'YouMayOptOutBis' 	=> Piwik::translate('CoreAdminHome_YouMayOptOutBis'),
			'ClickHereToOptIn' 	=> Piwik::translate('CoreAdminHome_ClickHereToOptIn'),
			'ClickHereToOptOut' => Piwik::translate('CoreAdminHome_ClickHereToOptOut'),
		));
	}

	public static function changeSiteTranslations($siteId = null) {

		if(
			!isset($GLOBALS['Piwik_CustomOptOut_Translations'])
			|| !is_array($GLOBALS['Piwik_CustomOptOut_Translations'])
			|| count($GLOBALS['Piwik_CustomOptOut_Translations']) < 1
		) {
			$translation = self::getDefaultSiteTranslations();
		} else {
			$translation = self::getSiteTranslations($siteId);
		}

		Translate::mergeTranslationArray($translation);
	}


	/**
	 * @param $jsFiles
	 */
	public function getJsFiles(&$jsFiles) {

		// CodeMirror
		$jsFiles[] = "plugins/CustomOptOut/javascripts/codemirror/codemirror.js";
		$jsFiles[] = "plugins/CustomOptOut/javascripts/codemirror/mode/css/css.js";
		$jsFiles[] = "plugins/CustomOptOut/javascripts/codemirror/addon/hint/show-hint.js";
		$jsFiles[] = "plugins/CustomOptOut/javascripts/codemirror/addon/hint/css-hint.js";
		$jsFiles[] = "plugins/CustomOptOut/javascripts/codemirror/addon/lint/lint.js";
		$jsFiles[] = "plugins/CustomOptOut/javascripts/codemirror/addon/lint/css-lint.js";

		// CSS Lint for CodeMirror
		$jsFiles[] = "plugins/CustomOptOut/javascripts/csslint/csslint.js";

		// Plugin
		$jsFiles[] = "plugins/CustomOptOut/javascripts/plugin.js";

	}

	/**
	 * @param $stylesheets
	 */
	public function getStylesheetFiles(&$stylesheets) {

		// CodeMirror CSS
		$stylesheets[] = "plugins/CustomOptOut/stylesheets/codemirror/codemirror.css";
		$stylesheets[] = "plugins/CustomOptOut/stylesheets/codemirror/theme/blackboard.css";
		$stylesheets[] = "plugins/CustomOptOut/stylesheets/codemirror/lint.css";
		$stylesheets[] = "plugins/CustomOptOut/stylesheets/codemirror/show-hint.css";

	}

	/**
	 * Add Menu Item to the Sidebar
	 */
	public function addMenuItems() {

		// Piwik >= 2.1
		if(method_exists('Piwik\Piwik', 'hasUserSuperUserAccess')) {

		    $superUserAccess = Piwik::hasUserSuperUserAccess();

		    // Piwik < 2.1
		} else {

		    $superUserAccess = Piwik::isUserIsSuperUser();

		}

		MenuAdmin::getInstance()->add(
		    'General_Settings',
		    'Custom Opt-Out',
		    array('module' => 'CustomOptOut', 'action' => 'index'),
		    $superUserAccess,
		    $order = 6
		);

	}

	/**
	 * Plugin install hook
	 *
	 * @throws \Exception
	 */
	public function install() {

		try {

		    $sql = sprintf(
		        "ALTER TABLE %s" .
		        " ADD COLUMN `custom_css` TEXT NULL AFTER `keep_url_fragment`," .
		        " ADD COLUMN `custom_css_file` VARCHAR(255) NULL AFTER `custom_css`;", Common::prefixTable('site')
		    );

		    Db::exec($sql);

		} catch(\Exception $exp) {

		    if(!Db::get()->isErrNo($exp, '1060')) {
		        throw $exp;
		    }

		}

	}

	/**
	 * Plugin uninstall hook
	 *
	 * @throws \Exception
	 */
	public function uninstall() {

		try {

		    $sql = sprintf(
		        "ALTER TABLE %s" .
		        " DROP COLUMN `custom_css`," .
		        " DROP COLUMN `custom_css_file`;", Common::prefixTable('site')
		    );

		    Db::exec($sql);

		} catch(\Exception $exp) {

		    if(!Db::get()->isErrNo($exp, '1091')) {
		        throw $exp;
		    }

		}

	}
}


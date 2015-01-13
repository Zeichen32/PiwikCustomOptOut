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
use Piwik\Nonce;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugins\CustomOptOut\Manager\LanguageManager;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\SitesManager\API as APISiteManager;
use Piwik\Plugins\LanguagesManager\API as APILanguagesManager;
use Piwik\Tracker\IgnoreCookie;
use Piwik\Plugins\CustomOptOut\Manager\LanguageManager as CustomLanguageManager;
use Piwik\View;
use Piwik\Piwik;
use Piwik\Site;

/**
 *
 * @package CustomOptOut
 */
class Controller extends ControllerAdmin {

	/**
	 * Main Plugin Index
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	public function index() {

		Piwik::checkUserHasSomeAdminAccess();

		if(isset($_SERVER['REQUEST_METHOD']) && 'POST' == $_SERVER['REQUEST_METHOD']) {

		    // Cannot use Common::getRequestVar, because the function remove whitespaces and newline breaks
		    $postedSiteData = isset($_POST['site']) ? $_POST['site'] : null;

		    if(is_array($postedSiteData) && count($postedSiteData) > 0) {

		        foreach($postedSiteData as $id => $site) {

		            if(!isset($site['css'], $site['file'])) {
		                continue;
		            }

		            API::getInstance()->saveSite($id, $site['css'], $site['file']);

		        }

		        // Redirect to, clear POST vars
		        $this->redirectToIndex('CustomOptOut', 'index');
		        return;

		    }
		}

		$view = new View('@CustomOptOut/index.twig');
		Site::clearCache();

		// Piwik >= 2.1
		if(method_exists('Piwik\Piwik', 'hasUserSuperUserAccess')) {

		    $superUserAccess = Piwik::hasUserSuperUserAccess();

		// Piwik < 2.1
		} else {

		    $superUserAccess = Piwik::isUserIsSuperUser();

		}

		if ($superUserAccess) {

		    $sitesRaw = APISiteManager::getInstance()->getAllSites();

		} else {

		    $sitesRaw = APISiteManager::getInstance()->getSitesWithAdminAccess();

		}

		// Gets sites after Site.setSite hook was called
		$sites = array_values( Site::getSites() );

		if(count($sites) != count($sitesRaw)) {

		    throw new \Exception("One or more website are missing or invalid.");

		}

		foreach ($sites as &$site) {

		    $site['alias_urls'] = APISiteManager::getInstance()->getSiteUrlsFromId($site['idsite']);

		}

		$view->adminSites = $sites;
		$view->adminSitesCount = count($sites);
		$view->language = LanguagesManager::getLanguageCodeForCurrentUser();
		$view->isEditorEnabled = API::getInstance()->isCssEditorEnabled();
		$view->editorTheme = API::getInstance()->getEditorTheme();
		$this->setBasicVariablesView($view);

		return $view->render();

	}

	public function changeTranslation() {

		Piwik::checkUserHasSomeAdminAccess();

		$siteId = Common::getRequestVar('idSite', 0, 'integer');
		$lang 	= Common::getRequestVar('language', '');

		if($siteId > 0) {
			$site = API::getInstance()->getSiteDataId($siteId);

			if(!$site) {
				throw new \Exception('Website was not found!');
			}
		} else {
			$site = null;
			$siteId = null;
		}

		if(!$lang || !APILanguagesManager::getInstance()->isLanguageAvailable($lang)) {
			$view = new View('@CustomOptOut/changeTranslation');
			$view->selectLanguage = true;
			$view->site = $site;
			$view->idSite = $siteId;
			$view->languages = APILanguagesManager::getInstance()->getAvailableLanguageNames();
			$this->setBasicVariablesView($view);
			return $view->render();
		}

		LanguageManager::loadLanguages($lang);
		$values = LanguageManager::getSiteTranslationsForEdit($siteId);
		$defaults = LanguageManager::getSiteTranslations();
		$isPostRequest = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST';

		$values['CustomOptOut']['OptOutComplete'] = Common::getRequestVar('OptOutComplete', $isPostRequest ? '' : $values['CustomOptOut']['OptOutComplete'], 'string');
		$values['CustomOptOut']['OptOutCompleteBis'] = Common::getRequestVar('OptOutCompleteBis', $isPostRequest ? '' :$values['CustomOptOut']['OptOutCompleteBis'], 'string');
		$values['CustomOptOut']['YouAreOptedIn'] = Common::getRequestVar('YouAreOptedIn', $isPostRequest ? '' :$values['CustomOptOut']['YouAreOptedIn'], 'string');
		$values['CustomOptOut']['YouAreOptedOut'] = Common::getRequestVar('YouAreOptedOut', $isPostRequest ? '' :$values['CustomOptOut']['YouAreOptedOut'], 'string');
		$values['CustomOptOut']['YouMayOptOut'] = Common::getRequestVar('YouMayOptOut', $isPostRequest ? '' :$values['CustomOptOut']['YouMayOptOut'], 'string');
		$values['CustomOptOut']['YouMayOptOutBis'] = Common::getRequestVar('YouMayOptOutBis', $isPostRequest ? '' :$values['CustomOptOut']['YouMayOptOutBis'], 'string');
		$values['CustomOptOut']['ClickHereToOptIn'] = Common::getRequestVar('ClickHereToOptIn', $isPostRequest ? '' :$values['CustomOptOut']['ClickHereToOptIn'], 'string');
		$values['CustomOptOut']['ClickHereToOptOut'] = Common::getRequestVar('ClickHereToOptOut', $isPostRequest ? '' :$values['CustomOptOut']['ClickHereToOptOut'], 'string');

		if($isPostRequest) {
			LanguageManager::saveSiteTranslation($lang, $values['CustomOptOut'], $siteId);

			// Redirect to, clear POST vars
			$this->redirectToIndex('CustomOptOut', 'changeTranslation', null, null, null, array('idSite' => $siteId, 'language' => $lang));
			return;
		}

		$view = new View('@CustomOptOut/changeTranslation');
		$view->selectLanguage = false;
		$view->site = $site;
		$view->idSite = $siteId;
		$view->languages = APILanguagesManager::getInstance()->getAvailableLanguageNames();
		$view->selectedLanguage = $lang;
		$view->values = $values;
		$view->defaults = $defaults;
		$this->setBasicVariablesView($view);
		return $view->render();

	}

	/**
	 * Shows the "Track Visits" checkbox.
	 */
	public function optOut() {

		$trackVisits = !IgnoreCookie::isIgnoreCookieFound();

		$nonce = Common::getRequestVar('nonce', false);
		$language = Common::getRequestVar('language', '');

		$siteId = Common::getRequestVar('idSite', 0, 'integer');
		$site = API::getInstance()->getSiteDataId($siteId);

		if(!$site) {

		    throw new \Exception('Website was not found!');

		}

		if (false !== $nonce && Nonce::verifyNonce('Piwik_OptOut', $nonce)) {

		    Nonce::discardNonce('Piwik_OptOut');
		    IgnoreCookie::setIgnoreCookie();
		    $trackVisits = !$trackVisits;

		}

		CustomLanguageManager::changeSiteTranslations($siteId);

        $lang = APILanguagesManager::getInstance()->isLanguageAvailable($language)
            ? $language
            : LanguagesManager::getLanguageCodeForCurrentUser();


		$view = new View('@CustomOptOut/optOut');
		$view->site = $site;
        $view->setXFrameOptions('allow');
        $view->trackVisits = $trackVisits;
        $view->nonce = Nonce::getNonce('Piwik_OptOut', 3600);
        $view->language = $lang;

        return $view->render();

	}
}

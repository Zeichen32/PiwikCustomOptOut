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
use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugins\LanguagesManager\API as APILanguagesManager;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;
use Piwik\Plugins\SitesManager\API as APISiteManager;
use Piwik\Site;
use Piwik\Tracker\IgnoreCookie;
use Piwik\UrlHelper;
use Piwik\View;

/**
 *
 * @package CustomOptOut
 */
class Controller extends ControllerAdmin
{

    /**
     * Main Plugin Index
     *
     * @return mixed
     * @throws \Exception
     */
    public function index()
    {

        Piwik::checkUserHasSomeAdminAccess();

        if (isset($_SERVER['REQUEST_METHOD']) && 'POST' == $_SERVER['REQUEST_METHOD']) {

            // Cannot use Common::getRequestVar, because the function remove whitespaces and newline breaks
            $postedSiteData = isset($_POST['site']) ? $_POST['site'] : null;

            if (is_array($postedSiteData) && count($postedSiteData) > 0) {

                foreach ($postedSiteData as $id => $site) {

                    if (!isset($site['css'], $site['file'])) {
                        continue;
                    }

                    // Check URL
                    if (!UrlHelper::isLookLikeUrl($site['file'])) {
                        $site['file'] = null;
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

        if (Piwik::hasUserSuperUserAccess()) {
            $sitesRaw = APISiteManager::getInstance()->getAllSites();
        } else {
            $sitesRaw = APISiteManager::getInstance()->getSitesWithAdminAccess();
        }

        // Gets sites after Site.setSite hook was called
        $sites = array_values(Site::getSites());

        if (count($sites) != count($sitesRaw)) {
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
        $view->showOldLinks = version_compare(\Piwik\Version::VERSION, '2.14.1', '<');

        $this->setBasicVariablesView($view);

        return $view->render();
    }

    /**
     * Shows the "Track Visits" checkbox.
     * @deprecated This action is introduced only to keep BC with older piwik versions <= 2.14.1
     *             If action is called in newer piwik versions, the user will be redirected to
     *             CoreAdminHome:optOut
     */
    public function optOut()
    {
        $siteId = Common::getRequestVar('idSite', 0, 'integer');

        // Redirect to default OptOut Method if OptOut Manager available
        if (version_compare(\Piwik\Version::VERSION, '2.14.1', '>=') &&
            class_exists('\Piwik\Plugins\CoreAdminHome\OptOutManager')
        ) {
            $params = $_GET;

            // Remove action and module parameter to avoid endless redirect
            unset($params['action']);
            unset($params['module']);

            $this->redirectToIndex('CoreAdminHome', 'optOut', $siteId, null, null);
            return;
        }

        $site = API::getInstance()->getSiteDataId($siteId);

        if (!$site) {
            throw new \Exception('Website was not found!');
        }

        $trackVisits = !IgnoreCookie::isIgnoreCookieFound();

        $nonce = Common::getRequestVar('nonce', false);
        $language = Common::getRequestVar('language', '');

        if (false !== $nonce && Nonce::verifyNonce('Piwik_OptOut', $nonce)) {
            Nonce::discardNonce('Piwik_OptOut');
            IgnoreCookie::setIgnoreCookie();
            $trackVisits = !$trackVisits;
        }

        $lang = APILanguagesManager::getInstance()->isLanguageAvailable($language)
            ? $language
            : LanguagesManager::getLanguageCodeForCurrentUser();

        // Find Translation Key -- BC Piwik < 2.12.0
        if (version_compare(\Piwik\Version::VERSION, '2.12.0', '<=') ||
            \Piwik\Piwik::translate('CoreAdminHome_OptOutDntFound') == 'CoreAdminHome_OptOutDntFound'
        ) {
            $dntTranslationKey = 'CustomOptOut_OptOutDntFound';
        } else {
            $dntTranslationKey = 'CoreAdminHome_OptOutDntFound';
        }

        $dntChecker = new DoNotTrackHeaderChecker();

        $view = new View('@CustomOptOut/optOut');
        $view->setXFrameOptions('allow');
        $view->site = $site;
        $view->dntFound = $dntChecker->isDoNotTrackFound();
        $view->dntTranslationKey = $dntTranslationKey;
        $view->trackVisits = $trackVisits;
        $view->nonce = Nonce::getNonce('Piwik_OptOut', 3600);
        $view->language = $lang;

        return $view->render();
    }
}

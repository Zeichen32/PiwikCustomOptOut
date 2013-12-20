<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CustomOptOut
 */
namespace Piwik\Plugins\CustomOptOut;

use Piwik\Common;
use Piwik\Db;
use Piwik\Piwik;

/**
 * API for plugin CustomOptOut
 *
 * @package CustomOptOut
 * @method static \Piwik\Plugins\CustomOptOut\API getInstance()
 */
class API extends \Piwik\Plugin\API
{
    public function saveSite($siteId, $customCss = null, $customFile = null)
    {
        Piwik::checkUserHasAdminAccess($siteId);

        $query = "UPDATE " . Common::prefixTable("site") .
            " SET custom_css = ?, custom_css_file = ?" .
            " WHERE idsite = ?";

        $bind = array($customCss, $customFile, $siteId);
        Db::query($query, $bind);
    }

    /**
     * Returns the website information : name, main_url
     *
     * @throws Exception if the site ID doesn't exist or the user doesn't have access to it
     * @param int $idSite
     * @return array
     */
    public function getSiteDataId($idSite)
    {
        $site = Db::get()->fetchRow("SELECT idsite, custom_css, custom_css_file
    								FROM " . Common::prefixTable("site") . "
    								WHERE idsite = ?", $idSite);

        return $site;
    }
}
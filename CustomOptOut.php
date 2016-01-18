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
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Plugins\CustomOptOut\Settings;

/**
 * @package CustomOptOut
 */
class CustomOptOut extends \Piwik\Plugin
{

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getJavaScriptFiles' => 'getJsFiles',
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Controller.CoreAdminHome.optOut' => 'addOptOutStyles',
        );
    }

    /**
     * @param $jsFiles
     */
    public function getJsFiles(&$jsFiles)
    {

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
    public function getStylesheetFiles(&$stylesheets)
    {

        // CodeMirror CSS
        $stylesheets[] = "plugins/CustomOptOut/stylesheets/codemirror/codemirror.css";
        $stylesheets[] = "plugins/CustomOptOut/stylesheets/codemirror/theme/blackboard.css";
        $stylesheets[] = "plugins/CustomOptOut/stylesheets/codemirror/lint.css";
        $stylesheets[] = "plugins/CustomOptOut/stylesheets/codemirror/show-hint.css";

    }

    /**
     * @throws \Exception
     */
    public function addOptOutStyles()
    {
        /** @var \Piwik\Plugins\CoreAdminHome\OptOutManager $manager */
        $manager = StaticContainer::get('Piwik\Plugins\CoreAdminHome\OptOutManager');

        $settings = new Settings();

        // See Issue #33
        $siteId = Common::getRequestVar('idsite', 0, 'integer');

        // Is still available for BC
        if (!$siteId) {
            $siteId = Common::getRequestVar('idSite', 0, 'integer');
        }

        // Try to find siteId in Session
        if (!$siteId) {
            if ($settings->defaultCssStyles->getValue()) {
                $manager->addStylesheet($settings->defaultCssStyles->getValue());
            }

            if ($settings->defaultCssFile->getValue()) {
                $manager->addStylesheet($settings->defaultCssFile->getValue(), false);
            }

            return;
        }

        $site = API::getInstance()->getSiteDataId($siteId);

        if (!$site) {
            if ($settings->defaultCssStyles->getValue()) {
                $manager->addStylesheet($settings->defaultCssStyles->getValue());
            }

            if ($settings->defaultCssFile->getValue()) {
                $manager->addStylesheet($settings->defaultCssFile->getValue(), false);
            }

            return;
        }

        $manager->addQueryParameter('idsite', $siteId);

        // Add CSS file if set
        if (!empty($site['custom_css_file'])) {
            $manager->addStylesheet($site['custom_css_file'], false);
        }

        // Add CSS Inline Styles if set
        if (!empty($site['custom_css'])) {
            $manager->addStylesheet($site['custom_css'], true);
        }
    }

    /**
     * Plugin install hook
     *
     * @throws \Exception
     */
    public function install()
    {

        try {

            $sql = sprintf(
                "ALTER TABLE %s" .
                " ADD COLUMN `custom_css` TEXT NULL AFTER `keep_url_fragment`," .
                " ADD COLUMN `custom_css_file` VARCHAR(255) NULL AFTER `custom_css`;",
                Common::prefixTable('site')
            );

            Db::exec($sql);

        } catch (\Exception $exp) {

            if (!Db::get()->isErrNo($exp, '1060')) {
                throw $exp;
            }

        }

    }

    /**
     * Plugin uninstall hook
     *
     * @throws \Exception
     */
    public function uninstall()
    {

        try {

            $sql = sprintf(
                "ALTER TABLE %s" .
                " DROP COLUMN `custom_css`," .
                " DROP COLUMN `custom_css_file`;",
                Common::prefixTable('site')
            );

            Db::exec($sql);

        } catch (\Exception $exp) {

            if (!Db::get()->isErrNo($exp, '1091')) {
                throw $exp;
            }

        }

    }
}


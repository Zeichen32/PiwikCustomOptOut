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

use Piwik\Settings\UserSetting;
use Piwik\Piwik;

class Settings extends \Piwik\Plugin\Settings {

    /**
     * @var UserSetting
     */
    public $enableEditor;

    /**
     * Implemented by descendants. This method should define plugin settings (via the
     * {@link addSetting()}) method and set the introduction text (via the
     * {@link setIntroduction()}).
     */
    protected function init()
    {
        $this->setIntroduction('Here you can specify the settings for this plugin.');

        $this->createEnableEditorSetting();
    }

    private function createEnableEditorSetting() {
        $this->enableEditor                 = new UserSetting('enableEditor',
                                                Piwik::translate('CustomOptOut_ShowEditorOptionName'));

        $this->enableEditor->type           = static::TYPE_BOOL;
        $this->enableEditor->uiControlType  = static::CONTROL_CHECKBOX;
        $this->enableEditor->description    = Piwik::translate('CustomOptOut_ShowEditorDescription');
        $this->enableEditor->defaultValue   = true;

        $this->addSetting($this->enableEditor);
    }

}

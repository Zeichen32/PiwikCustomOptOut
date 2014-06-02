<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 02.06.2014
 * Time: 15:42
 */

namespace Piwik\Plugins\CustomOptOut;

use Piwik\Settings\SystemSetting;
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
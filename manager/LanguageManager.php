<?php
/**
 * Created by Two Developers - Sven Motz und Jens Averkamp GbR
 * http://www.two-developers.com
 *
 * Developer: Jens Averkamp
 * Date: 08.01.2015
 * Time: 19:02
 */

namespace Piwik\Plugins\CustomOptOut\Manager;


use Piwik\Piwik;
use Piwik\Translate;

class LanguageManager {

    public static function loadLanguages($language = null) {

        if(
            isset($GLOBALS['Piwik_translations'], $GLOBALS['Piwik_translations']['CustomOptOut'])
            && is_array($GLOBALS['Piwik_translations']['CustomOptOut'])
        ) {

            if(null === $language) {
                $currentLanguage = Translate::getLanguageLoaded();
                $fallbackLanguage = Translate::getLanguageDefault();
            } else {
                $currentLanguage = $language;
                $fallbackLanguage = $language;
            }

            $translations = array();

            if(is_file(__DIR__ . '/../lang/sites/' . $currentLanguage . '.json')) {
                $translations = json_decode(file_get_contents(__DIR__ . '/../lang/sites/' . $currentLanguage . '.json'), true);
            } elseif(is_file(__DIR__ . '/../lang/sites/' . $fallbackLanguage . '.json')) {
                $translations = json_decode(file_get_contents(__DIR__ . '/../lang/sites/' . $fallbackLanguage . '.json'), true);
            }

            $GLOBALS['Piwik_CustomOptOut_Translations'] = $translations;
        } else {
            $GLOBALS['Piwik_CustomOptOut_Translations'] = array();
        }
    }

    public static function getSiteTranslations($siteId = null) {

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

    public static function getSiteTranslationsForEdit($siteId = null) {

        $isGlobalAvailable = array_key_exists('Global', $GLOBALS['Piwik_CustomOptOut_Translations']);

        $translation = array('CustomOptOut' => array(
            'OptOutComplete' 	=> '',
            'OptOutCompleteBis' => '',
            'YouAreOptedIn' 	=> '',
            'YouAreOptedOut' 	=> '',
            'YouMayOptOut' 		=> '',
            'YouMayOptOutBis' 	=> '',
            'ClickHereToOptIn' 	=> '',
            'ClickHereToOptOut' => '',
        ));

        if(null !== $siteId && array_key_exists((string) $siteId, $GLOBALS['Piwik_CustomOptOut_Translations'])) {

            $translation = array('CustomOptOut' =>
                array_merge($translation['CustomOptOut'], $GLOBALS['Piwik_CustomOptOut_Translations'][$siteId])
            );
        } elseif(null === $siteId && $isGlobalAvailable) {

            $translation = array('CustomOptOut' =>
                array_merge($translation['CustomOptOut'], $GLOBALS['Piwik_CustomOptOut_Translations']['Global'])
            );
        }

        return $translation;
    }

    public static function saveSiteTranslation($language, array $data, $siteId = null) {

        $translations = array();

        // Load translation data if available
        if(is_file(__DIR__ . '/../lang/sites/' . $language . '.json')) {
            $translations = json_decode(file_get_contents(__DIR__ . '/../lang/sites/' . $language . '.json'), true);
        }

        $translation = array();

        empty($data['OptOutComplete'])      ?: $translation['OptOutComplete']       = $data['OptOutComplete'];
        empty($data['OptOutCompleteBis'])   ?: $translation['OptOutCompleteBis']    = $data['OptOutCompleteBis'];
        empty($data['YouAreOptedIn'])       ?: $translation['YouAreOptedIn']        = $data['YouAreOptedIn'];
        empty($data['YouAreOptedOut'])      ?: $translation['YouAreOptedOut']       = $data['YouAreOptedOut'];
        empty($data['YouMayOptOut'])        ?: $translation['YouMayOptOut']         = $data['YouMayOptOut'];
        empty($data['YouMayOptOutBis'])     ?: $translation['YouMayOptOutBis']      = $data['YouMayOptOutBis'];
        empty($data['ClickHereToOptIn'])    ?: $translation['ClickHereToOptIn']     = $data['ClickHereToOptIn'];
        empty($data['ClickHereToOptOut'])   ?: $translation['ClickHereToOptOut']    = $data['ClickHereToOptOut'];

        // Delete site section if no translation submitted
        if(count($translation) < 1 && null !== $siteId && isset($translations[$siteId])) {
            unset($translations[$siteId]);

        // Write site section
        } elseif(null !== $siteId && count($translation) > 0) {
            $translations[$siteId] = $translation;

        // Write / Delete Global section
        } elseif(!$siteId) {

            if(count($translation) > 0) {
                $translations['Global'] = $translation;
            } else {
                unset($translations['Global']);
            }
        }

        // If translations available save translation file
        if(count($translations) > 0) {
            file_put_contents(__DIR__ . '/../lang/sites/' . $language . '.json', json_encode($translations, JSON_PRETTY_PRINT));

        // If no translations available delete the exist translation file
        } elseif(is_file(__DIR__ . '/../lang/sites/' . $language . '.json')) {
            unlink(__DIR__ . '/../lang/sites/' . $language . '.json');
        }
    }
}

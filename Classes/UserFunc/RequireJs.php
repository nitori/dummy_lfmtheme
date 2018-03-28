<?php
/**
 * Created by PhpStorm.
 * User: Lars_Soendergaard
 * Date: 12.09.2017
 * Time: 14:45
 */

namespace LFM\Lfmtheme\UserFunc;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

class RequireJs
{
    /**
     * Load requirejs configuration file (json file) and returns the necessary requirejs
     * configuration for embedding in page markup.
     *
     * @param $content
     * @param $tsConf
     * @return string
     */
    public function generateRequireJsConfig($content, $tsConf)
    {
        $configPath = GeneralUtility::getFileAbsFileName($tsConf['config']);
        $config = json_decode(file_get_contents($configPath), true);
        if (!$config) {
            throw new \UnexpectedValueException("Could not load requirejs configuration file. " .
                "Please check for syntax errors or if the file exists.");
        }

        foreach ($config['paths'] as $module => $path) {
            $tmp = GeneralUtility::getFileAbsFileName($path);
            if (!empty($tmp)) {
                $path = PathUtility::getAbsoluteWebPath($tmp);
                $path = rtrim($path, '/');
            }
            $config['paths'][$module] = $path;
        }

        if (intval($tsConf['bust'])) {
            $bust = 'bust='.time();
            if (isset($config['urlArgs']) && $config['urlArgs']) {
                $bust = $config['urlArgs'] . '&' . $bust;
            }
            $config['urlArgs'] = $bust;
        }

        $json = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        // output requirejs configuration
        $html =  "<script type=\"text/javascript\">\n";
        $html .= "/*<![CDATA[*/\n";
        $html .= sprintf("var require = %s\n", $json);
        $html .= "/*]]>*/\n";
        $html .= "</script>\n";

        // load requirejs
        $requireJsUrl = PathUtility::getAbsoluteWebPath('typo3/sysext/core/Resources/Public/JavaScript/Contrib/require.js');
        $html .= sprintf("<script type=\"text/javascript\" src=\"%s\"></script>\n", $requireJsUrl);

        // load modules
        $html .= "<script type=\"text/javascript\">\n";
        $html .= "/*<![CDATA[*/\n";
        if (isset($tsConf['load.']) && is_array($tsConf['load.'])) {
            $html .= $this->generateModuleMarkup($tsConf['load.']);
        }
        $html .= "/*]]>*/\n";
        $html .= "</script>\n";

        return $html;
    }

    /**
     * Generate HTML that's embedded directly in the page and is responsible for initially loading
     * requirejs modules.
     *
     * This function is recursive, making it possible to nest the configuration:
     *
     *     load {
     *         theme = TYPO3/CMS/Lfmtheme/Theme
     *         theme {
     *             parsley-de = parsley-de
     *         }
     *     }
     *
     * Results in:
     *
     *     require(["TYPO3/CMS/Lfmtheme/Theme"], function(){
     *         require(["parsley-de"])
     *     })
     *
     * @param $conf
     * @param int $depth
     * @return string
     */
    protected function generateModuleMarkup($conf, $depth=0)
    {
        $indent = str_repeat(' ', $depth*4);
        $html = "";
        foreach ($conf as $key => $module) {
            if (substr($key, -1) == '.') {
                continue;
            }
            $html .= $indent . sprintf('require(["%s"]', (string)$module);
            if (isset($conf[$key.'.']) && is_array($conf[$key.'.']) && !empty($conf[$key.'.'])) {
                $html .= ", function(){\n";
                $html .= $this->generateModuleMarkup($conf[$key.'.'], $depth+1);
                $html .= $indent . "}";
            }
            $html .= ");\n";
        }
        return $html;
    }
}
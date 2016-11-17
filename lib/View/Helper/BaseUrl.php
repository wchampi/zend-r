<?php
/**
 *
 * @author Wilson Ramiro Champi Tacuri
 */

class ZendR_View_Helper_BaseUrl extends Zend_View_Helper_BaseUrl
{
    public function baseUrl($file = null)
    {
        // Get baseUrl
        $baseUrl = $this->getBaseUrl();

        // Remove trailing slashes
        if (null !== $file) {
            if (substr($file, 0, 4) == 'http') {
                return $file;
            }
            $file = '/' . ltrim($file, '/\\');
        }

        if ($this->view->paramsUrl !== null) {
            if ($file == null) {
                $baseUrl = $this->view->baseUrlOriginal;
            } else {
                if (is_file(dirname($_SERVER['SCRIPT_FILENAME']) . $file) && $this->view->baseUrlOriginal != $baseUrl) {
                    $baseUrl = $this->view->baseUrlOriginal;
                }
            }
        }
        
        if (defined('MODULE_ENV')) {
            if (substr($file, 0, strlen(MODULE_ENV) + 2) == '/' . MODULE_ENV . '/') {
                $file = str_replace('/' . MODULE_ENV . '/', '/', $file);
            } elseif ($file == '/' . MODULE_ENV) {
                $file = '/';
            }
        }
        
        return $baseUrl . $file;
    }

}

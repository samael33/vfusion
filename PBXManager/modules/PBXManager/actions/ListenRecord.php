<?php
/*+**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is: SalesPlatform Ltd
 * The Initial Developer of the Original Code is SalesPlatform Ltd.
 * All Rights Reserved.
 * If you have any questions or comments, please email: devel@SPManager
 ************************************************************************************/

class PBXManager_ListenRecord_Action extends Vtiger_Action_Controller {

    public function checkPermission(Vtiger_Request $request) {
        $moduleName = $request->getModule();

        if(!Users_Privileges_Model::isPermitted($moduleName, 'ListView', $request->get('record'))) {
            throw new AppException(vtranslate('LBL_PERMISSION_DENIED', $moduleName));
        }
    }

    public function process(Vtiger_Request $request) {
        $pbxRecordModel = PBXManager_Record_Model::getInstanceById($request->get('record'));
        if($pbxRecordModel->get('recordingurl') != null) {
            $filePath = $pbxRecordModel->get('recordingurl');
            $fileContent = file_get_contents($filePath);
            if($fileContent === false) {
                return;
            }
            $contentType = mime_content_type($filePath);
            header('Content-Type: ' . $contentType);
            header('Content-Length: ' . filesize($filePath));
            echo $fileContent;                
            return;            
        }
    }
    
    private function prepareCurl($pbxRecordModel) {
        
        $pbxSettinsModel = PBXManager_Server_Model::getInstance();
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($curl, CURLOPT_URL, $pbxRecordModel->get('recordingurl') . "&secret=" . urlencode($pbxSettinsModel->get('vtigersecretkey')));
        
        return $curl;
    }
    
    private function getHeadersList($headerContent) {
        $headersList = array();
        foreach(explode("\r\n", $headerContent) as $number => $header) {
            if($number == 0) {
                $headersList['http_code'] = $header;
            } else {
               list($headerName, $headerValue) = explode(': ', $header); 
               $headersList[strtolower($headerName)] = trim($headerValue);
            }
        }
        
        return $headersList;
    }
}
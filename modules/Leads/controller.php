<?php
/**
 *
 * SugarCRM Community Edition is a customer relationship management program developed by
 * SugarCRM, Inc. Copyright (C) 2004-2013 SugarCRM Inc.
 *
 * SuiteCRM is an extension to SugarCRM Community Edition developed by SalesAgility Ltd.
 * Copyright (C) 2011 - 2018 SalesAgility Ltd.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY SUGARCRM, SUGARCRM DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 * You can contact SugarCRM, Inc. headquarters at 10050 North Wolfe Road,
 * SW2-130, Cupertino, CA 95014, USA. or at email address contact@sugarcrm.com.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * SugarCRM" logo and "Supercharged by SuiteCRM" logo. If the display of the logos is not
 * reasonably feasible for technical reasons, the Appropriate Legal Notices must
 * display the words "Powered by SugarCRM" and "Supercharged by SuiteCRM".
 */

class LeadsController extends SugarController
{
    public function __construct()
    {
        parent::__construct();
    }



    public function pre_editview()
    {
        //IF we have a prospect id leads convert it to a lead
        if (empty($this->bean->id) && !empty($_REQUEST['return_module']) &&$_REQUEST['return_module'] == 'Prospects') {
            $prospect=BeanFactory::newBean('Prospects');
            $prospect->retrieve($_REQUEST['return_id']);
            foreach ($prospect->field_defs as $key=>$value) {
                if ($key == 'id' or $key=='deleted') {
                    continue;
                }
                if (isset($this->bean->field_defs[$key])) {
                    $this->bean->$key = $prospect->$key;
                }
            }
            $_POST['is_converted']=true;
        }
        return true;
    }
    public function action_editview()
    {
        $this->view = 'edit';
        return true;
    }

    public function action_CampaignView()
    {
        $this->view = 'compose';
        // For viewing the Compose as modal from other modules we need to load the Emails language strings
        if (isset($_REQUEST['in_popup']) && $_REQUEST['in_popup']) {
            if (!is_file('cache/jsLanguage/Leads/' . $GLOBALS['current_language'] . '.js')) {
                require_once('include/language/jsLanguage.php');
                jsLanguage::createModuleStringsCache('Leads', $GLOBALS['current_language']);
            }
            echo '<script src="cache/jsLanguage/Leads/'. $GLOBALS['current_language'] . '.js"></script>';
        }
        if (isset($_REQUEST['ids']) && isset($_REQUEST['targetModule'])) {
            $toAddressIds = explode(',', rtrim($_REQUEST['ids'], ','));
            foreach ($toAddressIds as $id) {
                $destinataryBean = BeanFactory::getBean($_REQUEST['targetModule'], $id);
                if ($destinataryBean) {
                    $idLine = '<input type="hidden" class="email-compose-view-to-list" ';
                    $idLine .= 'data-record-module="' . $_REQUEST['targetModule'] . '" ';
                    $idLine .= 'data-record-id="' . $id . '" ';
                    $idLine .= 'data-record-name="' . $destinataryBean->name . '" ';
                    $idLine .= 'data-record-email="' . $destinataryBean->email1 . '">';
                    echo $idLine;
                }
            }
        }
        if (isset($_REQUEST['relatedModule']) && isset($_REQUEST['relatedId'])) {
            $relateBean = BeanFactory::getBean($_REQUEST['relatedModule'], $_REQUEST['relatedId']);
            $relateLine = '<input type="hidden" class="email-relate-target" ';
            $relateLine .= 'data-relate-module="' . $_REQUEST['relatedModule'] . '" ';
            $relateLine .= 'data-relate-id="' . $_REQUEST['relatedId'] . '" ';
            $relateLine .= 'data-relate-name="' . $relateBean->name . '">';
            echo $relateLine;
        }
    }

    protected function callLegacyCode()
    {
        if (strtolower($this->do_action) == 'convertlead') {
            if (file_exists('modules/Leads/ConvertLead.php') && !file_exists('custom/modules/Leads/metadata/convertdefs.php')) {
                if (!empty($_REQUEST['emailAddressWidget'])) {
                    foreach ($_REQUEST as $key=>$value) {
                        if (preg_match('/^Leads.*?emailAddress[\d]+$/', $key)) {
                            $_REQUEST['Leads_email_widget_id'] = 0;
                            break;
                        }
                    }
                }
                $this->action_default();
                $this->_processed = true;
            } else {
                $this->view = 'convertlead';
                $this->_processed = true;
            }
        } else {
            parent::callLegacyCode();
        }
    }
}

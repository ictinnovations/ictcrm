<?php

require_once('include/ListView/ListViewSmarty.php');

class OpportunitiesListViewSmarty extends ListViewSmarty
{
    public function __construct()
    {
        parent::__construct();
    }




    public function buildExportLink($id = 'export_link')
    {
        global $app_strings;

        if ($_REQUEST['module'] == 'Opportunities') {
          $script = "<a class='email-link' href='javascript:void(0)'
                  onclick=\"$(document).openCampaignViewModal(this);\"
                  data-module data-record-id data-module-name data-email-address>{$app_strings['LBL_EXPORT']}</a>" .
                  "</li><li>". // List item hack
                  "<a href='javascript:void(0)' id='map_listview_top' " .
                  " onclick=\"return sListView.send_form(true, 'jjwg_Maps', " .
                  "'index.php?entryPoint=jjwg_Maps&display_module={$_REQUEST['module']}', " .
                  "'{$app_strings['LBL_LISTVIEW_NO_SELECTED']}')\">{$app_strings['LBL_MAP']}</a>";
        } else {
        $script = "<a href='javascript:void(0)' class=\"parent-dropdown-action-handler\" id='export_listview_top' ".
                "onclick=\"return sListView.send_form(true, '{$_REQUEST['module']}', " .
                "'index.php?entryPoint=export', " .
                "'{$app_strings['LBL_LISTVIEW_NO_SELECTED']}')\">{$app_strings['LBL_EXPORT']}</a>" .
                "</li><li>". // List item hack
                "<a href='javascript:void(0)' id='map_listview_top' " .
                " onclick=\"return sListView.send_form(true, 'jjwg_Maps', " .
                "'index.php?entryPoint=jjwg_Maps&display_module={$_REQUEST['module']}', " .
                "'{$app_strings['LBL_LISTVIEW_NO_SELECTED']}')\">{$app_strings['LBL_MAP']}</a>";
        }

        return $script;
    }
}

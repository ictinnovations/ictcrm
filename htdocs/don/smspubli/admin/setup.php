<?php
/* SMSPubli: send SMS to thirdparties by smspubli.com
/* Copyright (C) 2012 Maxime MANGIN <maxime@tuxserv.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
 
/**
 * \file    admin/setup.php
 * \ingroup smspubli
 * \brief   SmsPubli setup page.
 *
 * Based on smsdecanet module.
 */

// Load Dolibarr environment
//require(DOL_DOCUMENT_ROOT."/main.inc.php");
if (false === (@include '../../main.inc.php')) {  // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
}

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once("../core/modules/modSmsPubli.class.php");


// Translations
//$langs->load("admin");
$langs->load("smspubli@smspubli");


// Access control
if (! $user->admin) {
	accessforbidden();
}


// Parameters
$action = GETPOST('action', 'alpha');



/*
 * Actions
 */
 
if ($_POST["action"] == 'modifyconfig')
{
	dolibarr_set_const($db, "SMSPUBLI_SMSFROM", $_POST['fromSMS'],'chaine',0,'From SMS, telf number or text',$conf->entity,0);
	if($_POST['apikeySMSpubli']!='')dolibarr_set_const($db, "SMSPUBLI_APIKEY", $_POST['apikeySMSpubli'],'chaine',0,'API key for SMSPUBLI',$conf->entity,0);
}


 
 
/*
 * View
 */
 
$page_name = "SMSPubliSetup";
llxHeader('', $langs->trans($page_name));


// Subheader
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">'
	. $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($langs->trans($page_name), $linkback);


// Configuration header

$head = ''; //mymoduleAdminPrepareHead();
dol_fiche_head(
	$head,
	'settings',
	$langs->trans("Module409000Name"),
	0,
	"smspubli@smspubli"
);


// Setup page goes here
echo $langs->trans("SMSPubliSetupPage"); 
 
 
 

$var=true;

echo '<table class="noborder" width="100%">';
echo '<tr class="liste_titre">';
echo "  <td>".$langs->trans("SMSPubliAccount")."</td>";
echo '</tr>';
echo "<tr ".$bc[$var].">";
echo '<td>';

include_once('../class/SMSpubliAPI.class.php');

$url = 'https://api.gateway360.com/api/3.0';	
$api = new SMSPubliApi($conf->global->SMSPUBLI_APIKEY, $url);

$result = $api->get('/account/get-balance');


if(!$conf->global->SMSPUBLI_APIKEY || ($result->status!="ok")) {
	if (!$conf->global->SMSPUBLI_APIKEY)
		echo $langs->trans('ApiKeyNotDefined').' - (<a href="http://panel.smspubli.com/signup/?ida=67340" target="_blank"><strong>'.$langs->trans('CreateSMSAcount').'</strong></a>)';
	else
		echo $result->error_msg.' - (<a href="http://panel.smspubli.com/signup/?ida=67340" target="_blank"><strong>'.$langs->trans('CreateSMSAcount').'</strong></a>)';
} else {
	echo '<strong>'.$langs->trans('CreditSMS').'</strong> '.$result->result->balance.' '.$result->result->currency.' - (<a href="http://panel.smspubli.com/signup/?ida=67340" target="_blank"><strong>'.$langs->trans('RechargeSms').'</strong></a>)';
}
echo '</td>';
echo '</td>';
echo '</table><br><br>';


echo '<table class="noborder" width="100%">';
echo '<tr class="liste_titre">';
echo "  <td>".$langs->trans("ParametersAccount")."</td>\n";
echo "  <td align=\"left\" ></td>";
echo "  <td >&nbsp;</td></tr>";

$var=!$var;

echo "<form method=\"post\" action=\"setup.php\">";
echo "<input type=\"hidden\" name=\"action\" value=\"modifyconfig\">";
echo "<tr ".$bc[$var].">";
echo '<td>'.$langs->trans("fromSMS").'</td>';
echo '<td align="left"><input type="text" name="fromSMS" size="50" class="flat" value="'.$conf->global->SMSPUBLI_SMSFROM.'"></td>';
echo '<td align="right"></td>';
echo '</tr>';
$var=!$var;
echo "<tr ".$bc[$var].">";
echo '<td>'.$langs->trans("ApiKey").'</td>';
echo '<td align="left"><input type="text" name="apikeySMSpubli" size="50" class="flat" value="'.$conf->global->SMSPUBLI_APIKEY.'"></td>';
echo '<td align="right"><input type="submit" class="button" value="'.$langs->trans("Modify").'"></td>';
echo '</tr>';
echo '</form>';
echo '</table><br><br>';

$db->close();

echo '<table class="noborder" width="100%">';
echo '<tr class="liste_titre">';
echo "  <td>".$langs->trans("HistorySMSAccount")."</td>\n";
echo "  <td align=\"left\" ></td>";
echo "  <td >&nbsp;</td></tr>";
$var=!$var;


//from_date --> Obtain reports from this date onwards. Format is YYYY-MM-DD HH:MM:SS.
//dol_print_date(dol_now(),'standard')
$result = $api->post('/sms/get-reports', 
				array(
				      '"to_date"'=>'"'.dol_print_date(dol_now(),'standard').'"'
			         )
			    );
			    
if(count($result->result)>0) {
	foreach($result->result as $k=>$s) {
		echo "<tr ".$bc[$var].">";
		echo '<td>'.$s->dlr_date.' ('.$s->status.')</td>';
		echo '<td align="left">'.$s->from.' ('.$s->custom.')</td>';
		echo '<td align="left">'.$s->to.'</td>';
		echo '</tr>';
		$var=!$var;
		if($k==50)break;
	}
}

echo '</table><br><br>';



// Page end
dol_fiche_end();
llxFooter();

?>







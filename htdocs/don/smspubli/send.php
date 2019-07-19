<?php
/* Copyright (C) 2012 Maxime MANGIN <maxime@tuxserv.fr>
 * Copyright (C) 2017 Josep Lluis Amador <joseplluis@lliuretic.cat>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
 * \file    send.php
 * \ingroup smspubli
 * \brief   SmsPubli send SMS page.
 *
 * File based on smsdecanet module.
 */

// Load Dolibarr environment
//require(DOL_DOCUMENT_ROOT."/main.inc.php");
if (false === (@include '../../main.inc.php')) {  // From htdocs directory
	require '../../../main.inc.php'; // From "custom" directory
}

// Libraries
require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";
require_once("./core/modules/modSmsPubli.class.php");
$langs->load("admin");
$langs->load("sms");
$langs->load("smspubli@smspubli");

// Security check
if ($user->societe_id > 0)
{
	accessforbidden();
}

$action=GETPOST('action');

if ($action == 'send' && ! $_POST['cancel'])
{	
	$error=0;

	$smsfrom='';
	if (! empty($_POST["fromsms"])) $smsfrom=GETPOST("fromsms");
	if (empty($smsfrom)) $smsfrom=GETPOST("fromname");
	$sendto     = GETPOST("sendto");
	$body       = GETPOST('message');
	$deliveryreceipt= GETPOST("deliveryreceipt");
    $deferred   = GETPOST('deferred');
    $priority   = GETPOST('priority');
    $class      = GETPOST('class');
    $errors_to  = GETPOST("errorstosms");
	// Create form object
	include_once('./core/class/html.formsmspubli.class.php');
	$formsms = new FormSms($db);

	if (! empty($formsms->error))
	{
	    $message='<div class="error">'.$formsms->error.'</div>';
	    $action='singlesms';
	    $error++;
	}
    if (empty($body))
    {
        $message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("Message")).'</div>';
        $action='singlesms';
        $error++;
    }
	if (empty($smsfrom) || ! str_replace('+','',$smsfrom))
	{
		$message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("SmsFrom")).'</div>';
        $action='singlesms';
		$error++;
	}
	if (empty($sendto) || ! str_replace('+','',$sendto))
	{
		$message='<div class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentities("SmsTo")).'</div>';
        $action='singlesms';
		$error++;
	}
	if (! $error)
	{
		require_once(DOL_DOCUMENT_ROOT."/core/class/CSMSFile.class.php");
		$smsfile = new CSMSFile($sendto, $smsfrom, $body, $deliveryreceipt, $deferred, $priority, $class);

		$result=$smsfile->sendfile();

		if ($result!='0')
		{
			$message='<div class="ok">'.$langs->trans("SmsSuccessfulySent",$smsfrom,$sendto).'</div>';
		}
		else
		{
			$message='<div class="error">'.$langs->trans("ResultKo").'<br>'.$smsfile->error.' '.$result.'</div>';
		}

		$action='';
	}

}

llxHeader('',$langs->trans("SendSMS"));
dol_htmloutput_mesg($message);
$to = '+34';
$socid=intval($_GET['id']);
if($socid>0) {
	$soc = new Societe($db);
	$soc->fetch($socid);
	$soc->info($socid);
	if(substr($soc->phone,0,1)!='+')
		$to = '+34'.substr($soc->phone,1);
	else
		$to = $soc->phone;
}


$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("SendSMS"),false,'setup');
include_once("./core/class/html.formsmspubli.class.php");
$formsms = new FormSms($db);
$formsms->fromtype='user';
$formsms->fromid=$user->id;
$formsms->fromsms = (isset($_POST['fromsms'])?$_POST['fromsms']:($conf->global->SMSPUBLI_SMSFROM?$conf->global->SMSPUBLI_SMSFROM:($conf->global->MAIN_MAIL_SMS_FROM?$conf->global->MAIN_MAIL_SMS_FROM:$user->user_mobile)));
$formsms->withfromreadonly=0;
$formsms->withsubstit=0;
$formsms->withfrom=1;
$formsms->witherrorsto=1;
$formsms->withto=$to;
$formsms->withfile=2;
$formsms->withbody=$langs->trans("yourMessage");
$formsms->withbodyreadonly=0;
$formsms->withcancel=0;
$formsms->withfckeditor=0;
// Tableau des parametres complementaires du post
$formsms->param["action"]="send";
$formsms->param["models"]="body";
$formsms->param["smsid"]=0;
$formsms->param["returnurl"]=$_SERVER['REQUEST_URI'];

$formsms->show_form();
$db->close();


llxFooter();

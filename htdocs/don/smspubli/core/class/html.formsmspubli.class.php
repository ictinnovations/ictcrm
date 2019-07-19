<?php
/* Copyright (C) 2005-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2010      Juanjo Menent        <jmenent@2byte.es>
*
* This program is free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 3 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
/**
 *       \file       htdocs/core/class/html.formmail.class.php
 *       \ingroup    core
 *       \brief      Fichier de la classe permettant la generation du formulaire html d'envoi de mail unitaire
 */
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';


/**
 *      Classe permettant la generation du formulaire d'envoi de Sms
 *      Usage: $formsms = new FormSms($db)
 *             $formsms->proprietes=1 ou chaine ou tableau de valeurs
 *             $formsms->show_form() affiche le formulaire
 */
class FormSms
{
    var $db;

    var $fromname;
    var $fromsms;
    var $replytoname;
    var $replytomail;
    var $toname;
    var $tomail;

    var $withsubstit;			// Show substitution array
    var $withfrom;
    var $withto;
    var $withtopic;
    var $withbody;

    var $withfromreadonly;
    var $withreplytoreadonly;
    var $withtoreadonly;
    var $withtopicreadonly;
    var $withcancel;

    var $substit=array();
    var $sendmulti=false;
    var $param=array();

    var $error;


    /**
     *	Constructor
     *
     *  @param		DoliDB		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;

        $this->withfrom=1;
        $this->withto=1;
        $this->withtopic=1;
        $this->withbody=1;

        $this->withfromreadonly=1;
        $this->withreplytoreadonly=1;
        $this->withtoreadonly=0;
        $this->withtopicreadonly=0;
        $this->withbodyreadonly=0;

        return 1;
    }

    /**
     *	Show the form to input an sms.
     *
     *	@param	string	$width	Width of form
     *	@return	void
     */
    function show_form($width='180px')
    {
        global $conf, $langs, $user, $form;

        if (! is_object($form)) $form=new Form($this->db);

        $langs->load("other");
        $langs->load("mails");
        $langs->load("sms");

        $soc=new Societe($this->db);
        if (!empty($this->withtosocid) && $this->withtosocid > 0)
        {
            $soc->fetch($this->withtosocid);
        }

        print "\n<!-- Begin form SMS -->\n";

        print '
            <script language="javascript">
            function limitChars(textarea, limit, infodiv)
            {
                var text = textarea.value;
                var textlength = text.length;
                var info = document.getElementById(infodiv);

                info.innerHTML = (limit - textlength);
                return true;
            }
            </script>';

        print "<form method=\"POST\" name=\"smsform\" enctype=\"multipart/form-data\" action=\"".$this->param["returnurl"]."\">\n";
        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        foreach ($this->param as $key=>$value)
        {
            print "<input type=\"hidden\" name=\"$key\" value=\"$value\">\n";
        }
        print "<table class=\"border\" width=\"100%\">\n";

        // Substitution array
        if ($this->withsubstit)
        {
            print "<tr><td colspan=\"2\">";
            $help="";
            foreach($this->substit as $key => $val)
            {
                $help.=$key.' -> '.$langs->trans($val).'<br>';
            }
            print $form->textwithpicto($langs->trans("SmsTestSubstitutionReplacedByGenericValues"),$help);
            print "</td></tr>\n";
        }
        // From
        if ($this->withfrom)
        {
                print "<tr><td width=\"".$width."\">".$langs->trans("SmsFrom")."</td><td>";
                print '<input type="text" name="fromname" size="30" value="'.$this->fromsms.'">';
			
                print '</td>';
                print "</tr>\n";
        }

        // To (target)
        
        if(!$this->sendmulti){
            if ($this->withto || is_array($this->withto))
            {
                print '<tr><td width="180">';
                //$moretext=$langs->trans("YouCanUseCommaSeparatorForSeveralRecipients");
                $moretext='';
                print $form->textwithpicto($langs->trans("SmsTo"),$moretext);
                print '</td><td>';
                if ($this->withtoreadonly)
                {
                    print (! is_array($this->withto) && ! is_numeric($this->withto))?$this->withto:"";
                }
                else
                {
                    print "<input size=\"16\" id=\"sendto\" name=\"sendto\" value=\"".(! is_array($this->withto) && $this->withto != '1'? (isset($_REQUEST["sendto"])?$_REQUEST["sendto"]:$this->withto):"+")."\">";
                    if(! empty($this->withtosocid) && $this->withtosocid > 0)
                    {
                        $liste=array();
                        foreach($soc->thirdparty_and_contact_phone_array() as $key=>$value)
                        {
                            $liste[$key]=$value;
                        }
                        print " ".$langs->trans("or")." ";
                        //var_dump($_REQUEST);exit;
                        print $form->selectarray("receiver", $liste, GETPOST("receiver"), 1);
                    }
                    print ' '.$langs->trans("SmsInfoNumero");
                }
                print "</td></tr>\n";
            }
        }else{
            if ($this->withto) {
                print '<tr><td width="180">';
                //$moretext=$langs->trans("YouCanUseCommaSeparatorForSeveralRecipients");
                $moretext='';
                print $form->textwithpicto($langs->trans("SmsTo"),$moretext);
                print '</td><td>';
                
                print "<input type=\"hidden\" id=\"prevaction\" name=\"prevaction\" value=\"smsmulti\">";
                print '
                    <select id="sendto" name="sendto">
                        <option value="t">Tous les tiers</option>
                        <option value="p" '.(! is_array($this->withto) && $this->withto == 'p'? "selected":"").'>Tous les prospects</option>
                        <option value="c" '.(! is_array($this->withto) && $this->withto == 'c'? "selected":"").'>Tous les clients</option>
                        <option value="f" '.(! is_array($this->withto) && $this->withto == 'f'? "selected":"").'>Tous les fournisseurs</option>';
				if(! empty($conf->global->MAIN_MODULE_ADHERENT))
					print '<option value="a" '.(! is_array($this->withto) && $this->withto == 'a'? "selected":"").'>Tous les adhérents</option>';
				print '		
                    </select>
                ';
                
                print "</td></tr>\n";
            }
        }

        // Message
        if ($this->withbody)
        {
            $defaultmessage='';
            if ($this->param["models"]=='body')
            {
                $defaultmessage=$this->withbody;
            }
            $defaultmessage=make_substitutions($defaultmessage,$this->substit,$langs);
            if (isset($_POST["message"])) $defaultmessage=$_POST["message"];
            $defaultmessage=str_replace('\n',"\n",$defaultmessage);

            print "<tr>";
            print "<td width=\"180\" valign=\"top\">".$langs->trans("SmsText")."</td>";
            print "<td>";
            if ($this->withbodyreadonly)
            {
                print nl2br($defaultmessage);
                print '<input type="hidden" name="message" value="'.$defaultmessage.'">';
            }
            else
            {
                print '<textarea cols="40" name="message" id="message" rows="4" onkeyup="limitChars(this, 160, \'charlimitinfospan\')">'.$defaultmessage.'</textarea>';
                print '<div id="charlimitinfo">'.$langs->trans("SmsInfoCharRemain").': <span id="charlimitinfospan">'.(160-dol_strlen($defaultmessage)).'</span></div></td>';
            }
            print "</td></tr>\n";
        }

        print "</table>\n";

        print '<center>';
        print "<input class=\"button\" type=\"submit\" name=\"sendmail\" value=\"".$langs->trans("SendSms")."\"";
        print ">";
        if ($this->withcancel)
        {
            print " &nbsp; &nbsp; ";
            print "<input class=\"button\" type=\"submit\" name=\"cancel\" value=\"".$langs->trans("Cancel")."\">";
        }
        print "</center>\n";

        print "</form>\n";
        print "<!-- End form SMS -->\n";
    }
    
    
    function getContactPhoneListByCategory($type = "") {
        $db = $this->db;
		if($type == 'a') {
			$sql = "SELECT phone_mobile as phone";
			$sql.= " FROM ".MAIN_DB_PREFIX."adherent";
		} else {
			$sql = "SELECT p.phone ";
			$sql.= " FROM ".MAIN_DB_PREFIX."socpeople as p";
			$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = p.fk_soc";
			$sql.= ' WHERE  ';
			$sql.= " p.statut = 1 ";
	
			if ($type == "o")        // filtre sur type
			{
				$sql .= " AND p.fk_soc IS NULL";
			}
			else if ($type == "f")        // filtre sur type
			{
				$sql .= " AND s.fournisseur = 1";
			}
			else if ($type == "c")        // filtre sur type
			{
				$sql .= " AND s.client IN (1, 3)";
			}
			else if ($type == "p")        // filtre sur type
			{
				$sql .= " AND s.client IN (2, 3)";
			}
		}
        $result = $db->query($sql);
        // Count total nb of records
        $num = (int)$db->num_rows($result);


        $contact_list = array();
        if ($result){
            $i = 0;

            while ($i < $num) {
                $contact = $db->fetch_array($result);
                $contact_list[] = $contact["phone"];
                $i++;
            }

            $db->free($result);
        }
        //$db->close();

        //var_dump($contact_list);die;
        return $contact_list;
    }


}


<?php
	class Smspubli extends CommonObject{
		var $expe='';
		var $dest='';
		var $message='';
		var $deferred='';
		var $priority='';
		var $class='';
		var $error;
        var $timeDrift = 0;
		
		function Smspubli($DB) {
		
		}
		
		function SmsSenderList() {
			global $conf;
			$frm = new stdClass();
			$frm->number = $conf->global->SMSPUBLI_SMSFROM;
			return array($frm);
		}
		
		function SmsSend() {
			
			global $langs, $conf;
			$langs->load("smspubli@smspubli");
			$to = str_replace('+','00',$this->dest);
			if(!preg_match('/^[0-9]+$/', $to)) {
				$this->error = $langs->trans('errorRecipient');
				return 0;
			}
			$customsms= '';
			if($socid>0) {
				$customsms = 'socid='.$socid;
			}
			
			include_once('SMSpubliAPI.class.php');
			$url = 'https://api.gateway360.com/api/3.0';	
			$api = new SMSPubliApi($conf->global->SMSPUBLI_APIKEY, $url);
			$result = $api->post('/sms/send', array(
//				'"fake"'=>'"1"',
				'"messages"'=>'[{
					"from":"'.$this->expe.'",
					"to":"'.$to.'",
					"text":"'.$api->decode($this->message).'",
					"custom":"'.$customsms.'",
					"send_at":"'.dol_print_date(dol_now(),'standard').'"
				}]'
				)
			);
			
			if($result->code==1) {
				$this->error = $result->details;
				dol_syslog(get_class($this)."::SmsSend ".print_r($result->details, true), LOG_ERR);
				return 0;
			} else {
				return 1;
			}
		}

	}
?>

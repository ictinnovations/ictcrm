<?php
/* Copyright (C) 2017  Josep Lluís Amador <joseplluis@lliuretic.cat>
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
 * \defgroup    mymodule    MyModule module
 * \brief       MyModule module descriptor.
 *
 * Put detailed description here.
 */

/**
 * \file        core/modules/modSmsPubli.class.php
 * \ingroup     smspubli
 * \brief       Module for send SMS by SMSpubli.
 *
 * Put detailed description here.
 */

include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";


	
/**
 * Description and activation class for module SmsPubli
 */
class modSmsPubli extends DolibarrModules
{
	/** @var DoliDB Database handler */
	public $db;
	public $numero = 409000;
	public $rights_class = 'smspubli';
	public $family = 'interface';
	public $module_position = 500;
	public $name = "SMS Publi";	
	public $description = "Send SMS to your thirdparties around the world with smspubli.com";
	public $descriptionlong = "You can send SMS to your thirdparties around the world with SmsPubli<br />
	                           Create and account and start sending SMS with a very good price.";
	public $editor_name = "LliureTIC";
	public $editor_url = "https://www.lliuretic.cat";
	public $version = '1.0';
	public $const_name = 'MAIN_MODULE_SMSPUBLI';
	public $picto = 'email';


	/** @var array Define module parts */
	public $module_parts = array(
		'sms' => true,
		'triggers' => false,
		'login' => false,
		'substitutions' => false,
		'menus' => true,
		'theme' => true,
		'tpl' => true,
		'barcode' => true,
		'models' => true,
		'hooks' => array(),
		'dir' => array(),
		'workflow' => array(),
	);

	public $config_page_url = 'setup.php@smspubli';
	
	public $hidden = false; /** @var bool Control module visibility */
	public $depends = array(); /** @var string[] List of class names of modules to enable when this one is enabled */
	public $requiredby = array(); /** @var string[] List of class names of modules to disable when this one is disabled */
	public $conflictwith = array(); /** @var string List of class names of modules this module conflicts with */
	public $phpmin = array(5, 3); /** @var int[] Minimum PHP version required by this module */
	public $need_dolibarr_version = array(3, 2); /** @var int[] Minimum Dolibarr version required by this module */

	public $langfiles = array('smspubli@smspubli');
	
	
	/** @var array Indexed list of constants options */
	public $const = array(
	);
	
	//		0 => array(
	//		'MAIN_SMS_SENDMODE', /** @var string Constant name */
	//		'chaine',
	//		'smspubli', /** @var string Constant initial value */
	//		'SMS send mode with SMS Publi', /** @var string Constant description */
	//		false, /** @var bool Constant visibility */
	//		'current', /* Multi-company entities: 'current' or 'allentities' */
	//		false /** @var bool Delete constant when module is disabled */
	//	)
	
	public $tabs = array(
			0 => 'thirdparty:+sendsmspubli:SendSMS:smspubli@smspubli:$user->rights->smspubli->send:/smspubli/send.php?id=__ID__'
	);
	
	public $rights = array();
	public $menu = array();

	/** @var bool Module only enabled / disabled in main company when multi-company is in use */
	public $core_enabled = false;
	// @codingStandardsIgnoreEnd



	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		global $langs, $conf;
		// DolibarrModules is abstract in Dolibarr < 3.8
		if (is_callable('parent::__construct')) {
			parent::__construct($db);
		} else {
			global $db;
			$this->db = $db;
		}
		
		
		// Permissions		
		$this->rights[]=array(
			0 => 40900001,
			1 => 'Send a single SMS',
			3 => 0,
			4 => 'send'
		);
		
		$this->rights[]=array(
			0 => 40900002,
			1 => 'Send a massive SMS',
			3 => 0,
			4 => 'sendmulti'
		);
		
		
		// Menu entries
		$this->menu[]=array(
			'fk_menu'=>'fk_mainmenu=tools',
			'type'=>'left',
			'titre'=>'SendSMS',
			'mainmenu'=>'tools',
			'leftmenu'=>'smspubli',
			'url'=>'/smspubli/send.php',
			'langs'=>'smspubli@smspubli',
			'position'=>100,
			'enabled'=>'$conf->smspubli->enabled',
			'perms'=>'$user->rights->smspubli->send',
			'target'=>'',
			'user'=>0
		);

	}
	


	
	/**
	 * Function called when module is enabled.
	 * The init function add constants, boxes, permissions and menus
	 * (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		$sql = array();
		$result = $this->loadTables();
		return $this->_init($sql, $options);
	}
	
	
	
	
	/**
	 * Create tables, keys and data required by module
	 * Files llx_table1.sql, llx_table1.key.sql llx_data.sql with create table, create keys
	 * and create data commands must be stored in directory /mymodule/sql/
	 * This function is called by this->init
	 *
	 * @return int <=0 if KO, >0 if OK
	 */
	private function loadTables()
	{
		//return $this->_load_tables('/smspubli/sql/');
	}
	
	
	
	
	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param string $options Options when enabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();
		$sql[] = 'DELETE FROM '.MAIN_DB_PREFIX.'const WHERE name LIKE "MAIN_MODULE_SMSPUBLI%"';
		$sql[] = 'DELETE FROM '.MAIN_DB_PREFIX.'const WHERE name LIKE "SMSPUBLI%"';
		return $this->_remove($sql, $options);
	}
	
}

?>

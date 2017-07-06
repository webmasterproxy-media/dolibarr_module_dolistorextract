<?php
/* Copyright (C) - 2017    Jean-François Ferry    <jfefe@aternatik.fr>
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
* along with this program; if not, write to the Free Software
* Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/
/**
 *    \file       dolistorextract/class/actions_dolistorextract.class.php
*    \ingroup    dolistorextract
*    \brief      File Class dolistorextract
*/
//require_once "dolistorextract.class.php";
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
/**
 *    \class      ActionsTicketsup
 *    \brief      Class Actions of the module ticketsup
 */
class ActionsDolistorextract
{
	public $db;
	public $dao;
	public $mesg;
	public $error;
	public $errors = array();
	//! Numero de l'erreur
	public $errno = 0;
	public $template_dir;
	public $template;
	

	/**
	 *    Constructor
	 *
	 *    @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Hook to add email element template
	 *
	 * @param array 		$parameters
	 * @param Object 		$object
	 * @param string 		$action
	 * @param HookManager 	$hookmanager
	 * @return int
	 */
	public function emailElementlist($parameters, &$object, &$action, $hookmanager)
	{
		global $langs;
		 
		$error = 0;
		 
		if (in_array('admin', explode(':', $parameters['context']))) {
			$this->results = array('dolistore_extract' => $langs->trans('DolistorextractMessageToSendAfterDolistorePurchase'));
		}
		 
		if (! $error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	
	}
	
	/**
	 * Create a new customer with email datas
	 *
	 * @param User $user
	 * @param dolistoreMail $dolistoreMail
	 * @return int ID of created customer
	 */
	public function newCustomerFromDatas(User $user, dolistoreMail $dolistoreMail)
	{
		$socStatic = new Societe($this->db);
	
		if (empty($dolistoreMail->invoice_company) || empty($dolistoreMail->email)) {
			return -1;
		}
		$socStatic->name = $dolistoreMail->invoice_company;
		$socStatic->firstname = $dolistoreMail->invoice_firstname;
		$socStatic->lastname = $dolistoreMail->invoice_lastname;
		$socStatic->address = $dolistoreMail->invoice_address1;
		$socStatic->zip = $dolistoreMail->invoice_postal_code;
		$socStatic->town = $dolistoreMail->invoice_city;
		$socStatic->phone = $dolistoreMail->invoice_phone;
		$socStatic->email = $dolistoreMail->email;
		$socStatic->country_code = $dolistoreMail->invoice_country;
		$socStatic->state = $dolistoreMail->invoice_state;
		$socStatic->multicurrency_code = $dolistoreMail->currency;
	
		$socStatic->client = 2; // Prospect / client
		$res = $socStatic->create($user);
		var_dump($socStatic->errors);
		return $res;
	}
	
	/**
	 * Ajoute le client $socid dans la catégorie correspondante au module $productRef
	 *
	 * Les categories doivent avoir un champ extrafield `ref_dolistore`
	 *
	 * @uses searchCategoryDolistore()
	 * @param string $productRef Product reference
	 * @param int $socid ID of company
	 */
	public function setCustomerCategoryFromOrder($productRef, $socid)
	{
		$socStatic = new Societe($this->db);
		$catStatic = new Categorie($this->db);
	
		$catStatic->id = $this->searchCategoryDolistore($productRef);
	
		if ($catStatic->id > 0 && $socStatic->fetch($socid)) {
			return $catStatic->add_type($socStatic, 'customer');
		} else {
			return -1;
		}
		return 0;
	}
	
	/**
	 * Search a category with extrafield `ref_dolistore` value
	 *
	 * @param string $productRef
	 * @return int Category ID or -1 if error
	 */
	private function searchCategoryDolistore($productRef)
	{
		$sql = "SELECT fk_object FROM ".MAIN_DB_PREFIX."categories_extrafields WHERE ref_dolistore='".$productRef."'";
	
		dol_syslog(__METHOD__, LOG_DEBUG);
		$resql = $this->db->query($sql);
		$result = 0;
		if ($resql) {
			if ($this->db->num_rows($resql)) {
				$obj = $this->db->fetch_object($resql);
				$return = $obj->fk_object;
			}
			$this->db->free($resql);
			return $return;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);
			return -1;
		}
	}
	
	/**
	 * Create an event
	 *
	 * @param array $extractDatas Array fil
	 * @param string $socid
	 * @return number
	 */
	public function createEventFromExtractDatas($extractProductDatas, $socid)
	{
		global $conf;
	
		// Check value
		if (empty($extractDatas['order_name']) ||_empty($extractDatas['item_reference'])) {
			dol_syslog(__METHOD__.' Error : params order_name nnd product_ref missing');
			return -1;
		}
	
		$userStatic = new User($this->db);
		$user->fetch($conf->global->DOLISTOREXTRACT_USER_FOR_ACTIONS);
	
		require_once DOL_DOCUMENT_ROOT.'/comm/actions/class/actioncomm.class.php';
		$actionStatic = new ActionComm($this->db);
	
		$actionStatic->authorid = $conf->global->DOLISTOREXTRACT_USER_FOR_ACTIONS;
		$actionStatic->userownerid = $conf->global->DOLISTOREXTRACT_USER_FOR_ACTIONS;
	
		$actionStatic->code = 'AC_STRXTRACT';
		$actionStatic->label = $langs->trans('DolistorextractLabelActionForSale', $productRef);
		// Define a tag which allow to detect twice
		$actionStatic->note = 'ORDER:'.$extractDatas['order_name'].':'.$extractDatas['product_ref'];
	
		return $actionStatic->create($userStatic);
	}
}
<?php
/* Functionnal tests for module Dolistorextract
 * Copyright (C) 2017  Jean-François Ferry	<jfefe@aternatik.fr>
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
 * \file    test/unit/DolistorextractTest.php
 * \ingroup dolistorextract
 * \brief   PHPUnit tests for module DolistorExtract.
 *
 * Functionnal tests for module Dolistorextract
 */

namespace test\unit;

global $conf,$user,$langs,$db;
//define('TEST_DB_FORCE_TYPE','mysql');	// This is to force using mysql driver
//require_once 'PHPUnit/Autoload.php';
$res = false;
if (file_exists(dirname(__FILE__).'/../../../../htdocs/master.inc.php')) {
	$res = require_once dirname(__FILE__).'/../../../../htdocs/master.inc.php';
} elseif (file_exists(dirname(__FILE__).'/../../../../../htdocs/master.inc.php')) {
	$res = require_once dirname(__FILE__).'/../../../../../htdocs/master.inc.php';
} else {
	die('Include of mains fails');
}
require_once dirname(__FILE__).'/../../class/dolistoreMail.class.php';
require_once dirname(__FILE__).'/../../class/dolistoreMailExtract.class.php';

use PHPUnit\Framework\TestCase;

if (empty($user->id)) {
	print "Load permissions for admin user nb 1\n";
	$user->fetch(1);
	$user->getrights();
}
$conf->global->MAIN_DISABLE_ALL_MAILS=1;

/**
 * Class DolistorextractTest
 * @package test\unit
 */
class DolistoreMailExtractTest extends TestCase
{
	
	protected $savconf;
	protected $savuser;
	protected $savlangs;
	protected $savdb;
	

	/**
	 * Global test setup
	 */
	public static function setUpBeforeClass()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
		
	}

	/**
	 * Unit test setup
	 */
	protected function setUp()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
		
		global $conf,$user,$langs,$db;
		
		$this->savconf=$conf;
		$this->savuser=$user;
		$this->savlangs=$langs;
		$this->savdb=$db;
		
		fwrite(STDOUT, __METHOD__ ." db->type=".$db->type." user->id=".$user->id."\n");
	}

	/**
	 * Verify pre conditions
	 */
	protected function assertPreConditions()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * Verify post conditions
	 */
	protected function assertPostConditions()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * Unit test teardown
	 */
	protected function tearDown()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}
	
	/**
	 * testExtractDatas
	 *
	 * @return	array
	 */
	public function testExtractOrderDatas()
	{
		//global $conf, $user, $langs, $db;
	
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;
	
		$html = file_get_contents(dirname(__FILE__).'/../ex_info_produit.html');
	
		$dolistoreMailExtract = new \dolistoreMailExtract($db, $html);
		$datas = $dolistoreMailExtract->extractOrderDatas();
		
		// Check if result array contain correct keys
		foreach (\dolistoreMailExtract::ARRAY_EXTRACT_TAGS as $key) {
			fwrite(STDOUT, __METHOD__." ".$key."=".$datas[${key}]."\n");
			$this->assertArrayHasKey($key, $datas, 'test '.$key);
		}
		
		return $datas;
	}
	
	/**
	 * testExtractProductsDatas
	 * 
	 * @return array
	 */
	public function testExtractProductsDatas()
	{
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;
		
		$html = file_get_contents(dirname(__FILE__).'/../ex_info_produit.html');
		
		$dolistoreMail = new \dolistoreMailExtract($db, $html);
		// Extract product data
		$lines = $dolistoreMail->extractProductsData();
		
		foreach ($lines as $data) {			
				// Check if result array contain correct keys
				foreach (\dolistoreMailExtract::ARRAY_EXTRACT_TAGS_PRODUCT as $key) {
					fwrite(STDOUT, __METHOD__." ".$key."=".$data[${key}]."\n");
					$this->assertArrayHasKey($key, $data, 'test '.$key);
				}
		}
		return $lines;
	}
	
	/**
	 * testExtractDatas
	 *
	 * @return	array
	 */
	public function testExtractAllDatas()
	{
		//global $conf, $user, $langs, $db;
	
		$conf=$this->savconf;
		$user=$this->savuser;
		$langs=$this->savlangs;
		$db=$this->savdb;
	
		$html = file_get_contents(dirname(__FILE__).'/../ex_info_produit.html');
	
		$dolistoreMail = new \dolistoreMailExtract($db, $html);
		$datas = $dolistoreMail->extractAllDatas();
	
		// Check if result array contain correct keys
		foreach (\dolistoreMailExtract::ARRAY_EXTRACT_TAGS as $key) {
			fwrite(STDOUT, __METHOD__." ".$key."=".$datas[${key}]."\n");
			$this->assertArrayHasKey($key, $datas, 'test '.$key);
		}
	
		return $datas;
	}
	
	

	/**
	 * Global test teardown
	 */
	public static function tearDownAfterClass()
	{
		fwrite(STDOUT, __METHOD__ . "\n");
	}

	/**
	 * Unsuccessful test
	 *
	 * @param \Throwable $t
	 * @throws \Throwable
	 */
	protected function onNotSuccessfulTest(\Throwable $t)
	{
		fwrite(STDOUT, __METHOD__ . "\n");
		throw $t;
	}
	
	
}
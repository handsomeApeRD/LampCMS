<?php
/**
 *
 * License, TERMS and CONDITIONS
 *
 * This software is lisensed under the GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * Please read the license here : http://www.gnu.org/licenses/lgpl-3.0.txt
 *
 *  Redistribution and use in source and binary forms, with or without
 *  modification, are permitted provided that the following conditions are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. Redistributions in binary form must reproduce the above copyright
 *    notice, this list of conditions and the following disclaimer in the
 *    documentation and/or other materials provided with the distribution.
 * 3. The name of the author may not be used to endorse or promote products
 *    derived from this software without specific prior written permission.
 *
 * ATTRIBUTION REQUIRED
 * 4. All web pages generated by the use of this software, or at least
 * 	  the page that lists the recent questions (usually home page) must include
 *    a link to the http://www.lampcms.com and text of the link must indicate that
 *    the website's Questions/Answers functionality is powered by lampcms.com
 *    An example of acceptable link would be "Powered by <a href="http://www.lampcms.com">LampCMS</a>"
 *    The location of the link is not important, it can be in the footer of the page
 *    but it must not be hidden by style attibutes
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR "AS IS" AND ANY EXPRESS OR IMPLIED
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE FREEBSD PROJECT OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
 * THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This product includes GeoLite data created by MaxMind,
 *  available from http://www.maxmind.com/
 *
 *
 * @author     Dmitri Snytkine <cms@lampcms.com>
 * @copyright  2005-2011 (or current year) ExamNotes.net inc.
 * @license    http://www.gnu.org/licenses/lgpl-3.0.txt GNU LESSER GENERAL PUBLIC LICENSE (LGPL) version 3
 * @link       http://www.lampcms.com   Lampcms.com project
 * @version    Release: @package_version@
 *
 *
 */

namespace Lampcms\Controllers;

use \Lampcms\String;

class Changepwd extends Resetpwd
{
	const TPL_SUCCESS = 'Password updated successfully';

	protected $membersOnly = true;
	
	protected $aRequired = array();
	
	protected $layoutID = 1;

	/**
	 * @var object of type Form
	 */
	protected $oForm;

	protected $aAllowedVars = array('current', 'pwd1', 'pwd2');


	/**
	 * New password
	 *
	 * @var string
	 */
	protected $newPwd;


	protected function main(){

		$this->oForm = new \Lampcms\Forms\Changepwd($this->oRegistry);
		$this->oForm->formTitle = $this->aPageVars['title'] = 'Change password';

		if($this->oForm->isSubmitted() && $this->oForm->validate()){
			$this->saveNewPassword();
			if(!empty($this->email)){
				$this->emailPwd();
			}

			$this->aPageVars['body'] = '<div class="message">'.self::TPL_SUCCESS.'</div>';

		} else {
			$this->aPageVars['body'] = $this->oForm->getForm();
		}
	}


	/**
	 * Update ['pwd'] in Viewer object and save object
	 *
	 * @return object $this
	 */
	protected function saveNewPassword(){
		$this->email = $this->oRegistry->Viewer['email'];
		$this->username = $this->oRegistry->Viewer['username'];
		$this->newPwd = $this->oRequest->pwd1;

		$this->oRegistry->Viewer['pwd'] = String::hashPassword($this->newPwd);
		$this->oRegistry->Viewer->save();

		return $this;
	}

}
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


namespace Lampcms;

class UserTagsBlock extends LampcmsObject
{

	/**
	 *
	 * Renders block with user tags
	 *
	 * @todo get Viewer from Registry and if NOT
	 * the same as oUser then get array intersection
	 * and show you have these 'tags' in common
	 *
	 * @param object $oRegistry Registry object
	 * @param object $oUser User object
	 */
	public static function get(Registry $oRegistry, User $oUser){
		$uid = $oUser->getUid();
		$aTags = $oRegistry->Mongo->getCollection('USER_TAGS')
		->findOne(array('_id' => $uid));

		if(empty($aTags) || empty($aTags['tags'])){
			d('no tags for user: '.$uid);

			return '';
		}

		$userTags = $aTags['tags'];
		d('$userTags: '.print_r($userTags, 1));


		$tags = '';
		foreach($userTags as $tag => $count){
			$tags .= \tplUserTag::parse(array($tag, $count), false);
		}
		d('tags: '.$tags);

		/**
		 * @todo translate string
		 */
		$vals = array('count' => count($userTags), 'label' => 'tag', 'tags' => $tags);
		d('vals: '.print_r($vals, 1));

		$ret = \tplUserTags::parse($vals)."<!-- // UserTagsBlock::get -->\n";

		d('ret: '.$ret);

		return $ret;
	}


	/**
	 * @todo finish this
	 *
	 * Finds and parses common tags a Viewer has
	 * with User whos profile user is viewing
	 *
	 * @param User $oViewer
	 * @param array $userTags
	 */
	public static function getCommonTags(User $oViewer, array $userTags){

		$uid = $oViewer->getUid();
		if(0 === $uid){
			return '';
		}

		$aTags = $oRegistry->Mongo->getCollection('USER_TAGS')
		->findOne(array('_id' => $uid));

		if(empty($aTags) || empty($aTags['tags'])){
			d('no tags for user: '.$uid);

			return '';
		}

		$viewerTags = $aTags['tags'];

		$aCommon = array_intersect_key($viewerTags, $userTags);
		d('aCommon: '.print_r($aCommon, 1));

		if(empty($aCommon)){
			d('no common tags');

			return '';
		}




	}
}

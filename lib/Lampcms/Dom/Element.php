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
 *    the website\'s Questions/Answers functionality is powered by lampcms.com
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


namespace Lampcms\Dom;

class Element extends \DOMElement implements \Lampcms\Interfaces\LampcmsObject
{

	/**
	 * Remove all next siblings of this node
	 * 
	 * This works OK
	 *
	 * @return object $this node
	 */
	public function removeNextSiblings(\DOMNode $node = null){
		$node = ($node) ? $node : $this->nextSibling;
		if($node){			
			$next = $node->nextSibling;
			if($next){
				$this->removeNextSibling($next);
			}

			$node->parentNode->removeChild($node);
		}
		
		return $this;
	}

	/**
	 * Remove itself from DOM Tree
	 *
	 * This works fine
	 *
	 */
	public function remove(){
		$this->parentNode->removeChild($this);
	}


	/**
	 * Remove all child nodes of this node
	 * if child nodes exist
	 *
	 * This works fine
	 *
	 * @return object $this
	 */
	public function removeChildNodes()
	{
		$len = $this->childNodes->length;
		while($len > 0){
			$this->removeChild($this->firstChild);
			$len--;
		}

		return $this;
	}


	/**
	 *
	 * Append child node with name $nodename
	 * and under it append CData section using
	 * contents of $cdata as CData value
	 * 
	 * This method was created specifically to add the 
	 * value of content:encoded section to the rss feed.
	 * 
	 * 
	 * @todo probably better to NOT add extra node first
	 * and instead just directly add CDATA section to this element?
	 *
	 * @param string $nodename
	 * @param string $cdata
	 * @param string $ns
	 */
	public function addCData($nodename, $cdata, $ns = 'http://purl.org/rss/1.0/modules/content/')
	{
		if(empty($cdata)){
			return $this;
		}

		$elChild = $this->appendChild(new self($nodename, '', $ns));
		$elCdata = $this->ownerDocument->createCDATASection($cdata);
		$elChild->appendChild($elCdata);

		return $this;
	}


	/**
	 * (non-PHPdoc)
	 * @see Lampcms\Interfaces.LampcmsObject::hashCode()
	 */
	public function hashCode(){
		return spl_object_hash($this);
	}


	/**
	 * (non-PHPdoc)
	 * @see Lampcms\Interfaces.LampcmsObject::getClass()
	 */
	public function getClass(){
		return get_class($this);
	}


	/**
	 * (non-PHPdoc)
	 * @see Lampcms\Interfaces.LampcmsObject::__toString()
	 */
	public function __toString(){
		return 'Object of type '.$this->getClass().' nodeName: '.$this->nodeName.' value: '.$this->nodeValue;
	}
}
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

 
/**
 * All interfaces conbined into this one file
 * 
 * @important Allways include this file! Don't rely
 * of autoloader to include it!
 */
 
namespace Lampcms\Interfaces;


interface Cache
{

	/**
	 * Get value of a single cache key
	 * @param string $key
	 * @return unknown_type
	 */
	public function get($key);

	/**
	 * Get value for array of keys
	 * @param array $aKeys
	 * @return unknown_type
	 */
	public function getMulti(array $aKeys);

	/**
	 * Puts a value of $key into cache for $ttl seconds
	 * @param $key
	 * @param $value
	 * @param $ttl
	 * @return unknown_type
	 */
	public function set($key, $value, $ttl = 0, array $tags = null);


	public function setMulti(array $aItems, $ttl = 0);

	/**
	 * Deletes key from cache
	 * @param string $key
	 * @param integer $ttl time in seconds after which to remove the key
	 * @return void
	 */
	public function delete($key, $exptime = 0);

	/**
	 * Increment numeric value of $key
	 *
	 * @param $key
	 * @param $int
	 * @return unknown_type
	 */
	public function increment($key, $int = 1);


	/**
	 * Decrement numeric value of $key
	 * @param string $key
	 * @param int $int
	 */
	public function decrement($key, $int = 1);

	/**
	 * Removes all data from cache
	 * @return void
	 */
	public function flush();

	public function __toString();
}

/**
 * Basic object interface
 * must implement at least these 3 methods
 *
 * @author Dmitri Snytkine
 *
 */
interface LampcmsObject
{
	public function hashCode();

	public function getClass();

	public function __toString();

}

/**
 * Resource Interface
 * modeled after Zend_Resource_Interface
 *
 */
interface Resource
{
	/**
	 * Returns the string identifier of the Resource
	 *
	 * @return string
	 */
	public function getResourceId();
}


/**
 * clsUserObject implements this, thus
 * this interface in this file, because
 * we know 100% that it will be used with every page request
 *
 */
interface RoleInterface
{
	/**
	 * Returns the string identifier of the Role
	 *
	 * @return string
	 */
	public function getRoleId();
}


/**
 * @copyright  Copyright (c) 2005-2009 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Assert
{
	/**
	 * Returns true if and only if the assertion conditions are met
	 *
	 * This method is passed the ACL, Role, Resource, and privilege to which the authorization query applies. If the
	 * $role, $resource, or $privilege parameters are null, it means that the query applies to all Roles, Resources, or
	 * privileges, respectively.
	 *
	 * @param  Acl  $acl
	 * @param  AclRole     $role
	 * @param  Resource $resource
	 * @param  string  $privilege
	 * @return boolean
	 */
	public function assert(\Lampcms\Acl\Acl $acl, RoleInterface $role = null, Resource $resource = null, $privilege = null);
}

/**
 *
 * Every resource like blog post, album,
 * even a single image or blog comment
 * should implement this interface
 *
 * Even the page controller (currently viewed page object)
 * should implement this interface so that we can
 * determine which user the object (including a page,
 * in which case it goes by the owner of the blog)
 * belongs to
 *
 * @author Dmitri Snytkine
 *
 */
interface LampcmsResource extends Resource
{
	
	/**
	 * Returns id from RESOURCE_TYPE table
	 * this way we can find out what type of resource this
	 * is and then lookup on RESOURCE_TYPE table
	 * Things like resource type name,
	 * possibly the name of table where ratings for this
	 * resource are stored, where the comments
	 * for this resource are stored, etc.
	 *
	 * @return int
	 */
	public function getResourceTypeId();

	/**
	 * Returnes id of user (USERS.id)
	 * who owns the resource
	 * Which is usually the user who created it
	 * but doest not have to be.
	 * It is up to the individual class
	 * to decide who owns the resource.
	 *
	 * @return int
	 */
	public function getOwnerId();

	/**
	 * Get unix timestamp of
	 * when resource was last modified
	 * This includes any type of change made to a
	 *
	 * resource, including when new comments were added
	 * or new rating added ,etc.
	 *
	 * It's up to the implementer to decide what changes
	 * are significant enough to be considered modified
	 * but usually the on update CURRENT TIMESTAMP
	 * is a very good way to mark resouce as modified
	 *
	 * @return int last modified time in unix timestamp
	 *
	 */
	public function getLastModified();

	/**
	 * Every resource when deleted actually has
	 * a 'deleted' timestamp set
	 * to the time when it was deleted.
	 * This way we consider a resource as deleted
	 * but can also find and possibly show
	 * the date/time when resource was deleted
	 * and also have the option to 'undelete'
	 * by just switching the 'deleted' column value
	 * back to 0
	 *
	 * @return int
	 * 0 means not deleted or unix timestamp of when resource
	 * was marked as deleted
	 */
	public function getDeletedTime();

}

/**
 * A Resource that may have comments
 * For example a blog post
 * but can also be any other type of resource
 * like a news feed article
 * or forum feed article
 *
 * @author Dmitri Snytkine
 *
 */
interface CommentedResource extends Resource
{

	/**
	 * For blog posts
	 * (just an example) a blog owner can decide to close
	 * comments on the blog post
	 * The value will be unix timestamp
	 * of when resource comments have ended.
	 * The default value of 0 means
	 * comments are allowed, provided
	 * all other accertions are true.
	 *
	 * @return string 'none' means comments not allowed,
	 * 'all' means from all users, including anonymous,
	 * 'members' means only from logged in members,
	 * 'friends' means only from friends
	 */
	public function getCommentsPermission();

	/**
	 * Get total number of comments
	 * This one does not have to be 100% accurate,
	 * but should be as accurate as possible.
	 * This info will be displayed next to the
	 * total comments on blog page
	 *
	 * @return int
	 */
	public function getCommentsCount();

	/**
	 * Increase comments count by 1
	 * which will also reset last activity
	 *
	 * This is hard to implement in same object
	 * as actual message because it does not belong
	 * in the same table. Better to not increase comments count directly
	 * and just do it by directly increasing
	 * count in database table RESOURCE (for example) and
	 * then cache observer will unset the bp_ (blog post)
	 *
	 * @return object $this
	 */
	public function increaseCommentsCount();
}

/**
 * Resource that can be rated in the UP/Down fashion
 * like 'did you find this message helpful?'
 * or did you like this picture 'yes/no'?
 *
 * @author Dmitri Snytkine
 *
 */
interface UpDownRatable
{

	/**
	 *
	 * @param int $inc could be 1 for vote
	 * or -1 for 'unvote'
	 */
	public function addUpVote($inc = 1);

	/**
	 *
	 *
	 * @param int $inc could be 1 for vote
	 * or -1 for 'unvote'
	 */
	public function addDownVote($inc = 1);

	/**
	 * If resource has the UP/DOWN vote
	 * capability then return array with keys
	 * up=>countUpVotes
	 * down=>countDownVotes
	 *
	 * A resource that does not implement up/down voting
	 * should return an empty array or null
	 *
	 * @return array
	 */
	public function getVotesArray();

	/**
	 * Get total score which is usually
	 * a combination of up votes - down votes
	 *
	 * @return int
	 */
	public function getScore();

}

/**
 * User interface
 * every user has userID which is unique
 * except for a case of guest - all guests
 * have userID of 0
 *
 * @author Dmitri Snytkine
 *
 */
interface User
{
	/**
	 * Get id from USERS table
	 * it may also return 0 if id not available
	 * like when user is not logged in
	 * or when object is not an actual user per say
	 *
	 * @return int
	 */
	public function getUid();
}

/**
 * A Blog message is either an
 * original blog post
 * OR a comment to a blog
 * Each blog message must have a body
 * and userID of user who posted the message
 * and id of blog to which a message belongs
 * This is because a message can be posted
 * by someone who is not the owner of the blog,
 * for example a user may have permission
 * to post to company blog
 *
 * There are other important data for
 * each message like timestamp,
 * subject (title) and other things
 * but we must at least have these most
 * important parts of the message
 *
 * @author Dmitri Snytkine
 *
 */
interface BlogMessage
{
	/**
	 * Get the body of the message
	 *
	 * @return string body of this message
	 */
	public function getBody();

	/**
	 * Set $string to be the body of message
	 * possibly replacing already existing one
	 *
	 * @param string $string
	 * @return usually object $this
	 */
	public function setBody($string);

	/**
	 *
	 * @return int id of blog to which this message belongs
	 */
	public function getBlogId();
}


/**
 * Twitter user
 * user who had signed in with Twitter
 *
 * @author Dmitri Snytkine
 *
 */
interface TwitterUser
{
	/**
	 * Get oAuth token
	 * that we got from Twitter for this user
	 * @return string
	 */
	public function getTwitterToken();

	/**
	 * Get oAuth sercret that we got for this user
	 * @return string
	 */
	public function getTwitterSecret();

	/**
	 * Get twitter user_id
	 * @return int
	 */
	public function getTwitterUid();

	public function getTwitterUsername();

	public function revokeOauthToken();
}


interface FacebookUser
{
	public function revokeFacebookConnect();
	
	public function getFacebookUid();
	
	public function getFacebookToken();
}

interface Question extends LampcmsResource
{
	/**
	 * Should return false if NOT closed
	 * otherwise either true or timestamp
	 * of when it was closed
	 */
	public function isClosed();

	public function getAnswerCount();

	/**
	 * Set time, reason for when question was closed
	 * as well as username and userid of user who closed it
	 *
	 * @param int $timestamp
	 * @param string $reason
	 * @param object $closer user who closed the question
	 */
	public function setClosed($timestamp, $reason, clsUserObject $closer);

	/**
	 * Must set the id of best_answer
	 *
	 * @param int $qid id of answer
	 */
	public function setBestAnswer($qid);

	public function increaseAnswerCount($int = 1);

}

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

/**
 * Class responsible for adding a new question
 * to QUESTIONS collection as well as updating
 * all Tags-related collections as well as increasing
 * count of user questions and updating per-user tags.
 *
 * This class does everything that has to be done
 * when new questions is submitted, regardless of how
 * it was submitted. It accepts an object of type
 * clsSubmittedQuestion which may be sub-classed to work with
 * many different ways question can be submitted: web, api, email, etc.
 *
 * @author Dmitri Snytkine
 *
 */
class QuestionParser extends LampcmsObject
{

	/**
	 * Object of type clsSubmittedQuestion
	 * (or any sub-class of it)
	 *
	 * @var Object SubmittedQuestion
	 */
	protected $oSubmitted;

	/**
	 * New question object
	 * created
	 *
	 * @var object of type Question
	 */
	protected $oQuestion;

	public function __construct(Registry $oRegistry){
		$this->oRegistry = $oRegistry;
		$this->oRegistry->registerObservers('INPUT_FILTERS');
	}

	/**
	 * Getter for submitted object
	 * This can be used from observer object
	 * like spam filter so that via oSubmitted
	 * it's possible to call getUserObject()
	 * and get user object of question submitter, then
	 * look at some personal stats like reputation score,
	 * usergroup, etc.
	 *
	 * @return object of type SubmittedQuestion
	 */
	public function getSubmitted(){

		return $this->oSubmitted;
	}

	/**
	 * Main entry method to start processing
	 * the submitted question
	 *
	 * @param object $o object SubmittedQuestion
	 */
	public function parse(SubmittedQuestion $o){

		$this->oSubmitted = $o;

		$this->makeQuestion()
		->addTitleTags()
		->addTags()
		->addUnansweredTags()
		->addRelatedTags()
		->addUserTags();

		d('cp parsing done, returning question');

		return $this->oQuestion;
	}

	
	/**
	 *
	 * @todo later can also add spam filter via observer!
	 *
	 * @todo post onNewQuestion() event
	 * This will actually be posted from MongoDoc onCollectionInsert
	 *
	 * @return object $this
	 *
	 * @throws QuestionParserException in case a filter (which is an observer)
	 * either throws a FilterException (or sub-class of it) OR just cancells event
	 *
	 */
	protected function makeQuestion(){

		/**
		 * Is this protection necessary?
		 * Seems that without it
		 * a title may have html tags
		 * or something like a comment:
		 * <!--more-->
		 *
		 * @var unknown_type
		 */
		$title = $this->oSubmitted->getTitle()->htmlentities()->valueOf();

		$username = $this->oSubmitted->getUserObject()->getDisplayName();

		$aTags = $this->oSubmitted->getTags();

		$oBody = $this->oSubmitted->getBody()->makeClickable()->tidy()->safeHtml()->asHtml();

		/**
		 *
		 * Now body is in html but we still need to run
		 * it through Utf8Html string in order
		 * to make clickable links and to
		 * make sure all links are nofollow
		 *
		 */

		/**
		 * Not sure why we need to run the string through
		 * DomFeedItem now.
		 * What it does is fixes potentially bad html fragment
		 * and also makes sure all links are "nofollow"
		 * it also helpful
		 * to guard agains any type of hacks
		 *
		 * At that time we should also add safeHtml() to oClickable
		 * and pass array of our allowed tags
		 *
		 */
		$htmlBody = DomFeedItem::loadFeedItem($oBody)->getFeedItem();
		d('after DomFeedItem: '.$htmlBody);

		$uid = $this->oSubmitted->getUserObject()->getUid();
		$hash = hash('md5', strtolower($htmlBody.json_encode($aTags)));

		/**
		 * @todo can parse forMakrdown now but ideally
		 * parseMarkdown() would be done inside Utf8string
		 * as well as parseSmilies
		 *
		 * @todo later can also parse for smilies here
		 *
		 */
		$this->checkForDuplicate($uid, $hash);

		$username = $this->oSubmitted->getUserObject()->getDisplayName();
		
		/**
		 *
		 * @var array
		 */
		$aData = array(
		'_id' => $this->oRegistry->Resource->create('QUESTION'),
		'title' => $title,
		/*'title_hash' => hash('md5', strtolower(trim($title)) ),*/
		'b' => Bodytagger::highlight($htmlBody, $aTags),
		'hash' => $hash,
		'intro' => $this->oSubmitted->getBody()->asPlainText()->truncate(150)->valueOf(),
		'url' => $this->oSubmitted->getTitle()->toASCII()->makeLinkTitle()->valueOf(),
		'i_words' => $this->oSubmitted->getBody()->getWordsCount(),
		'i_uid' => $uid,
		'username' => $username,
		'ulink' => '<a href="'.$this->oSubmitted->getUserObject()->getProfileUrl().'">'.$username.'</a>',
		'avtr' => $this->oSubmitted->getUserObject()->getAvatarSrc(),
		'i_up' => 0,
		'i_down' => 0,
		'i_votes' => 0,
		'i_favs' => 0,
		'i_views' => 0,
		'a_tags' => $aTags,
		'status' => 'unans',
		'tags_c' => trim(\tplQtagsclass::loop($aTags, false)),
		'tags_html' => \tplQtags::loop($aTags, false),
		'credits' => '',
		'i_ts' => time(),
		'hts' => date('F j, Y g:i a T'),
		'i_lm_ts' => time(),
		'i_ans' => 0,
		'ans_s' => 's',
		'v_s' => 's',
		'f_s' => 's',
		'ip' => $this->oSubmitted->getIP(),
		'app' => 'web'
		);

		/**
		 * Submitted question object may provide
		 * extra elements to be added to aData array
		 * This is usually useful for parsing questions that
		 * came from external API, in which case the answered/unanswred
		 * status as well as number of answers is already known
		 *
		 * as well as adding 'credit' div
		 */
		$aExtraData = $this->oSubmitted->getExtraData();
		d('$aExtraData: '.print_r($aExtraData, 1));

		$aData = array_merge($aData, $aExtraData);
		$this->oQuestion = new Question($this->oRegistry, $aData);

		/**
		 * Post onBeforeNewQuestion event
		 * and watch for filter either cancelling the event
		 * or throwing FilterException (prefferred way because
		 * a specific error message can be passed in FilterException
		 * this way)
		 *
		 * In either case we throw QuestionParserException
		 * Controller that handles the question form should be ready
		 * to handle this exception and set the form error using
		 * message from exception. This way the error will be shown to
		 * the user right on the question form while question form's data
		 * is preserved in form.
		 * 
		 * Filter can also modify the data in oQuestion before
		 * it is saved. This is convenient, we can even set different
		 * username, i_uid if we want to 'post as alias'
		 */
		try {
			$oNotification = $this->oRegistry->Dispatcher->post($this->oQuestion, 'onBeforeNewQuestion');
			if($oNotification->isNotificationCancelled()){
				throw new QuestionParserException('Sorry, we are unable to process your question at this time.');
			}
		} catch (FilterException $e){
			e('Got filter exteption: '.$e->getFile().' '.$e->getLine().' '.$e->getMessage().' '.$e->getTraceAsString());
			throw new QuestionParserException($e->getMessage());
		}

		/**
		 * Do ensureIndexes() now and not before we are sure that we even going
		 * to add a new question.
		 */
		$this->ensureIndexes();
		
		$this->oQuestion->insert();

		$this->oRegistry->Dispatcher->post($this->oQuestion, 'onNewQuestion');

		return $this;
	}

	
	/**
	 * Ensure indexes in all collections involved
	 * in storing question data
	 *
	 * @return object $this
	 */
	protected function ensureIndexes(){
		$quest = $this->oRegistry->Mongo->getCollection('QUESTIONS');
		$quest->ensureIndex(array('i_ts' => 1));
		$quest->ensureIndex(array('i_votes' => 1));
		$quest->ensureIndex(array('i_ans' => 1));
		$quest->ensureIndex(array('a_tags' => 1));
		$quest->ensureIndex(array('i_uid' => 1));
		$quest->ensureIndex(array('hash' => 1));
		$quest->ensureIndex(array('ip' => 1));

		return $this;
	}

	
	/**
	 * Check to see if same user has already posted
	 * exact same question
	 *
	 * @todo translate the error message
	 *
	 * @param int $uid
	 * @param string $hash hash of question body
	 */
	protected function checkForDuplicate($uid, $hash){
		$a = $this->oRegistry->Mongo->getCollection('QUESTIONS')->findOne(array('i_uid' => $uid, 'hash' => $hash ));
		if(!empty($a)){
			$err = 'You have already asked exact same question  <span title="'.$a['hts'].'" class="ts" rel="time">on '.$a['hts'].
			'</span><br><a class="link" href="/questions/'.$a['_id'].'/'.$a['url'].'">'.$a['title'].'</a><br>
			You cannot post the same exact question twice';

			throw new QuestionParserException($err);
		}
	}

	
	/**
	 * Tokenize title and save into TITLE_TAGS
	 * and also save into MySQL QUESTION_TITLE table
	 * 
	 * @todo do this via shutdown function
	 * 
	 * @return object $this
	 */
	protected function addTitleTags(){

		$oTitleTagParser = new Qtitletags($this->oRegistry);
		$oTitleTagParser->parse($this->oQuestion);
		d('cp');
		
		return $this;
	}

	
	/**
	 * Update TAGS
	 * @return object $this
	 */
	protected function addTags(){

		Qtagscounter::factory($this->oRegistry)->parse($this->oQuestion);
		d('cp');
		return $this;
	}

	
	/**
	 * @todo do this via shutdown function
	 */
	protected function addRelatedTags(){
		Relatedtags::factory($this->oRegistry)->addTags($this->oQuestion);
		d('cp');
		return $this;
	}

	
	/**
	 * @todo do this via shutdown function
	 * @todo skip if $this->oQuestion['status'] is answered
	 * which would be the case when question came from API
	 * and is already answered
	 *
	 */
	protected function addUnansweredTags(){
		$o = new UnansweredTags($this->oRegistry);
		$o->set($this->oQuestion);
		d('cp');
		return $this;
	}

	
	/**
	 * @todo do this via shutdown function
	 */
	protected function addUserTags(){

		UserTags::factory($this->oRegistry)
		->addTags($this->oSubmitted->getUserObject()->getUid(), $this->oQuestion);
		d('cp');
		return $this;
	}


	/**
	 * @todo finish this, we don't have this in USER table yet
	 * This may not even be necessary at all
	 *
	 */
	protected function updateUserQCount(){


		return $this;
	}
}

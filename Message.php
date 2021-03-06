<?php 

/**
 * Message.php
 * @author Saranga Abeykoon http://nterms.com
 */

namespace nterms\mailqueue;

use Yii;
use nterms\mailqueue\models\Queue;

/**
 * Extends `yii\swiftmailer\Message` to enable queuing.
 * 
 * @see http://www.yiiframework.com/doc-2.0/yii-swiftmailer-message.html
 */
class Message extends \yii\swiftmailer\Message
{
	/**
	 * Enqueue the message storing it in database.
	 * 
	 * @return boolean true on success, false otherwise
	 */
	public function queue()
	{
		$item = new Queue();
		
		$item->from = serialize($this->from);
		$item->to = serialize($this->getTo());
		$item->cc = serialize($this->getCc());
		$item->bcc = serialize($this->getBcc());
		$item->reply_to = serialize($this->getReplyTo());
		$item->charset = $this->getCharset();
		$item->subject = $this->getSubject();
		$item->attempts = 0;

		if( $parts = $this->getSwiftMessage()->getChildren() ) {
			foreach( $parts as $key => $part ) {
				if( !( $part instanceof \Swift_Mime_Attachment ) ) {
					/* @var $part \Swift_Mime_MimePart */
					switch( $part->getContentType() ) {
						case 'text/html':
							$item->html_body = $part->getBody();
						break;
						case 'text/plain':
							$item->text_body = $part->getBody();
						break;
					}
					if( !$item->charset ) {
						$item->charset = $part->getCharset();
					}
				}
			}
		}

		return $item->save();
	}
}
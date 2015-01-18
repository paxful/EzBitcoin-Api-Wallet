<?php namespace Helpers;

class MailHelper
{
	public static function sendEmailPlain($data)
	{
		Mail::queue([], $data, function($message) use ($data)
		{
			$message->to($data['email']);
			$message->subject($data['subject']);
			$message->setBody($data['text']);
		});
		return true;
	}
}
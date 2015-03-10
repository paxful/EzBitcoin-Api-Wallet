<?php namespace Helpers;

use Illuminate\Support\Facades\Mail;

class MailHelper
{
	public static function sendEmailPlain($data)
	{
		Mail::send([], $data, function($message) use ($data)
		{
			$message->to($data['email']);
			$message->subject($data['subject']);
			$message->setBody($data['text']);
		});
		return true;
	}
}
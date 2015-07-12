<?php namespace Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class MailHelper
{
	public static function sendEmailPlain($data)
	{
		Mail::send([], $data, function($message) use ($data)
		{
			$message->to($data['email']);
			$message->subject(App::environment().': '.$data['subject']);
			$message->setBody(App::environment().': '.$data['text']);
		});
		return true;
	}
}
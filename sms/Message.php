<?php

namespace Messaging\sms;
use kernel;

class Message extends \Messaging\MessageHandler
{
	public function getType()
	{
		return 'sms';
	}

	public function send(string $subject, string $content, array $recipients)
	{
		$gateway = $this->getModuleValue('gateway');
		if (!$gateway)
		{
			kernel::log(LOG_ERR, 'missing sms gateway');
			return false;
		}
		switch ($gateway)
		{
		case 'gatewayapi.com':
			return $this->sendGatewayapiCom($subject, $content, $recipients);
		default:
			kernel::log(LOG_ERR, 'invalid sms gateway: ' . $gateway);
			break;
		}
		return false;
	}

	private function sendGatewayapiCom($subject, $content, $recipients)
	{
		$token   = $this->getModuleValue('token');
		$username = $this->getModuleValue('username');
		$password = $this->getModuleValue('password');
		if (!$token && (!$username || !$password))
		{
			kernel::log(LOG_ERR, 'gatewayapi.com needs token or username and password');
			return false;
		}
		$data = array(
			'message'    => (empty($subject) ? '' : $subject . "\n\n") . $content,
			'recipients' => array(),
		);
		if ($this->getOption('sender'))
		{
			/* gatewayapi.com accepts at most 11 sender characters */
			$data['sender'] = substr($this->getOption('sender'), 0, 11);
		}
		foreach ($recipients as $recipient)
		{
			$data['recipients'][] = array('msisdn' => $recipient);
		}

		$headers = array(
			'Accept: application/json, text/javascript',
			'Content-Type: application/json',
		);

		/* url is hardcoded on purpose mostly to avoid accidents! */
		$url  = 'https://gatewayapi.com/rest/mtsms' . ($token ? '?token=' . $token : '');
		$data = $this->post($url, json_encode($data), $username, $password, $headers);
		$data = @json_decode($data, true);
		var_dump($data);

		return $data === false ? false : true;
	}

	private function post($url, $data, $username, $password, $headers = array())
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($username && $password)
		{
			curl_setopt($ch, CURLOPT_USERPWD, $username . ':' . $password);
		}
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		var_dump($url);
		var_dump($data);
		// return false;

		$data = curl_exec($ch);
		if (curl_error($ch))
		{
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			kernel::log(LOG_ERR, 'message sms send POST request failed, http code: ' . $code . ', reason: ' . curl_error($ch));
			curl_close($ch);
			return false;
		}
		curl_close($ch);

		return $data;
	}
}

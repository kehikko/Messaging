<?php

namespace Messaging\email;

class Message extends \Messaging\MessageHandler
{
	public function getType()
	{
		return 'email';
	}

	public function send(string $subject, string $content, array $recipients)
	{
		$to      = implode(', ', $recipients);
		$headers = array();

		if ($this->getOption('sender'))
		{
			$headers['From'] = $this->getOption('sender');
		}

		if ($this->getOption('type'))
		{
			$headers['Content-Type'] = 'text/plain';
			switch ($this->getOption('type'))
			{
			case 'html':
				$headers['Content-Type'] = 'text/html';
				break;
			case 'binary':
				$headers['Content-Type'] = 'application/octet-stream';
				break;
			}
		}

		if ($this->getOption('encoding'))
		{
			if (!isset($headers['Content-Type']))
			{
				$headers['Content-Type'] = 'text/plain';
			}
			$headers['Content-Type'] .= '; charset=' . $this->getOption('encoding');
		}

		$_headers = array();
		foreach ($headers as $key => $value)
		{
			$_headers[] = $key . ': ' . $value;
		}
		return mail($to, $subject, $content, implode("\r\n", $_headers));
	}
}

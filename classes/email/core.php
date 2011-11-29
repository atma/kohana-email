<?php defined('SYSPATH') OR die('No direct access allowed.');
/**
 * Kohana Email abstraction module for Swiftmailer
 * 
 * @uses       Swiftmailer (v4.1.3)
 *
 * @package    Core
 * @author     Oleh Burkhay <atma@atmaworks.com>
 * @copyright  (c) 2007-2010 Kohana Team
 * @license    http://kohanaphp.com/license.html
 */
class Email {

	// SwiftMailer instance
	protected static $mail;

    // SwiftMailer attachments
    protected static $attachments = null;

	/**
	 * Creates a SwiftMailer instance.
	 *
	 * @param   string  DSN connection string
	 * @return  object  Swift object
	 */
	public static function connect($config = NULL)
	{
		if ( ! class_exists('Swift_Mailer', FALSE))
		{
			// Load SwiftMailer
			require Kohana::find_file('vendor', 'swift/swift_required');
		}

		// Load default configuration
		($config === NULL) and $config = Kohana::$config->load('email');
		
		switch ($config['driver'])
		{
			case 'smtp':
				// Set port
				$port = empty($config['options']['port']) ? 25 : (int) $config['options']['port'];
				
				// Create SMTP Transport
				$transport = Swift_SmtpTransport::newInstance($config['options']['hostname'], $port);

				if ( ! empty($config['options']['encryption']))
				{
					// Set encryption
					$transport->setEncryption($config['options']['encryption']);
				}
				
				// Do authentication, if part of the DSN
				empty($config['options']['username']) or $transport->setUsername($config['options']['username']);
				empty($config['options']['password']) or $transport->setPassword($config['options']['password']);

				// Set the timeout to 5 seconds
				$transport->setTimeout(empty($config['options']['timeout']) ? 5 : (int) $config['options']['timeout']);
			break;
			case 'sendmail':
				// Create a sendmail connection
				$transport = Swift_SendmailTransport::newInstance(empty($config['options']) ? "/usr/sbin/sendmail -bs" : $config['options']);

			break;
			default:
				// Use the native connection
				$transport = Swift_MailTransport::newInstance($config['options']);
			break;
		}

		// Create the SwiftMailer instance
		return Email::$mail = Swift_Mailer::newInstance($transport);
	}

	/**
	 * Send an email message.
	 *
	 * @param   string|array  recipient email (and name), or an array of To, Cc, Bcc names
	 * @param   string|array  sender email (and name)
	 * @param   string        message subject
	 * @param   string        message body
	 * @param   boolean       send email as HTML
	 * @return  integer       number of emails sent
	 */
	public static function send($to, $from, $subject, $message, $html = FALSE)
	{
		// Connect to SwiftMailer
		(Email::$mail === NULL) and Email::connect();

		// Determine the message type
		$html = ($html === TRUE) ? 'text/html' : 'text/plain';

		// Create the message
		$message = Swift_Message::newInstance($subject, $message, $html, 'utf-8');

		if (is_string($to))
		{
			// Single recipient
			$message->setTo($to);
		}
		elseif (is_array($to))
		{
			if (isset($to[0]) AND isset($to[1]))
			{
				// Create To: address set
				$to = array('to' => $to);
			}

			foreach ($to as $method => $set)
			{
				if ( ! in_array($method, array('to', 'cc', 'bcc')))
				{
					// Use To: by default
					$method = 'to';
				}

				// Create method name
				$method = 'add'.ucfirst($method);

				if (is_array($set))
				{
					// Add a recipient with name
					$message->$method($set[0], $set[1]);
				}
				else
				{
					// Add a recipient without name
					$message->$method($set);
				}
			}
		}

		if (is_string($from))
		{
			// From without a name
			$message->setFrom($from);
		}
		elseif (is_array($from))
		{
			// From with a name
			$message->setFrom($from[0], $from[1]);
		}

        if (! empty(self::$attachments))
        {
            foreach (self::$attachments as $attachment)
            {
                $message->attach($attachment);
            }
        }
        
        try
        {
            $response = self::$mail->send($message);
            self::clear_attachments();
            
            return $response;
        }
        catch (Swift_SwiftException $e)
        {
            // Throw Kohana Http Exception
            throw new Http_Exception_408('Connecting to mailserver timed out: :message', array(
                ':message' => $e->getMessage()
            ));
        }
	}

    /**
     * Clear attachments
     *
     * @return null
     */
    public static function clear_attachments()
    {
        return self::$attachments = null;
    }

	/**
	 * Attach file to the current message.
	 *
     * @param  string                         Filename for dynamic $data or absolute path for existing file
     * @param  string|Swift_OutputByteStream
     * @param  string                         Mime Type
	 * @return object|null                    Attached file as Swift_Attachment object
	 */
    public static function attach ($file = null, $data = null, $contentType = null)
    {
        // Connect to SwiftMailer
		if (self::$mail === NULL)
            self::connect();

        $attachment = null;
        // Add existing file
        if ($data === null AND $file !== null)
        {
            if (file_exists($file))
            {
                $attachment = Swift_Attachment::fromPath($file, $contentType);
            }
        }
        //Create the attachment with dynamic data
        else
        {
            $attachment = Swift_Attachment::newInstance($data, $file, $contentType);
        }
        if ($attachment)
            self::$attachments[] = $attachment;
        return $attachment;
    }
} // End Email_Core
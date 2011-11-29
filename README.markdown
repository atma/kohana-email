Email Module For Kohana 3.2
=================================

This is an Email abstraction module for [Swiftmailer](http://swiftmailer.org/).

It has been updated to work with SwiftMailer 4 and includes the libs from the 4.1.3 distribution.

Methods defined:

### Email::connect($config = NULL)

Creates SwiftMailer object. $config is an array of configuration values and defaults to using the config file 'email.php'.

### Email::send($to, $from, $subject, $message, $html = false)

$to can be any of the following:

*  a single string email address e.g. "test@example.com"
*  an array specifying an email address and a name e.g. array('test@example.com', 'John Doe')
*  an array of recipients in either above format, keyed by type e.g. array('to' => 'test@example.com', 'cc' => array('test2@example.com', 'Jane Doe'), 'bcc' => 'another@example.com')

$from can be either a string email or array of email and name as above

More complex email (multipart, attachments, batch mailing etc.) must be done using the native Swift_Mailer classes. The Swift Mailer autoloader is included by connect() so you can use and class in the Swift library without worrying about including files.

The Swift_Mailer object setup by connect is returned by it so if you need to access it manually use:

        $mailer = Email::connect();

        // Create complex Swift_Message object stored in $message

        $mailer->send($message);


### Email::attach($file = null, $data = null, $contentType = null)
*Modified for personal needs*

Attach files, can be called multiple times. Call this before Email::send, after sending attachments are clearing.
When used dynamic attachment $data $file uses for a file name, otherwise $file is an absolute file path.
#### Dynamic

        $file = file_get_contents('/var/log/some.log');

        Email::attach('current.log', $file);

#### Existing files

        Email::attach('/var/log/some.log');


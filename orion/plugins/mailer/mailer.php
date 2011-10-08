<?php
namespace Orion\Plugins;

use \Orion\Core;

class Mailer
{
    const E_MAILER_ERROR = 2000;
    const E_MISSING_TARGET = 2001;
    const E_MISSING_PARAMETER = 2002;
    
    private static $target='';
    private static $security = false;
    
    public static function load($args)
    {
        if(!empty($args['target'])) self::$target = $args['target'];
        if(!empty($args['security'])) self::$security = $args['security'];
    }
    
    /**
     * Send a mail.
     * @param string $mail The sender mail address (From)
     * @param string $subject The subject of the mail
     * @param string $message The content of the mail
     * @param string $name The name of the Target (To)
     * @param string $target The mail address of the target (To)
     * @return boolean success 
     */
    public static function send($mail, $subject, $message, $name='', $target='')
	{
        if(empty($target)) $target = self::$target;
		if(empty($target)) throw new Core\Exception('Missing target mail address.', self::E_MISSING_TARGET, __CLASS__);
		if(empty($mail)) throw new Core\Exception('Missing sender mail address.', self::E_MISSING_PARAMETER, __CLASS__);
        if(empty($subject)) throw new Core\Exception('Missing mail subject.', self::E_MISSING_PARAMETER, __CLASS__);
        if(empty($message)) throw new Core\Exception('Missing message content.', self::E_MISSING_PARAMETER, __CLASS__);
        
        if(self::$security)
        {
            $mail = htmlspecialchars($mail, ENT_QUOTES);
            $subject = htmlspecialchars($subject, ENT_QUOTES);
            $message = htmlspecialchars($message, ENT_QUOTES);
            $name = htmlspecialchars($name, ENT_QUOTES);
            $target = htmlspecialchars($target, ENT_QUOTES);
        }
        
        $headers = 'Content-type: text/html; charset=utf-8\r\n'.
                    'MIME-Version: 1.0\r\n'.
                    'From: '.$mail."\r\n".
                    'Reply-To: '.$mail."\r\n".
                    'X-Mailer: PHP/'.phpversion();
        $subject = $subject;
        $result = mail($target, $subject, $message, $headers);

        if($result) return true;
        else throw new Core\Exception('Internal mailer error.', self::E_MAILER_ERROR, __CLASS__);
    }
}
?>

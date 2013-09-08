<?php

namespace Kernel\Services;
 
use Kernel\Exceptions as Exceptions;

/**
 * @brief This class allows to send mails which are defined by one recipient, one subject, one content and one sender.
 */
class MailSender
{
    /**
     * @brief The boundary between the mail informations.
     * @var String.
     */
    private $boundary;
    /**
     * @brief The mail content.
     * @var String.
     */
    private $content;
    /**
     * @brief The mail of the sender.
     * @var String.
     */
    private $senderMail;
    /**
     * @brief The sender name.
     * @var String.
     */
    private $senderName;
    /**
     * @brief The mail subject.
     * @var String.
     */
    private $subject;
    /**
     * @brief The mail of the recipient.
     * @var String.
     */
    private $to;
    
    /**
     * @brief Constructor.
     * @param String $to The mail of the recipient.
     * @param String $subject The mail subject.
     * @param String $content The mail content.
     * @param String $senderName The sender name.
     * @param String $senderMail The mail of the sender.
     */
    public function __construct($to = '', $subject = '', $content = '', $senderName = '', $senderMail = '')
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->content = $content;
        $this->senderName = $senderName;
        $this->senderMail = $senderMail;
        $this->boundary = md5(rand());
    }

    /**
     * @brief Creates mail headers.
     * @return String The mail headers.
     */
    public function headers()
    {
        $return = $this->goNextLine();
        $boundary = '-----='.$this->boundary;
        $headers = 'From: \''.$this->senderName.'\'<'.$this->senderMail.'>'.$return;
        $headers.= 'Content-Type: text/html; charset=\'utf-8\''.$return;
        $headers.= 'MIME-Version: 1.0'.$return;
        $headers.= 'Content-Type: multipart/alternative;'.$return.' boundary=\''.$boundary.'\''.$return;
        return $headers.$return.'--'.$this->boundary.$return;
    }
    
    /**
     * @brief Sends mail.
     *
     * @exception Kernel::Exceptions::MailSendingException When mail isn't successfully accepted for delivery.
     * 
     * @see http://fr2.php.net/manual/en/function.mail.php
     */
    public function send()
    {
        $return = $this->goNextLine();
        $message = $return.$this->content.$return;
        $message.= $return.'--'.$this->boundary.'--'.$return;
        $message.= $return.'--'.$this->boundary.'--'.$return;
        if(mail($this->to, $this->subject, $message, $this->headers()) === false)
        {
            throw new Exceptions\MailSenderException('Mail "'.$this->subject.'" to '.$this->to.' isn\'t successfully accepted for delivery.');
        }
    }
    
    /**
     * @brief Set mail content.
     * @param String $content The mail content.
     */
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    /**
     * @brief Set mail of the sender.
     * @param String $mail The mail of the sender.
     */
    public function setSenderMail($mail)
    {
        $this->senderMail = $mail;
    }
    
    /**
     * @brief Set sender name.
     * @param String $name The sender name.
     */
    public function setSenderName($name)
    {
        $this->senderName = $name;
    }
    
    /**
     * @brief Set mail subject.
     * @param String $subject The mail subject.
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }
    
    /**
     * @brief Set mail of the recipient.
     * @param String $to The mail of the recipient.
     */
    public function setTo($to)
    {
        $this->to = $to;
    }

    /**
     * @brief Computes the end of line character which depends on mail server.
     * @return String The end of line character.
     */
    private function goNextLine()
    {
        return (preg_match('#^[a-z0-9._-]+@(hotmail|live|msn).[a-z]{2,4}$#', $this->to)) ? '\n' : '\r\n';
    }
    
}

?>
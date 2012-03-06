<?php

/***
 * Pre-Requisites
     * 1. Install OpenSSL on your Web Server
         *  It can be enabled by uncommenting ';' from php.ini file
     * 2. Obtain Cert ID
         * 2.a. Obtain PayPal Cert File
         * 2.b. Generate PayPal Public File
         * 2.c. Generate PayPal Private File
     * 3. This service will only be running on those Web-Servers, where Email is enabled
 */

/**
 * Description of class
 * This class is a kind of Hack of PayPal Request Money
 * Because there is no such API available.
 *
 * @author Legouverneur Romain
 * romain@xstyled.net
 */
class PayPalRequestMoney 
{
    /**
     * Sandbox PayPal Settings
     */
    const MY_KEY_FILE = "paypal_empfile/private-key.pem";       //PayPal Private Key File
    const MY_CERT_FILE = "paypal_empfile/public-cert.pem";      //PayPal Public Key File
    const PAYPAL_CERT_FILE = "paypal_empfile/paypal_cert.pem";  //PayPal Certification File
    const OPENSSL = "/usr/bin/openssl";                         //OpenSSL Location
    const CERT_ID = "xxxxxxxxxxxxx";                            //PayPal Cert ID
    const MERCHANT_NAME = "TEST MERCHANT NAME";                 //Merchant Name
    
    private $mode;
    private $paynow_url, $notify_url, $business_email;

    /**
     * To set the Mode for PayPal Request Money
     * @param string $md 
     */
    function __construct($md = "sandbox")
    {
        if($md == "live")
        {
            $this->mode = "live";
            $this->paynow_url = "https://www.paypal.com/cgi-bin/webscr";
            
            //Notify URL - The URL where IPN response is received after payment is paid from customer by clicking PayNow button
            $this->notify_url = "http://xxxxxxxxxxxxxxxxx.php";
            
             //PayPal Business Email Address
            $this->business_email = "xxxxxxxxxxxxxxxx_biz@xxx.xxx.xx";
        }
        else
        {
            $this->mode = "sandbox";
            $this->paynow_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
            
            //Notify URL - The URL where IPN response is received after payment is paid from customer by clicking PayNow button
            $this->notify_url = "http://xxxxxxxxxxxxxxxxx.php";
            
             //PayPal Business Email Address
            $this->business_email = "xxxxxxxxxxxxxxxx_biz@xxx.xxx.xx";
        }
    }
    
    /**
     * It further process the encrypted string
         * NOTE: If you are facing problem on PayNow button, you can uncomment those two line in the function
     * 
     * @param type $encrypted_string
     * @return string/boolean - It returns processed string 
     */
    function processEncryptedString($encrypted_string = "")
    {
        if($encrypted_string == "")
            return false;

        //$encrypted_string = str_replace("\n", "", $encrypted_string);
        //$encrypted_string = str_replace("\r", "", $encrypted_string);
        
        return $encrypted_string;
    }
    
    /**
     *
     * @param associative array $hash - Associative Array to get encrypted string
     * @return string - returns the encrypted string 
     */
    function getEncryptedString($hash)
    {

            if (!file_exists(self::MY_KEY_FILE)) {
                    exit( "FILE MISSING: MY_KEY_FILE ".self::MY_KEY_FILE." not found");
            }
            if (!file_exists(self::MY_CERT_FILE)) {
                    exit( "FILE MISSING: MY_CERT_FILE ".self::MY_CERT_FILE." not found");
            }
            if (!file_exists(self::PAYPAL_CERT_FILE)) {
                    exit( "FILE MISSING: PAYPAL_CERT_FILE ".self::PAYPAL_CERT_FILE." not found");
            }

            
            $data = "";
            foreach ($hash as $key => $value) {
                    if ($value != "") {                          
                            $data .= "$key=$value\n";
                    }
            }

            $openssl_cmd = "(".self::OPENSSL." smime -sign -signer ".self::MY_CERT_FILE." -inkey ".self::MY_KEY_FILE." " .
                                                    "-outform der -nodetach -binary <<_EOF_\n$data\n_EOF_\n) | " .
                                                    "".self::OPENSSL." smime -encrypt -des3 -binary -outform pem ".self::PAYPAL_CERT_FILE."";

            exec($openssl_cmd, $output, $error);

            if (!$error) 
            {
                    return implode("\n",$output);
            } 
            else 
            {
                    return false;
            }
    }
    
    /**
     *
     *  Generate Email Contents for Request Money Button
     * 
     * @param associative array -  $aryData
     * @return string 
     */
    function generateEmailContents($aryData)
    {
        date_default_timezone_set('America/Los_Angeles');

        $contents = '';
        $contents .= '
                        <div align="center">    
                            <table width="600" cellspacing="0" cellpadding="0" border="0" align="center">
                                <tbody>
                                    <tr valign="top">
                                        <td width="100%">
                                            <table width="600" cellspacing="0" cellpadding="0" border="0" align="center" style="color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px;">
                                                <tbody>
                                                    <tr valign="top">
                                                        <td><img border="0" alt="PayPal logo" src="http://images.paypal.com/en_US/i/logo/paypal_logo.gif"></td>
                                                        <td valign="middle" align="right">'.@date('M d, Y H:i:s T').'</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                            <div style="margin-top: 30px; color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px;">
                                                <span style="color: rgb(51, 51, 51) ! important; font-weight: bold; font-family: arial,helvetica,sans-serif;">Hello <a target="_blank" href="mailto:'.$aryData['recipient_email'].'">'.$aryData['recipient_email'].'</a>,</span>
                                                <br><br>
                                                <span style="font-size: 14px; color: rgb(200, 128, 57); font-weight: bold; text-decoration: none;">'.$aryData['subject'].'.</span>
                                                <br><br>
                                                <table width="100%" cellspacing="0" cellpadding="0" border="0" align="left" style="border-top: 1px solid rgb(204, 204, 204); color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px;">
                                                    <tbody>
                                                        <tr>
                                                            <td width="50%" valign="top" align="left" style="padding: 10px 0pt 20px; color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px;">
                                                                <span style="color: rgb(51, 51, 51); font-weight: bold;">Merchant</span>
                                                                <br>'.self::MERCHANT_NAME.'<br>
                                                            </td>
                                                            <td valign="top" style="padding: 10px 0pt 20px; color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px;">
                                                                <span style="color: rgb(51, 51, 51); font-weight: bold;">Note from merchant<br></span>
                                                                '.$aryData['message'].'
                                                                <br>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center" style="clear: both; color: rgb(102, 102, 102) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px;">
                                                    <tbody>
                                                        <tr>
                                                            <td width="398" align="left" style="border-width: 1px medium; border-style: solid none; border-color: rgb(204, 204, 204) -moz-use-text-color; padding: 5px 10px ! important; color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px;">Description</td>
                                                            <td width="200" align="right" style="border-width: 1px medium; border-style: solid none; border-color: rgb(204, 204, 204) -moz-use-text-color; padding: 5px 10px ! important; color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px;">Amount</td>
                                                        </tr>
                                                        <tr style="padding: 10px;">
                                                            <td align="left" style="border-bottom: medium none; padding: 10px; color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px;">Goods</td>
                                                            <td valign="top" align="right" style="border-bottom: medium none; padding: 10px 7px 10px 10px; color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px;">'.number_format(doubleval($aryData['amount']), 2).' USD</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <table width="595" cellspacing="0" cellpadding="0" border="0" align="center" style="border-top: 1px solid rgb(204, 204, 204); border-bottom: 1px solid rgb(204, 204, 204); padding-bottom: 20px; clear: both; color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px; margin-bottom: 20px;">
                                                    <tbody>
                                                        <tr>
                                                            <td width="100%" style="color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px;">
                                                                <table cellspacing="0" cellpadding="0" border="0" align="right" style="color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px; margin-top: 10px;">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td style="color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px; text-align: right; padding: 0pt 10px 0pt 0pt;">
                                                                                <span style="color: rgb(51, 51, 51) ! important; font-weight: bold;">Total</span>
                                                                            </td>
                                                                            <td style="color: rgb(51, 51, 51) ! important; font-family: arial,helvetica,sans-serif; font-size: 12px; text-align: right; padding: 0pt 5px 0pt 0pt;">'.number_format(doubleval($aryData['amount']), 2).' USD</td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>

                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <span style="font-weight: bold; color: rgb(51, 51, 51);">Pay with PayPal</span>
                                                <p>International Checkout would like you to use PayPal - the safer, easier way to pay and get paid online. Click the <span style="font-weight: bold;">Pay Now</span> to continue.</p>
                                                <table cellspacing="0" cellpadding="0" border="0" align="center">
                                                    <tbody>
                                                        <tr>
                                                            <td align="center">
                                                                <a target="_blank" href="'.$this->paynow_url.'?cmd=_s-xclick&encrypted='.urlencode($aryData['encrypted']).'"><img border="0" align="center" alt="Click here to pay instantly with PayPal!" src="http://images.paypal.com/en_US/i/btn/btn_paynow_LG.gif"></a>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <br>
                                                <table cellspacing="0" cellpadding="0" border="0" align="center">
                                                    <tbody>
                                                        <tr>
                                                            <td valign="top" align="left">
                                                                <img border="0" alt="Visa" src="http://images.paypal.com/en_US/i/logo/logo_ccVisa.gif">
                                                                <img border="0" alt="MasterCard" src="http://images.paypal.com/en_US/i/logo/logo_ccMC.gif">
                                                                <img border="0" alt="Discover" src="http://images.paypal.com/en_US/i/logo/logo_ccDiscover.gif">
                                                                <img border="0" alt="American Express" src="http://images.paypal.com/en_US/i/logo/logo_ccAmex.gif">
                                                                <img border="0" alt="eCheck" src="http://images.paypal.com/en_US/i/logo/logo_ccEcheck.gif">
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <br>
                                                <div style="border-top: 1px solid rgb(204, 204, 204);"></div>
                                                <br>
                                                <img border="0" alt="" style="margin-right: 5px; vertical-align: middle;" src="http://images.paypal.com/en_US/i/icon/icon_help_16x16.gif">
                                                <span style="font-family: arial,helvetica,sans-serif; font-size: 12px;">Questions? Go to the Help Center at: <a target="_blank" href="https://www.paypal.com/us/help">https://www.paypal.com/us/help</a><br><br></span>
                                                <span style="font-family: arial,helvetica,sans-serif; font-size: 11px;">Please do not reply to this email. This mailbox is not monitored and you will not receive a response. For assistance, log in to your PayPal account and click <span style="font-weight: bold;">Help</span> in the top right corner of any PayPal page.<br><br>To receive email notifications in plain text instead of HTML, log in to your PayPal account and go to your Profile to update your settings.</span>
                                            </div>
                                            <br>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
            ';
        return $contents;
    }
    
    /**
     *
     * The Actual Money Request Fucntion
     * 
     * @param associative-array $aryData
     * @return string 
     */
    function requestMoney($aryData = array())
    {
        if(count($aryData)==0)
            return false;

        $form = array(
            'business' => $this->business_email,
            'cert_id' => self::CERT_ID,
            'cmd' => '_xclick',
            'upload' => '1',
            'currency_code' => 'USD',
            'no_shipping' => '1',
            'rm' => '1',
            'no_note'=> '1',
            'custom'=> $aryData['subject'],
            'notify_url' => $this->notify_url
	);
        
        if(isset($aryData['invoice']) && trim($aryData['invoice']) != "")
            $form['invoice'] = $aryData['invoice'];
        
        $form['item_name'] = $aryData['message'];
        $form['amount'] = number_format(doubleval($aryData['amount']), 2);
        
        $form['on0'] = "Order No";
        $form['os0'] = $aryData['invoice'];

        $aryData['encrypted'] = $this->getEncryptedString($form);

        if($aryData['encrypted'])
            return $this->generateEmailContents($aryData);
        else
            return false;
    }
    
    /**
     *
     * This function sends the generated Email of Request Money to the customer
     * 
     * @param string $to
     * @param string $cc
     * @param string $subject
     * @param string $from
     * @param string $body
     * @return boolean 
     */
    function sendEmail($to = "", $cc = "", $subject = "", $from = "service@paypal.com" , $body = "")
    {
        
        $from = $this->business_email;      //From Email can be set to your Business Email Address

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
        $headers .= 'From: <'.$from.'>' . "\r\n";
        
        if($cc != "")
            $headers .= 'Cc: '. $cc . "\r\n";

        $mail_sent = @mail( $to, $subject, $body, $headers );

        if($mail_sent)
            return true;
        return false;
    }
    
}/* End of Class */


?>
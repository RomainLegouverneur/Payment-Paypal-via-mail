<?php
    error_reporting(15);

    require_once('class.request.money.php');
    
    $objReq = new PayPalRequestMoney("sandbox");

    $amount = "42.00";

    $aryData = array();
    $aryData['recipient_email'] = "romain@xstyled.net";
    $aryData['amount'] = number_format(doubleval($amount), 2);
    $aryData['subject'] = "TEST MERCHANT NAME would like to be paid through PayPal.";
    $aryData['invoice'] = strtotime('now');
    $aryData['message'] = "Additional Amount Request";

    $contents = $objReq->requestMoney($aryData);    
    if(!$contents)
    {
        echo "Please Check that OpenSSL is enabled from php.ini file. <br />";
        exit("ERROR: Encryption Failed");
    }

    
    if($objReq->sendEmail($aryData['recipient_email'], "", $aryData['subject'], "" , $contents))
    {
        echo "Request Money Email is sent Successfully.";
    }
    else
    {
        echo "Error Sending Request Money Email.<br /><br />The Email Contents are: <br /><br />";
        echo $contents;
    }
?>
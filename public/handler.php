<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);

ini_set('upload_max_size' , '55M');
ini_set('post_max_size', '50M');

require './vendor/autoload.php';

$serverEmail = 'from@gmail.com';

$errormsg = [];

if ($isPost = isset($_POST['send'])) {
    if (!isset($_POST['email'])) {
        array_push($errormsg, 'Введите адрес электронной почты');
    } else {       
        $email = trim($_POST['email']);
        $email = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$email) {
            array_push($errormsg, 'Email указан не верно.');
        }
    }
    
    $pure_html = '';
    if (!isset($_POST['html'])) {
        array_push($errormsg, 'Отсутствует текст сообщения');
    } else {
        $rawHtml = $_POST['html'];
        $config = HTMLPurifier_Config::createDefault();
        
        $config->set('Core.Encoding', 'UTF-8');
        $config->set('HTML.Doctype', 'HTML 4.01 Strict');
        $purifier = new HTMLPurifier($config);
        $pure_html = $purifier->purify($rawHtml);        
    }
        
    $filesToSend = [];
    if (isset($_FILES['uploadfile']) && is_array($_FILES['uploadfile'])) {             
        foreach ($_FILES['uploadfile']['error'] as $key => $error) {
            $name = basename($_FILES['uploadfile']['name'][$key]);
            if ($error == UPLOAD_ERR_OK) {
                if ($_FILES['uploadfile']['size'][$key] > 0) {
                    $tmp_name = $_FILES['uploadfile']['tmp_name'][$key];
                    $name = $_FILES['uploadfile']['name'][$key];
                    move_uploaded_file($tmp_name, "./data/$name");
                    array_push($filesToSend, $name);
                } else {
                    array_push($errormsg, "Ошибка! Выбранный файл $name пуст.");
                }                
            } else {
                array_push($errormsg, "При загрузке файла $name произошла ошибка $error.");
            }            
        }        
    }
    
    if (count($errormsg) === 0) {
        //$text = 'Text version of email.';               
        
        $crlf = "\n";
        $charset = 'utf-8';
        $hdrs = [
            'From'    => $serverEmail,
            'Subject' => 'Test mime message'
        ];

        $mime = new Mail_mime(array('eol' => $crlf, 'text_charset' => $charset, 'head_charset' => $charset, 'html_charset' => $charset));

        //$mime->setTXTBody($text);
        $mime->setHTMLBody($pure_html);
        
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        foreach ($filesToSend as $fileName) {
            $type = finfo_file($finfo, './data/'.$fileName);            
            $mime->addAttachment('./data/'.$fileName, $type, '', true, 'base64', 'attachment', $charset);            
        }
        finfo_close($finfo);

        $body = $mime->get();
        $hdrs = $mime->headers($hdrs);

        @$mail =& Mail::factory('mail');
        $status = $mail->send($email, $hdrs, $body);
        if (is_a($status, 'PEAR_Error')) {
            array_push($errormsg, 'При отправке email произошла ошибка.');            
        }        
    }
    
    foreach ($filesToSend as $fileName) {
        unlink('./data/'.$fileName);
    }    
} else {    
    header("Location: /");
}

?>
<!DOCTYPE HTML>
<html>
 <head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <title>Статус отправки email</title>
  <link href="/css/style.css" media="screen" rel="stylesheet" type="text/css">  
 </head>
 <body>
   <?php if (count($errormsg) === 0) {
       echo '<div class="success">Email удачно отправлен!</div>';
   } else {
       echo '<div class="error">Email не был отправлен!';
       foreach ($errormsg as $msg) {
           echo '<p>'.$msg.'</p>';
       }
       echo '</div>';
   } ?>
   <div><a href="/">Отправить email</a></div>
 </body>
</html>

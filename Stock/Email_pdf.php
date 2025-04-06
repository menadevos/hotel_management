<?php
//pour envoyer l'email de verification
// Prépare l'email de confirmation avec le lien
 //utiliser les biblio 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
 //use PHPMailer\PHPMailer\SMTP;
require 'C:/xampp/htdocs/myphp/PHPMailer/src/Exception.php';
require 'C:/xampp/htdocs/myphp/PHPMailer/src/PHPMailer.php';
require 'C:/xampp/htdocs/myphp/PHPMailer/src/SMTP.php';


function sendemail($to,$subject , $body ){
    $mail = new PHPMailer(true); 
    try {
        //Server settings 
        $mail->isSMTP();  // l'email sera envoye avec SMTP
    
    
        //////////////////////////
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;  // gerer les exceptions , normallement on fait ca m nous on veut pas debuger .  
        $mail->SMTPDebug = 0; // Pas de débogage 
        ////////////////////////////////
    
        $mail->Host       = 'smtp.gmail.com';   // gmail            
        $mail->SMTPAuth   = true;  //  Authentification :  En activant cette option, PHPMailer va se connecter au serveur SMTP en utilisant un identifiant et un mot de passe                                                    
        $mail->Username   = 'af9201894@gmail.com';                     //SMTP username
        $mail->Password   = 'xhpz zfkb hdiw uwak'; //code application courrier     //SMTP password
       $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;   // type de securite 
        $mail->Port       = 587;                           //port de type de securite         
    
        // preciser a qui on va pas envoyer l'email 
         
           
        $mail->setFrom('af9201894@gmail.com', 'TetraVilla');
        $mail->addAddress($to,'Fournisseur' ); 
        //$mail->addReplyTo('no-reply@exemple.com', 'Ne pas répondre a cet  email');// exemple est remplace par le domaine de sie maintenant on est local y'a pas de domaine on la laisse avec exemple 
       
    
       
        //Content of email 
        $mail->isHTML(true);
                                       //Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $body;
       
        // send email
    
    // ce code d'apres chatgbt je ne compris pas pourquoi mais il a resolu un probleme de ssl ....
    ////////////////////////////////////////////////////////////
    $mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        )
    ); //////////////////////////////////////////////////////////////////////
    
        $mail->send();
        echo 'Email has been sent';

    } catch (Exception $e) {
        echo "Error , please repeat after . Mailer Error: {$mail->ErrorInfo}";
    }
}//end fonction 1 

?>
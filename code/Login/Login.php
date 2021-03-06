<?php
// This, to be sure, should go under ssh
//require_once 'php/Login.php';
require_once 'php/User.php';
require_once 'php/mailer.php';

// Is the stuff below really needed ?
// 
//$Login = new Login;
//if ($Login->adminCorrectlyLogged()) {
//    header('Location: Admin.php');
//    exit(0);
//}
// This might rise problems
//if ($Login->clubCorrectlyLogged()) {
//    header('Location: Regate.php');
//    exit(0);
//}


function doLogin($login, $codedMdp) {
    global $Login;

    $user = User_selectByLoginAndMdp($login, $codedMdp);
    if ($user == NULL) {
        $message = "Le login ou le mot de passe n'est pas correcte";
        pageErreur($message);
        exit(1);
    }

    switch ($user['role']) {
        case 'Admin':
            $Login->loginAsAdmin($user['ID']);
            header("Location: Admin.php");
            break;
        case 'Club':
            $Login->loginAsClub($user['ID']);
            header("Location: Regate.php");
            break;
    }
    exit(0); // We do not return from here
}

function doLoginWithIdAndNonce($id, $nonce) {
    global $Login;

    $user = User_selectByIdAndNonce($id, $nonce);

    if ($user != NULL) {
        switch ($user['role']) {
            case 'Admin':
                $Login->loginAsAdmin($user['ID']);
                break;
            case 'Club':
                $Login->loginAsClub($user['ID']);
                break;
        }
    }
    return $user;
}

if (isset($_POST['submit'])) {
    /* Un des champs est manquant */
    if ((!isset($_POST['login']) || !isset($_POST['pass'])) ||
            ($_POST["pass"] == "" || $_POST["login"] == "")
    ) {
        $message = "Un ou plusieurs champs sont manquants.";
        pageErreur($message);
        exit(1);
    }


    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_STRING);
    $codedPass = md5($pass);

    doLogin($login, $codedPass);
    exit(0); // We do not return from here
}

function sendEmailWithNonceLink($user, $nonce) {
    global $hashGetString, $config;

    // Format the body of answer
    $urlChpwd = format_url_login();
    $nonceString = encodeHashId($nonce, $user['ID']);
    $urlChpwdNonce = $urlChpwd . "&$hashGetString=$nonceString";

    $destinataire = $user['courriel'];
    $login = $user['login'];

    $sender = $config['webMasterEmail'];
    // No need for this, as the webMaster is in CC of every Email 
    // $cc = $config['webMasterEmail'];
    $cc = '';
    $to = $destinataire;
    $subject = 'Jeton pour reinitialiser le mot de passe '
            . "sur le site des pré-inscriptions de $config[signature]";

    $message = "Bonjour,\n\n"
            . "vous pouvez modifier votre mot de passe en suivant ce lien : "
            . "\n"
            . $urlChpwdNonce
            . "\n\n"
            . "On vous rappelle votre login : $login"
            . "\n\n"
            . "Cordialement, \n\t $config[signature]";

    $result = send_mail_text($sender, $to, $subject, $message, $cc);

    if ($result) {
        $message = "Un courriel permettant de reinitialiser vos identifiants "
                . "a été envoyé à votre adresse email";
        pageAnswer($message, 'index.php');
    } else {
        $message = "Problème lors de l'envoie d'un courriel";
        pageErreur($message);
    }
}

function doSetNonceAndSendEmail($courriel) {

    $user = User_selectByLastCourriel($courriel);
    if ($user == NULL) {
        $message = "Ce courriel ne correspond à aucun utilisateur";
        pageErreur($message);
        exit(1);
    }

    $nonce = User_setNonce($user);
    sendEmailWithNonceLink($user, $nonce);
}

function sendEmailWithNonceSLink($users) {
    global $config;
    global $hashGetString;

    //$site = 'des pré-inscriptions aux régates de '.$config['signature'];
    $destinataire = $users[0]['courriel'];

    $mainText = '';
    foreach ($users as $user) {
        $urlChpwd = format_url_login();
        $nonceString = encodeHashId($user['nonce'], $user['ID']);
        $urlChpwdNonce = $urlChpwd . "&$hashGetString=$nonceString";

        $text = "Vous pouvez modifier le mot de passe pour l'utilisatur '$user[login]' en suivant ce lien :\n"
                . "$urlChpwdNonce\n\n";
        $mainText.=$text;
    }


    $sender = $config['webMasterEmail'];
    // No need for this, asthe webMaster is in CC of every Email 
    // $cc = $config['webMasterEmail'];
    $cc = '';
    $to = $destinataire;
    $subject = 'Jeton pour reinitialiser le mot de passe '
            . "sur le site des pré-inscriptions aux régates";

    $message = "Bonjour,\n\n"
            . $mainText
            . "\n\n"
//            . 'Ces liens seront valable pendant 24 heures de maintenant.'
//            . "\n\n"
            . "Cordialement, \n\n\t $config[signature]";

    $result = send_mail_text($sender, $to, $subject, $message, $cc);

    if ($result) {
        $message = "Un courriel permettant de reinitialiser vos identifiants "
                . "a été envoyé à votre adresse email";
        pageAnswer($message, 'index.php');
    } else {
        $message = "Problème lors de l'envoie d'un courriel";
        pageErreur($message);
    }
}

function doSetNonceSAndSendEmail($courriel) {

    $users = User_selectByCourriel($courriel);
    if (count($users) == 0) {
        $message = "Ce courriel ne correspond à aucun utilisateur";
        pageErreur($message);
        exit(1);
    }

    foreach ($users as $index => $user) {
        $newNonce = User_setNonce($user);
        $users[$index]['nonce'] = $newNonce;
    }
    sendEmailWithNonceSLink($users);
}

if ($config['pwdRecoveryOn'] and isset($_POST['mdpOublie'])) {

    //echo "couocu";

    $courriel = filter_input(INPUT_POST, 'courriel', FILTER_VALIDATE_EMAIL);
    if ($courriel == NULL) {
        $message = "Pas un adresse email";
        pageErreur($message);
    }

    // To send a link to the last user with this courriel
    //    doSetNonceAndSendEmail($courriel);
    // To send links to all the users with this courriel
    doSetNonceSAndSendEmail($courriel);
    $message = "Nous vous avons envoyé un courriel"
            . "contenant un lien qui vous permettra de "
            . "re-initialiser votre mot de passe";
    pageAnswer($message);
    exit(0);
}

// THIS HANDLE PASWIRD RECOVERY
if ($config['pwdRecoveryOn'] and isset($_GET[$hashGetString])) {

    $hashstring = $_GET[$hashGetString];
    $id = decodeIdFromHashString($hashstring);
    $nonce = decodeHashFromHashString($hashstring);

    $utilisateur = doLoginWithIdAndNonce($id, $nonce);
    if ($utilisateur != NULL) {
        User_destroyNonce($utilisateur);
    } else {
        $message = "Jeton invalide";
        pageErreur($message);
        exit(1);
    }
    $_SESSION['withOldPwd'] = False;
    header("Location: changePwd.php");
    exit(0);
}

xhtml_pre('');
doMenu();
?>

<div class="contenu smallform">
    <form action="" method="post">
        <fieldset>
            <legend>Vos identifiants</legend>
            <div>
                <label for="login">Login :</label>
                <input name="login" type="text" id="login"/>
                <br />
                <label for="pass">Mot de passe :</label>
                <input name="pass" type="password" id="pass"/>

                <input type="submit" name="submit" id="submit" value="Valider"/>
            </div>
        </fieldset>
    </form>
    
    <?php if ($config['pwdRecoveryOn']): ?>
        <br />
        <br />
        <form action="" method="post">
            <fieldset>
                <legend>Identifiants oubliés</legend>
                <div>
                    <label for="courriel">Votre courriel :</label>
                    <input name="courriel" type="text" id="courriel"/>

                    <input type="submit" name="mdpOublie" value="Valider"/>
                </div>
            </fieldset>
        </form>
    <?php endif; ?>
</div>

<?php
xhtml_post();

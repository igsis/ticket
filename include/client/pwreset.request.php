<?php
if(!defined('OSTCLIENTINC')) die('Access Denied');

$userid=Format::input($_POST['userid']);
?>
<h1>Esqueci Minha Senha</h1>
<p>
Digite seu nome de usuário ou endereço de e-mail no formulário abaixo e pressione o botão
<strong>Enviar Email</strong> para encaminharmos um Link de redefinição de senha para 
sua conta de e-mail.

<form action="pwreset.php" method="post" id="clientLogin">
    <div style="width:50%;display:inline-block">
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="sendmail"/>
    <strong><?php echo Format::htmlchars($banner); ?></strong>
    <br>
    <div>
        <label for="username">Usuário:</label>
        <input id="username" type="text" name="userid" size="30" value="<?php echo $userid; ?>">
    </div>
    <p>
        <input class="btn" type="submit" value="Send Email">
    </p>
    </div>
</form>

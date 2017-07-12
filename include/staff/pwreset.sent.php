<?php
include_once(INCLUDE_DIR.'staff/login.header.php');
defined('OSTSCPINC') or die('Invalid path');
$info = ($_POST && $errors)?Format::htmlchars($_POST):array();
?>

<div id="loginBox">
    <h1 id="logo"><a href="index.php">osTicket Redefinição de Senha Pessoal</a></h1>
    <h3>Um email de confirmação foi enviado</h3>
    <h3 style="color:black;"><em>
    Um e-mail de redefinição de senha foi enviada para o e-mail cadastrado. 
    Siga o link no e-mail para redefinir sua senha.
    </em></h3>

    <form action="index.php" method="get">
        <input class="submit" type="submit" name="submit" value="Entrar"/>
    </form>
</div>

<div id="copyRights">Copyright &copy; <a href='http://www.osticket.com' target="_blank">osTicket.com</a></div>
</body>
</html>

<?php if ($content) {
    list($title, $body) = $ost->replaceTemplateVariables(
        array($content->getName(), $content->getBody())); ?>
<h1><?php echo Format::display($title); ?></h1>
<p><?php
echo Format::display($body); ?>
</p>
<?php } else { ?>
<h1>Registro de Conta</h1>
<p>
<strong>Obrigado por criar uma conta.</strong>
</p>
<p>
Você já confirmou o seu endereço de e-mail e ativado com sucesso sua conta. 
Você pode prosseguir com a verificação de tickets abertos ou abrir um novo ticket.
</p>
<p><em>Your friendly support center</em></p>
<?php } ?>

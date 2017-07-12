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
Te enviamos um e-mail para o endereço informado. Por favor, siga o 
link no e-mail para confirmar sua conta e ter acesso aos seus tickets.
</p>
<?php } ?>

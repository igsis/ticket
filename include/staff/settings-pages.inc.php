<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
$pages = Page::getPages();
?>
<h2>Perfil da Empresa</h2>
<form action="settings.php?t=pages" method="post" id="save"
    enctype="multipart/form-data">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="pages" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead><tr>
        <th colspan="2">
            <h4>Informação Básica</h4>
        </th>
    </tr></thead>
    <tbody>
    <?php
        $form = $ost->company->getForm();
        $form->addMissingFields();
        $form->render();
    ?>
    </tbody>
    <thead>
        <tr>
            <th colspan="2">
                <h4>Páginas do Site</h4>
                <em>Para editar ou adicionar novas páginas vá para <a href="pages.php"> Gerenciar> Páginas</a></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="220" class="required">Página Inicial:</td>
            <td>
                <span>
                <select name="landing_page_id">
                    <option value="">&mdash; Selecione Página &mdash;</option>
                    <?php
                    foreach($pages as $page) {
                        if(strcasecmp($page->getType(), 'landing')) continue;
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $page->getId(),
                                ($config['landing_page_id']==$page->getId())?'selected="selected"':'',
                                $page->getName());
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['landing_page_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#landing_page"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td width="220" class="required">Página Offline:</td>
            <td>
                <span>
                <select name="offline_page_id">
                    <option value="">&mdash; Selecione Página &mdash;</option>
                    <?php
                    foreach($pages as $page) {
                        if(strcasecmp($page->getType(), 'offline')) continue;
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $page->getId(),
                                ($config['offline_page_id']==$page->getId())?'selected="selected"':'',
                                $page->getName());
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['offline_page_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#offline_page"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td width="220" class="required">Página Padrão de Agradecimento:</td>
            <td>
                <span>
                <select name="thank-you_page_id">
                    <option value="">&mdash; Selecione Página &mdash;</option>
                    <?php
                    foreach($pages as $page) {
                        if(strcasecmp($page->getType(), 'thank-you')) continue;
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $page->getId(),
                                ($config['thank-you_page_id']==$page->getId())?'selected="selected"':'',
                                $page->getName());
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['thank-you_page_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#default_thank_you_page"></i>
                </span>
            </td>
        </tr>
    </tbody>
</table>
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4>Logos
                    <i class="help-tip icon-question-sign" href="#logos"></i>
                    </h4>
                <em>Logo Padrão do Sistema</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
        <td colspan="2">
                <label style="display:block">
                <input type="radio" name="selected-logo" value="0"
                    style="margin-left: 1em"
                    <?php if (!$ost->getConfig()->getClientLogoId())
                        echo 'checked="checked"'; ?>/>
                <img src="../assets/default/images/logo.png"
                    alt="Default Logo" valign="middle"
                    style="box-shadow: 0 0 0.5em rgba(0,0,0,0.5);
                        margin: 0.5em; height: 5em;
                        vertical-align: middle"/>
                </label>
        </td></tr>
        <tr>
            <th colspan="2">
                <em>Logotipo Personalizado&nbsp;<i class="help-tip icon-question-sign" href="#upload_a_new_logo"></i></em>
            </th>
        </tr>
        <tr><td colspan="2">
            <?php
            $current = $ost->getConfig()->getClientLogoId();
            foreach (AttachmentFile::allLogos() as $logo) { ?>
                <div>
                <label>
                <input type="radio" name="selected-logo"
                    style="margin-left: 1em" value="<?php
                    echo $logo->getId(); ?>" <?php
                    if ($logo->getId() == $current)
                        echo 'checked="checked"'; ?>/>
                <img src="image.php?h=<?php echo $logo->getDownloadHash(); ?>"
                    alt="Custom Logo" valign="middle"
                    style="box-shadow: 0 0 0.5em rgba(0,0,0,0.5);
                        margin: 0.5em; height: 5em;
                        vertical-align: middle;"/>
                </label>
                <?php if ($logo->getId() != $current) { ?>
                <label>
                <input type="checkbox" name="delete-logo[]" value="<?php
                    echo $logo->getId(); ?>"/> Excluir
                </label>
                <?php } ?>
                </div>
            <?php } ?>
            <br/>
            <b>Selecionar Novo Logo:</b>
            <input type="file" name="logo[]" size="30" value="" />
            <font class="error"><br/><?php echo $errors['logo']; ?></font>
        </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input class="button" type="submit" name="submit-button" value="Salvar">
    <input class="button" type="reset" name="reset" value="Redefinir">
</p>
</form>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3>Por favor Confirme</h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" id="delete-confirm">
        <font color="red"><strong>Tem certeza de que deseja EXCLUIR logo selecionado?</strong></font>
        <br/><br/>Logos excluídos não podem ser recuperados.
    </p>
    <div>Please confirm to continue.</div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons" style="float:left">
            <input type="button" value="Não, Cancelar" class="close">
        </span>
        <span class="buttons" style="float:right">
            <input type="button" value="Sim, Confirmar!" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>

<script type="text/javascript">
$(function() {
    $('#save input:submit.button').bind('click', function(e) {
        var formObj = $('#save');
        if ($('input:checkbox:checked', formObj).length) {
            e.preventDefault();
            $('.dialog#confirm-action').undelegate('.confirm');
            $('.dialog#confirm-action').delegate('input.confirm', 'click', function(e) {
                e.preventDefault();
                $('.dialog#confirm-action').hide();
                $('#overlay').hide();
                formObj.submit();
                return false;
            });
            $('#overlay').show();
            $('.dialog#confirm-action .confirm-action').hide();
            $('.dialog#confirm-action p#delete-confirm')
            .show()
            .parent('div').show().trigger('click');
            return false;
        }
        else return true;
    });
});
</script>

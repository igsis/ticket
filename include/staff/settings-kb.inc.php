<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
?>
<h2>Configurações e Opções da Base de Conhecimento</h2>
<form action="settings.php?t=kb" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="kb" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4>Configurações da Base de Conhecimento</h4>
                <em>Desativar Base de Conhecimento desabilita interface de clientes.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180">Status da Base de Conhecimento:</td>
            <td>
              <input type="checkbox" name="enable_kb" value="1" <?php echo $config['enable_kb']?'checked="checked"':''; ?>>
              Habilitar Base de Conhecimento
              &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_kb']; ?></font> <i class="help-tip icon-question-sign" href="#knowledge_base_status"></i>
            </td>
        </tr>
        <tr>
            <td width="180">Resposta Pronta:</td>
            <td>
                <input type="checkbox" name="enable_premade" value="1" <?php echo $config['enable_premade']?'checked="checked"':''; ?> >
                Habilitar Resposta Pronta
                &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_premade']; ?></font> <i class="help-tip icon-question-sign" href="#canned_responses"></i>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:210px;">
    <input class="button" type="submit" name="submit" value="Salvar">
    <input class="button" type="reset" name="reset" value="Redefinir">
</p>
</form>

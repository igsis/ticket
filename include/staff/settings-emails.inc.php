<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
?>
<h2>Configurações de E-mail e Opções</h2>
<form action="settings.php?t=emails" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="emails" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4>Configurações de E-mail</h4>
                <em>Note que algumas configurações globais podem ser substituídas pelo nível do departamento/email.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">Modelo de E-mail Padrão:</td>
            <td>
                <select name="default_template_id">
                    <option value="">&mdash; Selecione o Modelo de E-mail Padrão &mdash;</option>
                    <?php
                    $sql='SELECT tpl_id, name FROM '.EMAIL_TEMPLATE_GRP_TABLE
                        .' WHERE isactive =1 ORDER BY name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id, $name) = db_fetch_row($res)){
                            $selected = ($config['default_template_id']==$id)?'selected="selected"':''; ?>
                            <option value="<?php echo $id; ?>"<?php echo $selected; ?>><?php echo $name; ?></option>
                        <?php
                        }
                    } ?>
                </select>&nbsp;<font class="error">*&nbsp;<?php echo $errors['default_template_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#default_email_templates"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">E-mail Padrão de Saída:</td>
            <td>
                <select name="default_email_id">
                    <option value=0 disabled>Selecione Um</option>
                    <?php
                    $sql='SELECT email_id,email,name FROM '.EMAIL_TABLE;
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id,$email,$name) = db_fetch_row($res)){
                            $email=$name?"$name &lt;$email&gt;":$email;
                            ?>
                            <option value="<?php echo $id; ?>"<?php echo ($config['default_email_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
                        <?php
                        }
                    } ?>
                 </select>
                 &nbsp;<font class="error">*&nbsp;<?php echo $errors['default_email_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#default_system_email"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">Alerta de E-mail Padrão:</td>
            <td>
                <select name="alert_email_id">
                    <option value="0" selected="selected">Use do Sistema de e-mail padrão</option>
                    <?php
                    $sql='SELECT email_id,email,name FROM '.EMAIL_TABLE.' WHERE email_id != '.db_input($config['default_email_id']);
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while (list($id,$email,$name) = db_fetch_row($res)){
                            $email=$name?"$name &lt;$email&gt;":$email;
                            ?>
                            <option value="<?php echo $id; ?>"<?php echo ($config['alert_email_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
                        <?php
                        }
                    } ?>
                 </select>
                 &nbsp;<font class="error">*&nbsp;<?php echo $errors['alert_email_id']; ?></font>
                <i class="help-tip icon-question-sign" href="#default_alert_email"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">E-mail do Admin</td>
            <td>
                <input type="text" size=40 name="admin_email" value="<?php echo $config['admin_email']; ?>">
                    &nbsp;<font class="error">*&nbsp;<?php echo $errors['admin_email']; ?></font>
                <i class="help-tip icon-question-sign" href="#admins_email_address"></i>
            </td>
        </tr>
        <tr><th colspan=2><em><strong>Entrada de E-mail:</strong>&nbsp;
            </em></th>
        <tr>
            <td width="180">Busca de E-mail:</td>
            <td><input type="checkbox" name="enable_mail_polling" value=1 <?php echo $config['enable_mail_polling']? 'checked="checked"': ''; ?>  > Habilitar
                <i class="help-tip icon-question-sign" href="#email_fetching"></i>
                &nbsp;
                 <input type="checkbox" name="enable_auto_cron" <?php echo $config['enable_auto_cron']?'checked="checked"':''; ?>>
                 Buscar usando auto-cron&nbsp;
                <i class="help-tip icon-question-sign" href="#enable_autocron_fetch"></i>
            </td>
        </tr>
        <tr>
            <td width="180">Remover Resposta Citada:</td>
            <td>
                <input type="checkbox" name="strip_quoted_reply" <?php echo $config['strip_quoted_reply'] ? 'checked="checked"':''; ?>>
                Habilitar <i class="help-tip icon-question-sign" href="#strip_quoted_reply"></i>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['strip_quoted_reply']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180">Tag Separadora de Resposta:</td>
            <td><input type="text" name="reply_separator" value="<?php echo $config['reply_separator']; ?>">
                &nbsp;<font class="error">&nbsp;<?php echo $errors['reply_separator']; ?></font>&nbsp;<i class="help-tip icon-question-sign" href="#reply_separator_tag"></i>
            </td>
        </tr>
        <tr>
            <td width="180">Prioridade dos Tickets:</td>
            <td>
                <input type="checkbox" name="use_email_priority" value="1" <?php echo $config['use_email_priority'] ?'checked="checked"':''; ?> >&nbsp;Habilitar&nbsp;
                <i class="help-tip icon-question-sign" href="#emailed_tickets_priority"></i>
            </td>
        </tr>	
        <tr>
            <td width="180">Aceitar Todos E-mails:</td>
            <td><input type="checkbox" name="accept_unregistered_email" <?php
                echo $config['accept_unregistered_email'] ? 'checked="checked"' : ''; ?>/>
                Aceitar e-mails de usuários desconhecidos
                <i class="help-tip icon-question-sign" href="#accept_all_emails"></i>
            </td>
        </tr>
        <tr>
            <td width="180">E-mail de Colaboradores:</td>
            <td><input type="checkbox" name="add_email_collabs" <?php
    echo $config['add_email_collabs'] ? 'checked="checked"' : ''; ?>/>
            Adicionar automaticamente colaboradores no campo de e-mail&nbsp;
            <i class="help-tip icon-question-sign" href="#accept_email_collaborators"></i>
        </tr>
        <tr><th colspan=2><em><strong>Saída de E-mail</strong>: E-mail padrão só se aplica aos e-mails enviados sem configuração SMTP.</em></th></tr>
        <tr><td width="180">Padrão MTA:</td>
            <td>
                <select name="default_smtp_id">
                    <option value=0 selected="selected">Nenhum: Use a função PHP mail</option>
                    <?php
                    $sql=' SELECT email_id, email, name, smtp_host '
                        .' FROM '.EMAIL_TABLE.' WHERE smtp_active = 1';
                    if(($res=db_query($sql)) && db_num_rows($res)) {
                        while (list($id, $email, $name, $host) = db_fetch_row($res)){
                            $email=$name?"$name &lt;$email&gt;":$email;
                            ?>
                            <option value="<?php echo $id; ?>"<?php echo ($config['default_smtp_id']==$id)?'selected="selected"':''; ?>><?php echo $email; ?></option>
                        <?php
                        }
                    } ?>
                 </select>&nbsp;<font class="error">&nbsp;<?php echo $errors['default_smtp_id']; ?></font>
                 <i class="help-tip icon-question-sign" href="#default_mta"></i>
           </td>
       </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input class="button" type="submit" name="submit" value="Salvar">
    <input class="button" type="reset" name="reset" value="Redefinir">
</p>
</form>

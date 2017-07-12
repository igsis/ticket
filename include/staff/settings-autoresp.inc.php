<h2>Autoresponder Settings</h2>
<form action="settings.php?t=autoresp" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="autoresp" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4>Configurações da Auto-Resposta</h4>
                <em>Configuração Global - pode ser desativado no departamento ou nível de e-mail.</em>
            </th>
        </tr>
    </thead>
    <tbody>

        <tr>
            <td width="160">Novo Ticket:</td>
            <td>
                <input type="checkbox" name="ticket_autoresponder" <?php
echo $config['ticket_autoresponder'] ? 'checked="checked"' : ''; ?>/>
                Proprietário do Ticket&nbsp;
                <i class="help-tip icon-question-sign" href="#new_ticket"></i>
            </td>
        </tr>
        <tr>
            <td width="160">Novo Ticket do Pessoal:</td>
            <td>
                <input type="checkbox" name="ticket_notice_active" <?php
echo $config['ticket_notice_active'] ? 'checked="checked"' : ''; ?>/>
                Proprietário do Ticket&nbsp;
                <i class="help-tip icon-question-sign" href="#new_ticket_by_staff"></i>
            </td>
        </tr>
        <tr>
            <td width="160" rowspan="2">Nova Mensagem:</td>
            <td>
                <input type="checkbox" name="message_autoresponder" <?php
echo $config['message_autoresponder'] ? 'checked="checked"' : ''; ?>/>
                Enviado por: Enviar confirmação de recebimento&nbsp;
                <i class="help-tip icon-question-sign" href="#new_message_for_submitter"></i>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="message_autoresponder_collabs" <?php
echo $config['message_autoresponder_collabs'] ? 'checked="checked"' : ''; ?>/>
                Participantes: Enviar novo aviso de atividade&nbsp;
                <i class="help-tip icon-question-sign" href="#new_message_for_participants"></i>
                </div>
            </td>
        </tr>
        <tr>
            <td width="160">Aviso de Limite Ultrapassado:</td>
            <td>
                <input type="checkbox" name="overlimit_notice_active" <?php
echo $config['overlimit_notice_active'] ? 'checked="checked"' : ''; ?>/>
                Ticket Submitter&nbsp;
                <i class="help-tip icon-question-sign" href="#overlimit_notice"></i>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:200px;">
    <input class="button" type="submit" name="submit" value="Salvar">
    <input class="button" type="reset" name="reset" value="Redefinir">
</p>
</form>

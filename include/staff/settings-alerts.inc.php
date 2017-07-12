<h2>Alertas e Avisos
    <i class="help-tip icon-question-sign" href="#page_title"></i></h2>
<form action="settings.php?t=alerts" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="alerts" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th>
                <h4>Alertas e avisos enviados a equipe do ticket "eventos"</h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr><th><em><b>Alerta de Novo Ticket</b>:
            <i class="help-tip icon-question-sign" href="#ticket_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b>Status:</b></em> &nbsp;
                <input type="radio" name="ticket_alert_active"  value="1"
                <?php echo $config['ticket_alert_active']?'checked':''; ?>
                /> Ativar
                <input type="radio" name="ticket_alert_active"  value="0"   <?php echo !$config['ticket_alert_active']?'checked':''; ?> /> Desativar
                &nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['ticket_alert_active']; ?></font></em>
             </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_admin" <?php echo $config['ticket_alert_admin']?'checked':''; ?>> E-mail do Admin  <em>(<?php echo $cfg->getAdminEmail(); ?>)</em>
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_dept_manager" <?php echo $config['ticket_alert_dept_manager']?'checked':''; ?>> Gerente de Departamento
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="ticket_alert_dept_members" <?php echo $config['ticket_alert_dept_members']?'checked':''; ?>> Membros do Departamento
        </tr>
        <tr>
			<td>
                <input type="checkbox" name="ticket_alert_acct_manager" <?php echo $config['ticket_alert_acct_manager']?'checked':''; ?>> Gerente de Contas
            </td>
            <td>
        </tr>
        <tr><th><em><b>Alerta de Nova Mensagem</b>:
            <i class="help-tip icon-question-sign" href="#message_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b>Status:</b></em> &nbsp;
              <input type="radio" name="message_alert_active"  value="1"
              <?php echo $config['message_alert_active']?'checked':''; ?>
              /> Ativar
              &nbsp;&nbsp;
              <input type="radio" name="message_alert_active"  value="0"   <?php echo !$config['message_alert_active']?'checked':''; ?> /> Desativar
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_laststaff" <?php echo $config['message_alert_laststaff']?'checked':''; ?>> Último Reclamante
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_assigned" <?php
              echo $config['message_alert_assigned']?'checked':''; ?>>
              Atribuído ao Funcionário
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="message_alert_dept_manager" <?php
              echo $config['message_alert_dept_manager']?'checked':''; ?>>
              Gerente de Departamento
            </td>
        </tr>
        <tr>
            <td>
                <input type="checkbox" name="message_alert_acct_manager" <?php 
				echo $config['message_alert_acct_manager']?'checked':''; ?>>
				Gerente de Contas
            </td>
        </tr>
        <tr><th><em><b>Alerta de Nova Nota Interna</b>:
            <i class="help-tip icon-question-sign" href="#internal_note_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b>Status:</b></em> &nbsp;
              <input type="radio" name="note_alert_active"  value="1"   <?php echo $config['note_alert_active']?'checked':''; ?> /> Ativar
              &nbsp;&nbsp;
              <input type="radio" name="note_alert_active"  value="0"   <?php echo !$config['note_alert_active']?'checked':''; ?> /> Desativar
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['note_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_laststaff" <?php echo
              $config['note_alert_laststaff']?'checked':''; ?>> Último Reclamante
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_assigned" <?php echo $config['note_alert_assigned']?'checked':''; ?>> Atribuído Funcionário / Equipe
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="note_alert_dept_manager" <?php echo $config['note_alert_dept_manager']?'checked':''; ?>> Gerente de Departamento
            </td>
        </tr>
        <tr><th><em><b>Alerta de Atribuição de Ticket</b>:
            <i class="help-tip icon-question-sign" href="#assignment_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b>Status: </b></em> &nbsp;
              <input name="assigned_alert_active" value="1" type="radio"
                <?php echo $config['assigned_alert_active']?'checked="checked"':''; ?>> Ativar
              &nbsp;&nbsp;
              <input name="assigned_alert_active" value="0" type="radio"
                <?php echo !$config['assigned_alert_active']?'checked="checked"':''; ?>> Desativar
               &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['assigned_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="assigned_alert_staff" <?php echo
              $config['assigned_alert_staff']?'checked':''; ?>> Atribuído Funcionário
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox"name="assigned_alert_team_lead" <?php
              echo $config['assigned_alert_team_lead']?'checked':''; ?>> Líder da Equipe
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox"name="assigned_alert_team_members" <?php echo $config['assigned_alert_team_members']?'checked':''; ?>>
                Membros da Equipe
            </td>
        </tr>
        <tr><th><em><b>Alerta na Transferência</b>:
            <i class="help-tip icon-question-sign" href="#transfer_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b>Status:</b></em> &nbsp;
              <input type="radio" name="transfer_alert_active"  value="1"   <?php echo $config['transfer_alert_active']?'checked':''; ?> /> Ativar
              <input type="radio" name="transfer_alert_active"  value="0"   <?php echo !$config['transfer_alert_active']?'checked':''; ?> /> Desativar
              &nbsp;&nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['alert_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_assigned" <?php echo $config['transfer_alert_assigned']?'checked':''; ?>> Atribuído Funcionário / Equipe
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_dept_manager" <?php echo $config['transfer_alert_dept_manager']?'checked':''; ?>> Gerente de Departamento
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="transfer_alert_dept_members" <?php echo $config['transfer_alert_dept_members']?'checked':''; ?>>
                Membros do Departamento
            </td>
        </tr>
        <tr><th><em><b>Alertas de Tickets em Atraso</b>:
            <i class="help-tip icon-question-sign" href="#overdue_alert"></i>
            </em></th></tr>
        <tr>
            <td><em><b>Status:</b></em> &nbsp;
              <input type="radio" name="overdue_alert_active"  value="1"
                <?php echo $config['overdue_alert_active']?'checked':''; ?> /> Ativar
              <input type="radio" name="overdue_alert_active"  value="0"
                <?php echo !$config['overdue_alert_active']?'checked':''; ?> /> Desativar
              &nbsp;&nbsp;<font class="error">&nbsp;<?php echo $errors['overdue_alert_active']; ?></font>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_assigned" <?php
                echo $config['overdue_alert_assigned']?'checked':''; ?>> Atribuído Funcionário / Equipe
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_dept_manager" <?php
                echo $config['overdue_alert_dept_manager']?'checked':''; ?>> Gerente de Departamento
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="overdue_alert_dept_members" <?php
                echo $config['overdue_alert_dept_members']?'checked':''; ?>> Membros do Departamento
            </td>
        </tr>
        <tr><th>
            <em><b>Sistema de Alertas</b>: <i class="help-tip icon-question-sign" href="#system_alerts"></i></em></th></tr>
        <tr>
            <td>
              <input type="checkbox" name="send_sys_errors" checked="checked" disabled="disabled"> Erros do Sistema
              <em>(ativada por padrão)</em>
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="send_sql_errors" <?php echo $config['send_sql_errors']?'checked':''; ?>> Erros SQL
            </td>
        </tr>
        <tr>
            <td>
              <input type="checkbox" name="send_login_errors" <?php echo $config['send_login_errors']?'checked':''; ?>> Excesso de tentativas de login
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:350px;">
    <input class="button" type="submit" name="submit" value="Salvar">
    <input class="button" type="reset" name="reset" value="Redefinir">
</p>
</form>

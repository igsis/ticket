<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');

$info=array();
$qstr='';
if($staff && $_REQUEST['a']!='add'){
    //Editing Department.
    $title='Atualizar';
    $action='update';
    $submit_text='Salvar';
    $passwd_text='Para redefinir a senha, introduza uma nova abaixo';
    $info=$staff->getInfo();
    $info['id']=$staff->getId();
    $info['teams'] = $staff->getTeams();
    $info['signature'] = Format::viewableImages($info['signature']);
    $qstr.='&id='.$staff->getId();
}else {
    $title='Adicionar Novo Funcionário';
    $action='create';
    $submit_text='Adicionar';
    $passwd_text='Senha temporária necessária apenas para autenticação "Local"';
    //Some defaults for new staff.
    $info['change_passwd']=1;
    $info['welcome_email']=1;
    $info['isactive']=1;
    $info['isvisible']=1;
    $info['isadmin']=0;
    $info['timezone_id'] = $cfg->getDefaultTimezoneId();
    $info['daylight_saving'] = $cfg->observeDaylightSaving();
    $qstr.='&a=add';
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="staff.php?<?php echo $qstr; ?>" method="post" id="save" autocomplete="off">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2>Funcionário</h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><strong>Informação do Usuário</strong></em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                Nome de Usuário:
            </td>
            <td>
                <input type="text" size="30" class="staff-username typeahead"
                     name="username" value="<?php echo $info['username']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['username']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#username"></i>
            </td>
        </tr>

        <tr>
            <td width="180" class="required">
                Primeiro Nome:
            </td>
            <td>
                <input type="text" size="30" name="firstname" class="auto first"
                     value="<?php echo $info['firstname']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['firstname']; ?></span>&nbsp;
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Último Nome:
            </td>
            <td>
                <input type="text" size="30" name="lastname" class="auto last"
                    value="<?php echo $info['lastname']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['lastname']; ?></span>&nbsp;
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Endereço de Email:
            </td>
            <td>
                <input type="text" size="30" name="email" class="auto email"
                    value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#email_address"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                Telefone Res.:
            </td>
            <td>
                <input type="text" size="18" name="phone" class="auto phone"
                    value="<?php echo $info['phone']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone']; ?></span>
                Ext <input type="text" size="5" name="phone_ext" value="<?php echo $info['phone_ext']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone_ext']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                Telefone Celular:
            </td>
            <td>
                <input type="text" size="18" name="mobile" class="auto mobile"
                    value="<?php echo $info['mobile']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['mobile']; ?></span>
            </td>
        </tr>
<?php if (!$staff) { ?>
        <tr>
            <td width="180">E-mail de Boas Vindas</td>
            <td><input type="checkbox" name="welcome_email" id="welcome-email" <?php
                if ($info['welcome_email']) echo 'checked="checked"';
                ?> onchange="javascript:
                var sbk = $('#backend-selection');
                if ($(this).is(':checked'))
                    $('#password-fields').hide();
                else if (sbk.val() == '' || sbk.val() == 'local')
                    $('#password-fields').show();
                " />
                 Enviar informação da conta
                &nbsp;<i class="help-tip icon-question-sign" href="#welcome_email"></i>
            </td>
        </tr>
<?php } ?>
        <tr>
            <th colspan="2">
                <em><strong>Autenticação</strong>: <?php echo $passwd_text; ?> &nbsp;<span class="error">&nbsp;<?php echo $errors['temppasswd']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#account_password"></i></em>
            </th>
        </tr>
        <tr>
            <td>Autenticação Backend</td>
            <td>
            <select name="backend" id="backend-selection" onchange="javascript:
                if (this.value != '' && this.value != 'local')
                    $('#password-fields').hide();
                else if (!$('#welcome-email').is(':checked'))
                    $('#password-fields').show();
                ">
                <option value="">&mdash; Use qualquer backend disponível &mdash;</option>
            <?php foreach (StaffAuthenticationBackend::allRegistered() as $ab) {
                if (!$ab->supportsInteractiveAuthentication()) continue; ?>
                <option value="<?php echo $ab::$id; ?>" <?php
                    if ($info['backend'] == $ab::$id)
                        echo 'selected="selected"'; ?>><?php
                    echo $ab::$name; ?></option>
            <?php } ?>
            </select>
            </td>
        </tr>
    </tbody>
    <tbody id="password-fields" style="<?php
        if ($info['welcome_email'] || ($info['backend'] && $info['backend'] != 'local'))
            echo 'display:none;'; ?>">
        <tr>
            <td width="180">
                Senha:
            </td>
            <td>
                <input type="password" size="18" name="passwd1" value="<?php echo $info['passwd1']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd1']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                Confirmar Senha:
            </td>
            <td>
                <input type="password" size="18" name="passwd2" value="<?php echo $info['passwd2']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd2']; ?></span>
            </td>
        </tr>

        <tr>
            <td width="180">
                Forçar Mudança de Senha:
            </td>
            <td>
                <input type="checkbox" name="change_passwd" value="0" <?php echo $info['change_passwd']?'checked="checked"':''; ?>>
                <strong>Forçar</strong>mudança de senha no próximo login.&nbsp;<i class="help-tip icon-question-sign" href="#forced_password_change"></i>
            </td>
        </tr>
    </tbody>
    <tbody>
        <tr>
            <th colspan="2">
                <em><strong>Assinatura do Funcionário</strong>: Assinatura opcional usado em e-mails enviados. &nbsp;<span class="error">&nbsp;<?php echo $errors['signature']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#agents_signature"></i></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea class="richtext no-bar" name="signature" cols="21"
                    rows="5" style="width: 60%;"><?php echo $info['signature']; ?></textarea>
                <br><em>Assinatura é disponibilizado como uma escolha, em resposta ao ticket.</em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Conta & Configurações </strong>: Departamento e grupo atribuído controla as permissões de acesso.</em>
            </th>
        </tr>
        <tr>
            <td width="180" class="required">
                Tipo de Conta:
            </td>
            <td>
                <input type="radio" name="isadmin" value="1" <?php echo $info['isadmin']?'checked="checked"':''; ?>>
                    <font color="red"><strong>Admin</strong></font>
                <input type="radio" name="isadmin" value="0" <?php echo !$info['isadmin']?'checked="checked"':''; ?>><strong>Funcionário</strong>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['isadmin']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Status da Conta:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong>Ativado</strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>><strong>Bloqueado</strong>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['isactive']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#account_status"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Grupo Atribuído:
            </td>
            <td>
                <select name="group_id" id="group_id">
                    <option value="0">&mdash; Selecionar Grupo &mdash;</option>
                    <?php
                    $sql='SELECT group_id, group_name, group_enabled as isactive FROM '.GROUP_TABLE.' ORDER BY group_name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name,$isactive)=db_fetch_row($res)){
                            $sel=($info['group_id']==$id)?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s %s</option>',$id,$sel,$name,($isactive?'':' (Disabled)'));
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['group_id']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#assigned_group"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Departamento Principal:
            </td>
            <td>
                <select name="dept_id" id="dept_id">
                    <option value="0">&mdash; Selecionar Departamento &mdash;</option>
                    <?php
                    $sql='SELECT dept_id, dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$name)=db_fetch_row($res)){
                            $sel=($info['dept_id']==$id)?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$sel,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['dept_id']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#primary_department"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Fuso Horário:
            </td>
            <td>
                <select name="timezone_id" id="timezone_id">
                    <option value="0">&mdash; Selecione o Fuso Horário &mdash;</option>
                    <?php
                    $sql='SELECT id, offset,timezone FROM '.TIMEZONE_TABLE.' ORDER BY id';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$offset, $tz)=db_fetch_row($res)){
                            $sel=($info['timezone_id']==$id)?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>GMT %s - %s</option>',$id,$sel,$offset,$tz);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['timezone_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
               Horário de Verão:
            </td>
            <td>
                <input type="checkbox" name="daylight_saving" value="1" <?php echo $info['daylight_saving']?'checked="checked"':''; ?>>
                Observe o horário de verão
                <em>(Hora Atual: <strong><?php echo Format::date($cfg->getDateTimeFormat(),Misc::gmtime(),$info['tz_offset'],$info['daylight_saving']); ?></strong>)&nbsp;<i class="help-tip icon-question-sign" href="#daylight_saving"></i></em>
            </td>
        </tr>
        <tr>
            <td width="180">
                Acesso Limitado:
            </td>
            <td>
                <input type="checkbox" name="assigned_only" value="1" <?php echo $info['assigned_only']?'checked="checked"':''; ?>>Limitado APENAS a tickets atribuídos.&nbsp;<i class="help-tip icon-question-sign" href="#limited_access"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                Listagem de Diretório:
            </td>
            <td>
                <input type="checkbox" name="isvisible" value="1" <?php echo $info['isvisible']?'checked="checked"':''; ?>>&nbsp;Tornar visível no Diretório de Pessoal&nbsp;<i class="help-tip icon-question-sign" href="#directory_listing"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                Modo de Férias:
            </td>
            <td>
                <input type="checkbox" name="onvacation" value="1" <?php echo $info['onvacation']?'checked="checked"':''; ?>>
                    Muda o Status para Modo de Férias&nbsp;<i class="help-tip icon-question-sign" href="#vacation_mode"></i>
            </td>
        </tr>
        <?php
         //List team assignments.
         $sql='SELECT team.team_id, team.name, isenabled FROM '.TEAM_TABLE.' team  ORDER BY team.name';
         if(($res=db_query($sql)) && db_num_rows($res)){ ?>
        <tr>
            <th colspan="2">
                <em><strong>Times Atribuídos: </strong>: Os funcionários terão acesso a tickets atribuídos a equipe que pertencem, independentemente do departamento do ticket. </em>
            </th>
        </tr>
        <?php
         while(list($id,$name,$isactive)=db_fetch_row($res)){
             $checked=($info['teams'] && in_array($id,$info['teams']))?'checked="checked"':'';
             echo sprintf('<tr><td colspan=2><input type="checkbox" name="teams[]" value="%d" %s>%s %s</td></tr>',
                     $id,$checked,$name,($isactive?'':' (Disabled)'));
         }
        } ?>
        <tr>
            <th colspan="2">
                <em><strong>Notas do Admin</strong></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea class="richtext no-bar" name="notes" cols="28"
                    rows="7" style="width: 80%;"><?php echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="Redefinir">
    <input type="button" name="cancel" value="Cancelar" onclick='window.location.href="staff.php"'>
</p>
</form>

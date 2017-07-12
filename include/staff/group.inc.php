<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info=array();
$qstr='';
if($group && $_REQUEST['a']!='add'){
    $title='Atualizar';
    $action='update';
    $submit_text='Salvar';
    $info=$group->getInfo();
    $info['id']=$group->getId();
    $info['depts']=$group->getDepartments();
    $qstr.='&id='.$group->getId();
}else {
    $title='Adicionar Novo Grupo';
    $action='create';
    $submit_text='Criar';
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['can_create_tickets']=isset($info['can_create_tickets'])?$info['can_create_tickets']:1;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="groups.php?<?php echo $qstr; ?>" method="post" id="save" name="group">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2>Grupo</h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><strong>Informações do Grupo</strong>: Grupo desativado irá limitar o acesso pessoal. Administradores são isentos.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                Nome:
            </td>
            <td>
                <input type="text" size="30" name="name" value="<?php echo $info['name']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Status:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong>Ativado</strong>
                &nbsp;
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>>Desativado
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['status']; ?></span>
                <i class="help-tip icon-question-sign" href="#status"></i>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Permissões do Grupo</strong>: >: Aplica-se a todos os membros do grupo&nbsp;</em>
            </th>
        </tr>
        <tr><td>Pode <b>Criar</b> Tickets</td>
            <td>
                <input type="radio" name="can_create_tickets"  value="1"   <?php echo $info['can_create_tickets']?'checked="checked"':''; ?> />Sim
                &nbsp;&nbsp;
                <input type="radio" name="can_create_tickets"  value="0"   <?php echo !$info['can_create_tickets']?'checked="checked"':''; ?> />Não
                &nbsp;&nbsp;<i>Habilidade de abrir tickets em nome dos usuários.</i>
            </td>
        </tr>
        <tr><td>Pode <b>Editar</b> Tickets</td>
            <td>
                <input type="radio" name="can_edit_tickets"  value="1"   <?php echo $info['can_edit_tickets']?'checked="checked"':''; ?> />Sim
                &nbsp;&nbsp;
                <input type="radio" name="can_edit_tickets"  value="0"   <?php echo !$info['can_edit_tickets']?'checked="checked"':''; ?> />Não
                &nbsp;&nbsp;<i>Habilidade de editar tickets.</i>
            </td>
        </tr>
        <tr><td>Pode <b>Responder</b></td>
            <td>
                <input type="radio" name="can_post_ticket_reply"  value="1"   <?php echo $info['can_post_ticket_reply']?'checked="checked"':''; ?> />Sim
                &nbsp;&nbsp;
                <input type="radio" name="can_post_ticket_reply"  value="0"   <?php echo !$info['can_post_ticket_reply']?'checked="checked"':''; ?> />Não
                &nbsp;&nbsp;<i>Habilidade de responder ticket.</i>
            </td>
        </tr>
        <tr><td>Pode <b>Fechar</b> Tickets</td>
            <td>
                <input type="radio" name="can_close_tickets"  value="1" <?php echo $info['can_close_tickets']?'checked="checked"':''; ?> />Sim
                &nbsp;&nbsp;
                <input type="radio" name="can_close_tickets"  value="0" <?php echo !$info['can_close_tickets']?'checked="checked"':''; ?> />Não
                &nbsp;&nbsp;<i>Habilidade de fechar tickets. Os funcionários ainda pode postar uma resposta.</i>
            </td>
        </tr>
        <tr><td>Pode <b>Atribuir</b> Tickets</td>
            <td>
                <input type="radio" name="can_assign_tickets"  value="1" <?php echo $info['can_assign_tickets']?'checked="checked"':''; ?> />Sim
                &nbsp;&nbsp;
                <input type="radio" name="can_assign_tickets"  value="0" <?php echo !$info['can_assign_tickets']?'checked="checked"':''; ?> />Não
                &nbsp;&nbsp;<i>Habilidade de atribuir tickets para os funcionários.</i>
            </td>
        </tr>
        <tr><td>Pode <b>Transferir</b> Tickets</td>
            <td>
                <input type="radio" name="can_transfer_tickets"  value="1" <?php echo $info['can_transfer_tickets']?'checked="checked"':''; ?> />Sim
                &nbsp;&nbsp;
                <input type="radio" name="can_transfer_tickets"  value="0" <?php echo !$info['can_transfer_tickets']?'checked="checked"':''; ?> />Não
                &nbsp;&nbsp;<i>Habilidade de transferir tickets entre departamentos.</i>
            </td>
        </tr>
        <tr><td>Pode <b>Excluir</b> Tickets</td>
            <td>
                <input type="radio" name="can_delete_tickets"  value="1"   <?php echo $info['can_delete_tickets']?'checked="checked"':''; ?> />Sim
                &nbsp;&nbsp;
                <input type="radio" name="can_delete_tickets"  value="0"   <?php echo !$info['can_delete_tickets']?'checked="checked"':''; ?> />Não
                &nbsp;&nbsp;<i>Habilidade de excluir tickets (Tickets excluídos não podem ser recuperados!)</i>
            </td>
        </tr>
        <tr><td>Pode Banir Emails</td>
            <td>
                <input type="radio" name="can_ban_emails"  value="1" <?php echo $info['can_ban_emails']?'checked="checked"':''; ?> />Sim
                &nbsp;&nbsp;
                <input type="radio" name="can_ban_emails"  value="0" <?php echo !$info['can_ban_emails']?'checked="checked"':''; ?> />Não
                &nbsp;&nbsp;<i>Habilidade de adicionar/remover e-mails banidos via interface.</i>
            </td>
        </tr>
        <tr><td>Pode Gerenciar Modelo</td>
            <td>
                <input type="radio" name="can_manage_premade"  value="1" <?php echo $info['can_manage_premade']?'checked="checked"':''; ?> />Sim
                &nbsp;&nbsp;
                <input type="radio" name="can_manage_premade"  value="0" <?php echo !$info['can_manage_premade']?'checked="checked"':''; ?> />Não
                &nbsp;&nbsp;<i>Habilidade de adicionar/atualizar/desativar/excluir respostas modelo e anexos.</i>
            </td>
        </tr>
        <tr><td>Pode Gerenciar FAQ</td>
            <td>
                <input type="radio" name="can_manage_faq"  value="1" <?php echo $info['can_manage_faq']?'checked="checked"':''; ?> />Sim
                &nbsp;&nbsp;
                <input type="radio" name="can_manage_faq"  value="0" <?php echo !$info['can_manage_faq']?'checked="checked"':''; ?> />Não
                &nbsp;&nbsp;<i>Habilidade de adicionar/atualizar/desativar/excluir categorias da Base de Conhecimento e FAQs.</i>
            </td>
        </tr>
        <tr><td>Pode Visualizar Estatística.</td>
            <td>
                <input type="radio" name="can_view_staff_stats"  value="1" <?php echo $info['can_view_staff_stats']?'checked="checked"':''; ?> />Sim
                &nbsp;&nbsp;
                <input type="radio" name="can_view_staff_stats"  value="0" <?php echo !$info['can_view_staff_stats']?'checked="checked"':''; ?> />Não
                &nbsp;&nbsp;<i>Habilidade de visualizar estatísticas de outros membros da equipe em departamentos permitidos.</i>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Acesso de departamento</strong>:
                <i class="help-tip icon-question-sign" href="#department_access"></i>
                &nbsp;<a id="selectAll" href="#deptckb">Selecionar Todos</a>
                &nbsp;&nbsp;
                <a id="selectNone" href="#deptckb">Selecionar Nenhum</a></em>
            </th>
        </tr>
        <?php
         $sql='SELECT dept_id,dept_name FROM '.DEPT_TABLE.' ORDER BY dept_name';
         if(($res=db_query($sql)) && db_num_rows($res)){
            while(list($id,$name) = db_fetch_row($res)){
                $ck=($info['depts'] && in_array($id,$info['depts']))?'checked="checked"':'';
                echo sprintf('<tr><td colspan=2>&nbsp;&nbsp;<input type="checkbox" class="deptckb" name="depts[]" value="%d" %s>%s</td></tr>',$id,$ck,$name);
            }
         }
        ?>
        <tr>
            <th colspan="2">
                <em><strong>Notas do Admin</strong>: Notas internas visíveis por todos os administradores.&nbsp;</em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea class="richtext no-bar" name="notes" cols="21"
                    rows="8" style="width: 80%;"><?php echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="text-align:center">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="Redefinir">
    <input type="button" name="cancel" value="Cancelar" onclick='window.location.href="groups.php"'>
</p>
</form>

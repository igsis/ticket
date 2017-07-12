<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info=array();
$qstr='';
if($sla && $_REQUEST['a']!='add'){
    $title='Atualizar';
    $action='update';
    $submit_text='Salvar';
    $info=$sla->getInfo();
    $info['id']=$sla->getId();
    $qstr.='&id='.$sla->getId();
}else {
    $title='Adicionar Novo Plano de SLA';
    $action='add';
    $submit_text='Adicionar';
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $info['enable_priority_escalation']=isset($info['enable_priority_escalation'])?$info['enable_priority_escalation']:1;
    $info['disable_overdue_alerts']=isset($info['disable_overdue_alerts'])?$info['disable_overdue_alerts']:0;
    $qstr.='&a='.urlencode($_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="slas.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2>Acordo de Nível de Serviço</h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em>TIckets são marcados como atraso após período de carência.</em>
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
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['name']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#name"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
              Período de Carência:
            </td>
            <td>
                <input type="text" size="10" name="grace_period" value="<?php echo $info['grace_period']; ?>">
                <em>( em horas )</em>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['grace_period']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#grace_period"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Status:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong>Ativado</strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>>Desativado
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['isactive']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                Transitório
            </td>
            <td>
                <input type="checkbox" name="transient" value="1" <?php echo $info['transient']?'checked="checked"':''; ?> >               
				SLA pode ser substituído na transferência de ticket ou na mudança de tópico&nbsp;<i class="help-tip icon-question-sign" href="#transient"></i>
            </td>
        </tr>
        <tr>
            <td width="180">
                Ticket Overdue Alerts:
            </td>
            <td>
                <input type="checkbox" name="disable_overdue_alerts" value="1" <?php echo $info['disable_overdue_alerts']?'checked="checked"':''; ?> >
                    <strong>Desativar</strong>  alertas de avisos vencido.
                    <em>(Substituição de configuração global)</em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Notas do Admin </strong>: Notas interna.&nbsp;&nbsp;<i class="help-tip icon-question-sign" href="#admin_notes"></i></em>
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
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="Redefinir">
    <input type="button" name="cancel" value="Cancelar" onclick='window.location.href="slas.php"'>
</p>
</form>

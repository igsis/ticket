<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info=array();
$qstr='';
if($api && $_REQUEST['a']!='add'){
    $title='Atualizar';
    $action='update';
    $submit_text='Salvar';
    $info=$api->getHashtable();
    $qstr.='&id='.$api->getId();
}else {
    $title='Adicionar Nova Chave de API';
    $action='add';
    $submit_text='Adicionar';
    $info['isactive']=isset($info['isactive'])?$info['isactive']:1;
    $qstr.='&a='.urlencode($_REQUEST['a']);
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="apikeys.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2>Chave de API
    <i class="help-tip icon-question-sign" href="#api_key"></i>
    </h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em>Chave de API é gerado automaticamente. Excluir e adicionar novamente alterar a chave.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="150" class="required">
                Status:
            </td>
            <td>
                <input type="radio" name="isactive" value="1" <?php echo $info['isactive']?'checked="checked"':''; ?>><strong>Ativado</strong>
                <input type="radio" name="isactive" value="0" <?php echo !$info['isactive']?'checked="checked"':''; ?>>Desativado
                &nbsp;<span class="error">*&nbsp;</span>
            </td>
        </tr>
        <?php if($api){ ?>
        <tr>
            <td width="150">
                Endereço IP:
            </td>
            <td>
                <span>
                <?php echo $api->getIPAddr(); ?>
                <i class="help-tip icon-question-sign" href="#ip_addr"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td width="150">
                Chave de API:
            </td>
            <td><?php echo $api->getKey(); ?> &nbsp;</td>
        </tr>
        <?php }else{ ?>
        <tr>
            <td width="150" class="required">
               Endereço IP:
            </td>
            <td>
                <span>
                <input type="text" size="30" name="ipaddr" value="<?php echo $info['ipaddr']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['ipaddr']; ?></span>
                <i class="help-tip icon-question-sign" href="#ip_addr"></i>
                </span>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th colspan="2">
                <em><strong>Serviços:</strong>: Confira os serviços API aplicáveis ​​habilitados para a chave.</em>
            </th>
        </tr>
        <tr>
            <td colspan=2 style="padding-left:5px">
                <label>
                    <input type="checkbox" name="can_create_tickets" value="1" <?php echo $info['can_create_tickets']?'checked="checked"':''; ?> >
                    Pode Criar Tickets <em>(XML/JSON/EMAIL)</em>
                </label>
            </td>
        </tr>
        <tr>
            <td colspan=2 style="padding-left:5px">
                <label>
                    <input type="checkbox" name="can_exec_cron" value="1" <?php echo $info['can_exec_cron']?'checked="checked"':''; ?> >
                    Pode Executar Cron
                </label>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Notas do Admin</strong>: Notas Interna.&nbsp;</em>
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
    <input type="button" name="cancel" value="Cancelar" onclick='window.location.href="apikeys.php"'>
</p>
</form>

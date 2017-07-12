<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$info=array();
$qstr='';
if($team && $_REQUEST['a']!='add'){
    //Editing Team
    $title='Update Team';
    $action='update';
    $submit_text='Salvar';
    $info=$team->getInfo();
    $info['id']=$team->getId();
    $qstr.='&id='.$team->getId();
}else {
    $title='Adicionar Nova Equipe';
    $action='create';
    $submit_text='Criar';
    $info['isenabled']=1;
    $info['noalerts']=0;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<form action="teams.php?<?php echo $qstr; ?>" method="post" id="save">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2>Equipes
    <i class="help-tip icon-question-sign" href="#teams"></i>
    </h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em><strong>Informações da Equipe</strong>:</em>
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
                <span>
                <input type="radio" name="isenabled" value="1" <?php echo $info['isenabled']?'checked="checked"':''; ?>><strong>Ativado</strong>
                &nbsp;
                <input type="radio" name="isenabled" value="0" <?php echo !$info['isenabled']?'checked="checked"':''; ?>>Desativado
                &nbsp;<span class="error">*&nbsp;</span>
                <i class="help-tip icon-question-sign" href="#status"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td width="180">
                Líder de Equipe:
            </td>
            <td>
                <span>
                <select name="lead_id">
                    <option value="0">&mdash; Nenhum &mdash;</option>
                    <option value="" disabled="disabled">Selecione Líder (Opcional)</option>
                    <?php
                    if($team && ($members=$team->getMembers())){
                        foreach($members as $k=>$staff){
                            $selected=($info['lead_id'] && $staff->getId()==$info['lead_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$staff->getId(),$selected,$staff->getName());
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error"><?php echo $errors['lead_id']; ?></span>
                <i class="help-tip icon-question-sign" href="#lead"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td width="180">
                Alerta de Atribuição:
            </td>
            <td>
                <input type="checkbox" name="noalerts" value="1" <?php echo $info['noalerts']?'checked="checked"':''; ?> >
                <strong>Desativar</strong> para esta Equipe
                <i class="help-tip icon-question-sign" href="#assignment_alert"></i>
            </td>
        </tr>
        <?php
        if($team && ($members=$team->getMembers())){ ?>
        <tr>
            <th colspan="2">
                <em><strong>Membros da Equipe</strong>:
                <i class="help-tip icon-question-sign" href="#members"></i>
</em>
            </th>
        </tr>
        <?php
            foreach($members as $k=>$staff){
                echo sprintf('<tr><td colspan=2><span style="width:350px;padding-left:5px; display:block; float:left;">
                            <b><a href="staff.php?id=%d">%s</a></span></b>
                            &nbsp;<input type="checkbox" name="remove[]" value="%d"><i>Remove</i></td></tr>',
                          $staff->getId(),$staff->getName(),$staff->getId());


            }
        } ?>
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
    <input type="button" name="cancel" value="Cancelar" onclick='window.location.href="teams.php"'>
</p>
</form>

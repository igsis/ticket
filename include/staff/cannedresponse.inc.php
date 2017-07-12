<?php
if(!defined('OSTSCPINC') || !$thisstaff) die('Access Denied');
$info=array();
$qstr='';
if($canned && $_REQUEST['a']!='add'){
    $title='Atualizar';
    $action='update';
    $submit_text='Salvar';
    $info=$canned->getInfo();
    $info['id']=$canned->getId();
    $qstr.='&id='.$canned->getId();
    // Replace cid: scheme with downloadable URL for inline images
    $info['response'] = $canned->getResponseWithImages();
    $info['notes'] = Format::viewableImages($info['notes']);
}else {
    $title='Adicionar Nova Resposta Pronta';
    $action='create';
    $submit_text='Adicionar';
    $info['isenabled']=isset($info['isenabled'])?$info['isenabled']:1;
    $qstr.='&a='.$_REQUEST['a'];
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>
<form action="canned.php?<?php echo $qstr; ?>" method="post" id="save" enctype="multipart/form-data">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="<?php echo $action; ?>">
 <input type="hidden" name="a" value="<?php echo Format::htmlchars($_REQUEST['a']); ?>">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2>Resposta Pronta&nbsp;<i class="help-tip icon-question-sign" href="#canned_response"></i></h2>
 <table class="form_table fixed" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr><td></td><td></td></tr> <!-- For fixed table layout -->
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em>Configurações da Resposta Pronta</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">Status:</td>
            <td>
                <label><input type="radio" name="isenabled" value="1" <?php echo $info['isenabled']?'checked="checked"':''; ?>>&nbsp;Ativado&nbsp;</label>
                <label><input type="radio" name="isenabled" value="0" <?php echo !$info['isenabled']?'checked="checked"':''; ?>>&nbsp;Desativado&nbsp;</label>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['isenabled']; ?></span>
                &nbsp;<i class="help-tip icon-question-sign" href="#status"></i>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">Departamento:</td>
            <td>
                <select name="dept_id">
                    <option value="0">&mdash; Todos Departamentos &mdash;</option>
                    <?php
                    $sql='SELECT dept_id, dept_name FROM '.DEPT_TABLE.' dept ORDER by dept_name';
                    if(($res=db_query($sql)) && db_num_rows($res)) {
                        while(list($id,$name)=db_fetch_row($res)) {
                            $selected=($info['dept_id'] && $id==$info['dept_id'])?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>%s</option>',$id,$selected,$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['dept_id']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Reposta Pronta</strong>: Faça um título curto e claro.&nbsp;</em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <div><b>Título.</b><span class="error">*&nbsp;<?php echo $errors['title']; ?></span></div>
                <input type="text" size="70" name="title" value="<?php echo $info['title']; ?>">
                <br><br><div style="margin-bottom:0.5em"><b>Resposta Pronta</b> <font class="error">*&nbsp;<?php echo $errors['response']; ?></font>
                    &nbsp;&nbsp;&nbsp;(<a class="tip" href="#ticket_variables">Variáveis ​​Suportadas</a>)
                    </div>
                <textarea name="response" class="richtext draft draft-delete" cols="21" rows="12"
                    data-draft-namespace="canned"
                    data-draft-object-id="<?php if (isset($canned)) echo $canned->getId(); ?>"
                    style="width:98%;" class="richtext draft"><?php
                        echo $info['response']; ?></textarea>
                <br><br><div><b>Anexos Pronto</b> (opcional) &nbsp;<i class="help-tip icon-question-sign" href="#canned_attachments"></i><font class="error">&nbsp;<?php echo $errors['files']; ?></font></div>
                <?php
                if($canned && ($files=$canned->attachments->getSeparates())) {
                    echo '<div id="canned_attachments"><span class="faded">Uncheck to delete the attachment on submit</span><br>';
                    foreach($files as $file) {
                        $hash=$file['key'].md5($file['id'].session_id().strtolower($file['key']));
                        echo sprintf('<label><input type="checkbox" name="files[]" id="f%d" value="%d" checked="checked">
                                      <a href="file.php?h=%s">%s</a>&nbsp;&nbsp;</label>&nbsp;',
                                      $file['id'], $file['id'], $hash, $file['name']);
                    }
                    echo '</div><br>';

                }
                //Hardcoded limit... TODO: add a setting on admin panel - what happens on tickets page??
                if(count($files)<10) {
                ?>
                <div>
                    <input type="file" name="attachments[]" value=""/>
                </div>
                <?php
                }?>
                <div class="faded">Você pode adicionar até 10 anexos por resposta.</div>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Notas Interna</strong>: Notas sobre a resposta pronta.&nbsp;</em>
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
 <?php if ($canned && $canned->getFilters()) { ?>
    <br/>
    <div id="msg_warning">Canned response is in use by email filter(s): <?php
    echo implode(', ', $canned->getFilters()); ?></div>
 <?php } ?>
<p style="padding-left:225px;">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="Redefinir" onclick="javascript:
        $(this.form).find('textarea.richtext')
            .redactor('deleteDraft');
        location.reload();" />
    <input type="button" name="cancel" value="Cancelar" onclick='window.location.href="canned.php"'>
</p>
</form>

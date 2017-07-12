<?php

$info=array();
if($form && $_REQUEST['a']!='add') {
    $title = 'Atualizar';
    $action = 'update';
    $url = "?id=".urlencode($_REQUEST['id']);
    $submit_text='Salvar';
    $info = $form->ht;
    $newcount=2;
} else {
    $title = 'Adicionar Novo Formulário Personalizado';
    $action = 'add';
    $url = '?a=add';
    $submit_text='Adicionar';
    $newcount=4;
}
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

?>
<form class="manage-form" action="<?php echo $url ?>" method="post" id="save">
    <?php csrf_token(); ?>
    <input type="hidden" name="do" value="<?php echo $action; ?>">
    <input type="hidden" name="a" value="<?php echo $action; ?>">
    <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
    <h2>Formulário Personalizado</h2>
    <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4><?php echo $title; ?></h4>
                <em>Os formulários personalizados são usados ​​para permitir que os dados personalizados sejam associado ao bilhete</em>
            </th>
        </tr>
    </thead>
    <tbody style="vertical-align:top">
        <tr>
            <td width="180" class="required">Título:</td>
            <td><input type="text" name="title" size="40" value="<?php
                echo $info['title']; ?>"/>
                <i class="help-tip icon-question-sign" href="#form_title"></i>
                <font class="error"><?php
                    if ($errors['title']) echo '<br/>'; echo $errors['title']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180">Instruções:</td>
            <td><textarea name="instructions" rows="3" cols="40"><?php
                echo $info['instructions']; ?></textarea>
                <i class="help-tip icon-question-sign" href="#form_instructions"></i>
            </td>
        </tr>
    </tbody>
    </table>
    <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <?php if ($form && $form->get('type') == 'T') { ?>
    <thead>
        <tr>
            <th colspan="7">
                <em><strong>Informações sobre o Usuário</strong> mais informações aqui</em>
            </th>
        </tr>
        <tr>
            <th></th>
            <th>Rótulo</th>
            <th>Tipo</th>
            <th>Interno</th>
            <th>Exigido</th>
            <th>Variável</th>
            <th>Excluir</th>
        </tr>
    </thead>
    <tbody>
    <?php
        $uform = UserForm::objects()->all();
        $ftypes = FormField::allTypes();
        foreach ($uform[0]->getFields() as $f) {
            if ($f->get('private')) continue;
        ?>
        <tr>
            <td></td>
            <td><?php echo $f->get('label'); ?></td>
            <td><?php $t=FormField::getFieldType($f->get('type')); echo $t[0]; ?></td>
            <td><input type="checkbox" disabled="disabled"/></td>
            <td><input type="checkbox" disabled="disabled"
                <?php echo $f->get('required') ? 'checked="checked"' : ''; ?>/></td>
            <td><?php echo $f->get('name'); ?></td>
            <td><input type="checkbox" disabled="disabled"/></td></tr>

        <?php } ?>
    </tbody>
    <?php } # form->type == 'T' ?>
    <thead>
        <tr>
            <th colspan="7">
                <em><strong>Campos do Formulário</strong> campos disponíveis para informações sobre tickets</em>
            </th>
        </tr>
        <tr>
            <th nowrap>Ordem
                <i class="help-tip icon-question-sign" href="#field_sort"></i></th>
            <th nowrap>Rótulo
                <i class="help-tip icon-question-sign" href="#field_label"></i></th>
            <th nowrap>Tipo
                <i class="help-tip icon-question-sign" href="#field_type"></i></th>
            <th nowrap>Interno
                <i class="help-tip icon-question-sign" href="#field_internal"></i></th>
            <th nowrap>Exigido
                <i class="help-tip icon-question-sign" href="#field_required"></i></th>
            <th nowrap>Variável
                <i class="help-tip icon-question-sign" href="#field_variable"></i></th>
            <th nowrap>Excluir
                <i class="help-tip icon-question-sign" href="#field_delete"></i></th>
        </tr>
    </thead>
    <tbody class="sortable-rows" data-sort="sort-">
    <?php if ($form) foreach ($form->getDynamicFields() as $f) {
        $id = $f->get('id');
        $deletable = !$f->isDeletable() ? 'disabled="disabled"' : '';
        $force_name = $f->isNameForced() ? 'disabled="disabled"' : '';
        $force_privacy = $f->isPrivacyForced() ? 'disabled="disabled"' : '';
        $force_required = $f->isRequirementForced() ? 'disabled="disabled"' : '';
        $fi = $f->getImpl();
        $ferrors = $f->errors(); ?>
        <tr>
            <td><i class="icon-sort"></i></td>
            <td><input type="text" size="32" name="label-<?php echo $id; ?>"
                value="<?php echo Format::htmlchars($f->get('label')); ?>"/>
                <font class="error"><?php
                    if ($ferrors['label']) echo '<br/>'; echo $ferrors['label']; ?>
            </td>
            <td nowrap><select name="type-<?php echo $id; ?>" <?php
                if (!$fi->isChangeable()) echo 'disabled="disabled"'; ?>>
                <?php foreach (FormField::allTypes() as $group=>$types) {
                        ?><optgroup label="<?php echo Format::htmlchars($group); ?>"><?php
                        foreach ($types as $type=>$nfo) {
                            if ($f->get('type') != $type
                                    && isset($nfo[2]) && !$nfo[2]) continue; ?>
                <option value="<?php echo $type; ?>" <?php
                    if ($f->get('type') == $type) echo 'selected="selected"'; ?>>
                    <?php echo $nfo[0]; ?></option>
                    <?php } ?>
                </optgroup>
                <?php } ?>
            </select>
            <?php if ($f->isConfigurable()) { ?>
                <a class="action-button" style="float:none;overflow:inherit"
                    href="#ajax.php/form/field-config/<?php
                        echo $f->get('id'); ?>"
                    onclick="javascript:
                        $('#overlay').show();
                        $('#field-config .body').load($(this).attr('href').substr(1));
                        $('#field-config').show();
                        return false;
                    "><i class="icon-edit"></i> Config</a>
            <?php } ?>
            <div class="error" style="white-space:normal"><?php
                if ($ferrors['type']) echo $ferrors['type'];
            ?></div>
            </td>
            <td><input type="checkbox" name="private-<?php echo $id; ?>"
                <?php if ($f->get('private')) echo 'checked="checked"'; ?>
                <?php echo $force_privacy ?>/></td>
            <td><input type="checkbox" name="required-<?php echo $id; ?>"
                <?php if ($f->get('required')) echo 'checked="checked"'; ?>
                <?php echo $force_required ?>/>
            </td>
            <td>
                <input type="text" size="20" name="name-<?php echo $id; ?>"
                    value="<?php echo Format::htmlchars($f->get('name'));
                    ?>" <?php echo $force_name ?>/>
                <font class="error"><?php
                    if ($ferrors['name']) echo '<br/>'; echo $ferrors['name'];
                ?></font>
                </td>
            <td><input class="delete-box" type="checkbox" name="delete-<?php echo $id; ?>"
                    data-field-label="<?php echo $f->get('label'); ?>"
                    data-field-id="<?php echo $id; ?>"
                    <?php echo $deletable; ?>/>
                <input type="hidden" name="sort-<?php echo $id; ?>"
                    value="<?php echo $f->get('sort'); ?>"/>
                </td>
        </tr>
    <?php
    }
    for ($i=0; $i<$newcount; $i++) { ?>
            <td><em>+</em>
                <input type="hidden" name="sort-new-<?php echo $i; ?>"
                    value="<?php echo $info["sort-new-$i"]; ?>"/></td>
            <td><input type="text" size="32" name="label-new-<?php echo $i; ?>"
                value="<?php echo $info["label-new-$i"]; ?>"/></td>
            <td><select name="type-new-<?php echo $i; ?>">
                <?php foreach (FormField::allTypes() as $group=>$types) {
                    ?><optgroup label="<?php echo Format::htmlchars($group); ?>"><?php
                    foreach ($types as $type=>$nfo) {
                        if (isset($nfo[2]) && !$nfo[2]) continue; ?>
                <option value="<?php echo $type; ?>"
                    <?php if ($info["type-new-$i"] == $type) echo 'selected="selected"'; ?>>
                    <?php echo $nfo[0]; ?>
                </option>
                    <?php } ?>
                </optgroup>
                <?php } ?>
            </select></td>
            <td><input type="checkbox" name="private-new-<?php echo $i; ?>"
            <?php if ($info["private-new-$i"]
                || (!$_POST && $form && $form->get('type') == 'U'))
                    echo 'checked="checked"'; ?>/></td>
            <td><input type="checkbox" name="required-new-<?php echo $i; ?>"
                <?php if ($info["required-new-$i"]) echo 'checked="checked"'; ?>/></td>
            <td><input type="text" size="20" name="name-new-<?php echo $i; ?>"
                value="<?php echo $info["name-new-$i"]; ?>"/>
                <font class="error"><?php
                    if ($errors["new-$i"]['name']) echo '<br/>'; echo $errors["new-$i"]['name'];
                ?></font>
            <td></td>
        </tr>
    <?php } ?>
    </tbody>
    <tbody>
        <tr>
            <th colspan="7">
                <em><strong>Notas Interna</strong></em>
            </th>
        </tr>
        <tr>
            <td colspan="7"><textarea class="richtext no-bar" name="notes"
                rows="6" cols="80"><?php
                echo $info['notes']; ?></textarea>
            </td>
        </tr>
    </tbody>
    </table>
<p class="centered">
    <input type="submit" name="submit" value="<?php echo $submit_text; ?>">
    <input type="reset"  name="reset"  value="Redefinir">
    <input type="button" name="cancel" value="Cancelar" onclick='window.location.href="?"'>
</p>

<div style="display:none;" class="draggable dialog" id="delete-confirm">
    <h3><i class="icon-trash"></i> Remover Dados Existentes?</h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p>
        <strong>Você está prestes a excluir <span id="deleted-count"></span> campos.</strong>
        Também gostaria de remover os dados atualmente pertencentes a este campo? 
        <em>Se você optar por não remover os dados agora, terá a opção de apaga-los quando editar</em>
    </p><p style="color:red">
        Dados apagados não podem ser recuperados.
    </p>
    <hr>
    <div id="deleted-fields"></div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons" style="float:left">
            <input type="button" value="Não, Cancelar" class="close">
        </span>
        <span class="buttons" style="float:right">
            <input type="submit" value="Continuar" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>
</form>

<div style="display:none;" class="dialog draggable" id="field-config">
    <div class="body"></div>
</div>

<script type="text/javascript">
$('form.manage-form').on('submit.inline', function(e) {
    var formObj = this, deleted = $('input.delete-box:checked', this);
    if (deleted.length) {
        e.stopImmediatePropagation();
        $('#overlay').show();
        $('#deleted-fields').empty();
        deleted.each(function(i, e) {
            $('#deleted-fields').append($('<p></p>')
                .append($('<input/>').attr({type:'checkbox',name:'delete-data-'
                    + $(e).data('fieldId')})
                ).append($('<strong>').html(
                    'Remova todos os dados inseridos de <u>' + $(e).data('fieldLabel') + '</u>'
                ))
            );
        });
        $('#delete-confirm').show().delegate('input.confirm', 'click.confirm', function() {
            $('.dialog#delete-confirm').hide();
            $(formObj).unbind('submit.inline');
            $(window).unbind('beforeunload');
            $('#loading').show();
        })
        return false;
    }
    // TODO: Popup the 'please wait' dialog
    $(window).unbind('beforeunload');
    $('#loading').show();
});
</script>

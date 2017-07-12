<h3><i class="icon-paste"></i> Gerenciar Formulários</i></h3>
<b><a class="close" href="#"><i class="icon-remove-circle"></i></a></b>
<hr/>
Classifique os formulários deste ticket clicando e arrastando sobre eles. Use a caixa 
abaixo para adicionar novos formulários para o ticket.
<br/>
<br/>
<form method="post" action="<?php echo $info['action']; ?>">
<div id="ticket-entries">
<?php
$current_list = array();
foreach ($forms as $e) { ?>
<div class="sortable-row-item" data-id="<?php echo $e->get('id'); ?>">
    <input type="hidden" name="forms[]" value="<?php echo $e->get('form_id'); ?>" />
    <i class="icon-reorder"></i> <?php echo $e->getForm()->getTitle();
    $current_list[] = $e->get('form_id');
    if ($e->getForm()->get('type') == 'G') { ?>
    <div class="delete"><a href="#"><i class="icon-trash"></i></a></div>
    <?php } ?>
</div>
<?php } ?>
</div>
<hr/>
<i class="icon-plus"></i>&nbsp;
<select name="new-form" onchange="javascript:a
    var $sel = $(this).find('option:selected');
    $('#ticket-entries').append($('<div></div>').addClass('sortable-row-item')
        .text(' '+$sel.text())
        .data('id', $sel.val())
        .prepend($('<i>').addClass('icon-reorder'))
        .append($('<input/>').attr({name:'forms[]', type:'hidden'}).val($sel.val()))
        .append($('<div></div>').addClass('delete')
            .append($('<a href=\'#\'>').append($('<i>').addClass('icon-trash')))
        )
    );
    $sel.prop('disabled',true);">
<option selected="selected" disabled="disabled">Adicionar um novo formulário para este ticket</option>
<?php foreach (DynamicForm::objects()->filter(array(
    'type'=>'G')) as $f
) {
    if (in_array($f->get('id'), $current_list))
        continue;
    ?><option value="<?php echo $f->get('id'); ?>"><?php
    echo $f->getTitle(); ?></option><?php
} ?>
</select>
<div id="delete-warning" style="display:none">
<hr>
    <div id="msg_warning">
    Clicando em <strong>Salvar</strong>irá apagar permanentemente os dados associados com as formulários excluídos
    </div>
</div>
    <hr>
    <p class="full-width">
        <span class="buttons" style="float:left">
            <input type="reset" value="Redefinir">
            <input type="button" name="cancel" class="<?php echo $user ? 'cancel' : 'close' ?>"  value="Cancelar">
        </span>
        <span class="buttons" style="float:right">
            <input type="submit" value="Salvar">
        </span>
     </p>

<script type="text/javascript">
$(function() {
    $('#ticket-entries').sortable({containment:'parent',tolerance:'pointer'});
    $('#ticket-entries .delete a').live('click', function() {
        var $div = $(this).closest('.sortable-row-item');
        $('select[name=new-form]').find('option[data-id='+$div.data('id')+']')
            .prop('disabled',false);
        $div.remove();
        $('#delete-warning').show();
        return false;
    })
});
</script>

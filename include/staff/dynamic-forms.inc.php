<div style="width:700;padding-top:5px; float:left;">
 <h2>Formulários Personalizados</h2>
</div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
 <b><a href="forms.php?a=add" class="Icon form-add">Adicionar Formulário</a></b></div>
<div class="clear"></div>

<?php
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = DynamicForm::objects()->filter(array('type__in'=>array('G')))->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('forms.php');
$showing=$pageNav->showing().' formulários';
?>

<form action="forms.php" method="POST" name="forms">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th>Fomulários Embutidos</th>
            <th>Última Atualização</th>
        </tr>
    </thead>
    <tbody>
    <?php
    $forms = array(
        'U' => 'icon-user',
        'T' => 'icon-ticket',
        'C' => 'icon-building',
        'O' => 'icon-group',
    );
    foreach (DynamicForm::objects()
            ->filter(array('type__in'=>array_keys($forms)))
            ->order_by('type', 'title') as $form) { ?>
        <tr>
        <td><i class="<?php echo $forms[$form->get('type')]; ?>"></i></td>
            <td><a href="?id=<?php echo $form->get('id'); ?>">
                <?php echo $form->get('title'); ?></a>
            <td><?php echo $form->get('updated'); ?></td>
        </tr>
    <?php } ?>
    </tbody>
    <tbody>
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th>Formulário Personalizado</th>
            <th>Última Atualização</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach (DynamicForm::objects()->filter(array('type'=>'G'))
                ->order_by('title')
                ->limit($pageNav->getLimit())
                ->offset($pageNav->getStart()) as $form) {
            $sel=false;
            if($ids && in_array($form->get('id'),$ids))
                $sel=true; ?>
        <tr>
            <td><?php if ($form->isDeletable()) { ?>
                <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $form->get('id'); ?>"
                    <?php echo $sel?'checked="checked"':''; ?>>
            <?php } ?></td>
            <td><a href="?id=<?php echo $form->get('id'); ?>"><?php echo $form->get('title'); ?></a></td>
            <td><?php echo $form->get('updated'); ?></td>
        </tr>
    <?php }
    ?>
    </tbody>
    <tfoot>
     <tr>
        <td colspan="3">
            <?php if($count){ ?>
            Selecionar:&nbsp;
            <a id="selectAll" href="#ckb">Todos</a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb">Nenhum</a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb">Alternar</a>&nbsp;&nbsp;
            <?php }else{
                echo 'Não há formulários definido ainda &mdash; <a href="forms.php?a=add">adicionar um!</a>';
            } ?>
        </td>
     </tr>
    </tfoot>
</table>
<?php
if ($count) //Show options..
    echo '<div>&nbsp;Página:'.$pageNav->getPageLinks().'&nbsp;</div>';
?>
<p class="centered" id="actions">
    <input class="button" type="submit" name="delete" value="Excluir">
</p>
</form>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3>Por favor Confirme</h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong>Tem certeza que deseja EXCLUIR formulários selecionados?</strong></font>
        <br><br>Formulários excluídos não podem ser recuperados.
    </p>
    <div>Por favor confirme para continuar.</div>
    <hr style="margin-top:1em"/>
    <p class="full-width">
        <span class="buttons" style="float:left">
            <input type="button" value="Não, Cancelar" class="close">
        </span>
        <span class="buttons" style="float:right">
            <input type="button" value="Sim, Confirmar!" class="confirm">
        </span>
     </p>
    <div class="clear"></div>
</div>

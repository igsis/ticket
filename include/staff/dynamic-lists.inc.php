<div style="width:700;padding-top:5px; float:left;">
 <h2>Listas Personalizadas</h2>
</div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
 <b><a href="lists.php?a=add" class="Icon list-add">Adicionar Lista</a></b></div>
<div class="clear"></div>

<?php
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = DynamicList::objects()->count();
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('lists.php');
$showing=$pageNav->showing().' listas personalizadas';
?>

<form action="lists.php" method="POST" name="lists">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th>Nome da Lista</th>
            <th>Criado</th>
            <th>Última Atualização</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach (DynamicList::objects()->order_by('name')
                ->limit($pageNav->getLimit())
                ->offset($pageNav->getStart()) as $list) {
            $sel = false;
            if ($ids && in_array($form->get('id'),$ids))
                $sel = true; ?>
        <tr>
            <td>
                <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $list->get('id'); ?>"
                    <?php echo $sel?'checked="checked"':''; ?>></td>
            <td><a href="?id=<?php echo $list->get('id'); ?>"><?php echo $list->get('name'); ?></a></td>
            <td><?php echo $list->get('created'); ?></td>
            <td><?php echo $list->get('updated'); ?></td>
        </tr>
    <?php }
    ?>
    </tbody>
    <tfoot>
     <tr>
        <td colspan="4">
            <?php if($count){ ?>
            Selecionar:&nbsp;
            <a id="selectAll" href="#ckb">Todos</a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb">Nenhum</a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb">Alternar</a>&nbsp;&nbsp;
            <?php } else {
                echo 'Não há listas personalizadas definidas ainda — adicionar uma!';
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
        <font color="red"><strong>Tem certeza que deseja EXCLUIR listas selecionas?</strong></font>
        <br><br>Listas excluídas não podem ser recuperadas.
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

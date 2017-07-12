<div style="width:700;padding-top:5px; float:left;">
 <h2>Plugins Instalados Atualmente</h2>
</div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
 <b><a href="plugins.php?a=add" class="Icon form-add">Adicionar Plugin</a></b></div>
<div class="clear"></div>

<?php
$page = ($_GET['p'] && is_numeric($_GET['p'])) ? $_GET['p'] : 1;
$count = count($ost->plugins->allInstalled());
$pageNav = new Pagenate($count, $page, PAGE_LIMIT);
$pageNav->setURL('forms.php');
$showing=$pageNav->showing().' forms';
?>

<form action="plugins.php" method="POST" name="forms">
<?php csrf_token(); ?>
<input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
<table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th>Nome do Plugin</th>
            <th>Status</td>
            <th>Data de Instalação</th>
        </tr>
    </thead>
    <tbody>
<?php
foreach ($ost->plugins->allInstalled() as $p) {
    if ($p instanceof Plugin) { ?>
    <tr>
        <td><input type="checkbox" class="ckb" name="ids[]" value="<?php echo $p->getId(); ?>"
                <?php echo $sel?'checked="checked"':''; ?>></td>
        <td><a href="plugins.php?id=<?php echo $p->getId(); ?>"
            ><?php echo $p->getName(); ?></a></td>
        <td><?php echo ($p->isActive())
            ? 'Ativado' : '<strong>Desativado</strong>'; ?></td>
        <td><?php echo Format::db_datetime($p->getInstallDate()); ?></td>
    </tr>
    <?php } else {} ?>
<?php } ?>
    </tbody>
    <tfoot>
     <tr>
        <td colspan="4">
            <?php if($count){ ?>
            Select:&nbsp;
            <a id="selectAll" href="#ckb">Todos</a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb">Nenhum</a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb">Alternar</a>&nbsp;&nbsp;
            <?php }else{
                echo 'Não há plugin instalado ainda &mdash; <a href="?a=add">adicione um</a>!';
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
    <input class="button" type="submit" name="enable" value="Ativar">
    <input class="button" type="submit" name="disable" value="Desativar">
</p>
</form>

<div style="display:none;" class="dialog" id="confirm-action">
    <h3>Por favor Confirme</h3>
    <a class="close" href="">&times;</a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong>Tem certeza que deseja EXCLUIR plugins selecionados?</strong></font>
        <br><br>Plugins excluídos não podem ser recuperados.
    </p>
    <p class="confirm-action" style="display:none;" id="enable-confirm">
        <font color="green"><strong>Você está pronto para ativar plugins selecionados?</strong></font>
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        <font color="red"><strong>Tem certeza de que deseja desabilitar plugins selecionados?</strong></font>
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

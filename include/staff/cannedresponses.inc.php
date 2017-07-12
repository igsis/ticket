<?php
if(!defined('OSTSCPINC') || !$thisstaff) die('Access Denied');

$qstr='';
$sql='SELECT canned.*, count(attach.file_id) as files, dept.dept_name as department '.
     ' FROM '.CANNED_TABLE.' canned '.
     ' LEFT JOIN '.DEPT_TABLE.' dept ON (dept.dept_id=canned.dept_id) '.
     ' LEFT JOIN '.ATTACHMENT_TABLE.' attach
            ON (attach.object_id=canned.canned_id AND attach.`type`=\'C\' AND NOT attach.inline)';
$sql.=' WHERE 1';

$sortOptions=array('title'=>'canned.title','status'=>'canned.isenabled','dept'=>'department','updated'=>'canned.updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'title';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}

$order_column=$order_column?$order_column:'canned.title';

if($_REQUEST['order'] && $orderWays[strtoupper($_REQUEST['order'])]) {
    $order=$orderWays[strtoupper($_REQUEST['order'])];
}

$order=$order?$order:'ASC';

if($order_column && strpos($order_column,',')){
    $order_column=str_replace(','," $order,",$order_column);
}

$x=$sort.'_sort';
$$x=' class="'.strtolower($order).'" ';
$order_by="$order_column $order ";

$total=db_count('SELECT count(*) FROM '.CANNED_TABLE.' canned ');
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$pageNav->setURL('canned.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
//Ok..lets roll...create the actual query
$qstr.='&order='.($order=='DESC'?'ASC':'DESC');
$query="$sql GROUP BY canned.canned_id ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
$res=db_query($query);
if($res && ($num=db_num_rows($res)))
    $showing=$pageNav->showing().' resposta pronta';
else
    $showing='Nenhuma resposta encontrada!';

?>
<div style="width:700px;padding-top:5px; float:left;">
 <h2>Respostas Prontas&nbsp;<i class="help-tip icon-question-sign" href="#canned_responses"></i></h2>
 </div>
<div style="float:right;text-align:right;padding-top:5px;padding-right:5px;">
    <b><a href="canned.php?a=add" class="Icon newReply">Adicionar Resposta</a></b></div>
<div class="clear"></div>
<form action="canned.php" method="POST" name="canned">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
 <input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7">&nbsp;</th>
            <th width="500"><a <?php echo $title_sort; ?> href="canned.php?<?php echo $qstr; ?>&sort=title">Título</a></th>
            <th width="80"><a  <?php echo $status_sort; ?> href="canned.php?<?php echo $qstr; ?>&sort=status">Status</a></th>
            <th width="200"><a  <?php echo $dept_sort; ?> href="canned.php?<?php echo $qstr; ?>&sort=dept">Departamento</a></th>
            <th width="150" nowrap><a  <?php echo $updated_sort; ?>href="canned.php?<?php echo $qstr; ?>&sort=updated">Última Atualização</a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        $total=0;
        $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
        if($res && db_num_rows($res)):
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['canned_id'],$ids))
                    $sel=true;
                $files=$row['files']?'<span class="Icon file">&nbsp;</span>':'';
                ?>
            <tr id="<?php echo $row['canned_id']; ?>">
                <td width=7px>
                  <input type="checkbox" name="ids[]" value="<?php echo $row['canned_id']; ?>" class="ckb"
                            <?php echo $sel?'checked="checked"':''; ?> />
                </td>	
                <td>
                    <a href="canned.php?id=<?php echo $row['canned_id']; ?>"><?php echo Format::truncate($row['title'],200); echo "&nbsp;$files"; ?></a>&nbsp;
                </td>
                <td><?php echo $row['isenabled']?'Ativado':'<b>Desativado</b>'; ?></td>
                <td><?php echo $row['department']?$row['department']:'&mdash; Todos Departamentos &mdash;'; ?></td>
                <td>&nbsp;<?php echo Format::db_datetime($row['updated']); ?></td>
            </tr>
            <?php
            } //end of while.
        endif; ?>
    <tfoot>
     <tr>
        <td colspan="5">
            <?php if($res && $num){ ?>
            Selecionar:&nbsp;
            <a id="selectAll" href="#ckb">Todos</a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb">Nenhum</a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb">Alternar</a>&nbsp;&nbsp;
            <?php }else{
                echo 'Não há respostas prontas';
            } ?>
        </td>
     </tr>
    </tfoot>
</table>
<?php
if($res && $num): //Show options..
    echo '<div>&nbsp;Página:'.$pageNav->getPageLinks().'&nbsp;</div>';
?>
<p class="centered" id="actions">
    <input class="button" type="submit" name="enable" value="Ativar" >
    <input class="button" type="submit" name="disable" value="Desativar" >
    <input class="button" type="submit" name="delete" value="Excluir" >
</p>
<?php
endif;
?>
</form>
<div style="display:none;" class="dialog" id="confirm-action">
    <h3>Por favor Confirme</h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="enable-confirm">
        Tem certeza que deseja <b>ativar</b> respostas selecionadas?
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        Tem certeza que deseja <b>desativar</b> respostas selecionadas?
    </p>
    <p class="confirm-action" style="display:none;" id="mark_overdue-confirm">
        Tem certeza que deseja sinalizar os tickets selecionados como <font color="red"><b>atrasados</b></font>?
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong>Tem certeza que deseja EXCLUIR as respostas selecionadas?</strong></font>
        <br><br>Itens excluídos não podem ser recuperados, incluindo quaisquer anexos associados.
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

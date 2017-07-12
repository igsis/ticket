<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$filter) die('Access Denied');

$qstr='';
$select='SELECT rule.* ';
$from='FROM '.FILTER_RULE_TABLE.' rule ';
$where='WHERE rule.filter_id='.db_input($filter->getId());
$search=false;
if($_REQUEST['q'] && strlen($_REQUEST['q'])>3) {
    $search=true;
    if(strpos($_REQUEST['q'],'@') && Validator::is_email($_REQUEST['q']))
        $where.=' AND rule.val='.db_input($_REQUEST['q']);
    else
        $where.=' AND rule.val LIKE "%'.db_input($_REQUEST['q'],false).'%"';

}elseif($_REQUEST['q']) {
    $errors['q']='Term too short!';
}

$sortOptions=array('email'=>'rule.val','status'=>'isactive','created'=>'rule.created','created'=>'rule.updated');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'email';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'rule.val';

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

$total=db_count('SELECT count(DISTINCT rule.id) '.$from.' '.$where);
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total, $page, PAGE_LIMIT);
$pageNav->setURL('banlist.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
$qstr.='&order='.($order=='DESC'?'ASC':'DESC');
$query="$select $from $where ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;
?>
<h2>Endereços de E-mail Banido
    <i class="help-tip icon-question-sign" href="#ban_list"></i>
    </h2>
<div style="width:600; float:left;padding-top:5px;">
    <form action="banlist.php" method="GET" name="filter">
     <input type="hidden" name="a" value="filter" >
     <div>
       Consulta: <input name="q" type="text" size="20" value="<?php echo Format::htmlchars($_REQUEST['q']); ?>">
        &nbsp;&nbsp;
        <input type="submit" name="submit" value="Pesquisar"/>
     </div>
    </form>
 </div>
<div style="float:right;text-align:right;padding-right:5px;"><b><a href="banlist.php?a=add" class="Icon newstaff">Banir E-mail</a></b></div>
<div class="clear"></div>
<?php
if(($res=db_query($query)) && ($num=db_num_rows($res)))
    $showing=$pageNav->showing();
else
    $showing='Nenhum e-mail banido encontrado na consulta	!';

if($search)
    $showing='Resultado da Pesquisa: '.$showing;
    
?>
<form action="banlist.php" method="POST" name="banlist">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
<input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7px">&nbsp;</th>        
            <th width="350"><a <?php echo $email_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=email">Endereço de E-mail</a></th>
            <th width="200"><a  <?php echo $status_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=status">Status</a></th>
            <th width="120"><a <?php echo $created_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=created">Data Adicionada</a></th>
            <th width="120"><a <?php echo $updated_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=updated">Última Atualização</a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        if($res && db_num_rows($res)):
            $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['id'],$ids))
                    $sel=true;
                ?>
               <tr id="<?php echo $row['id']; ?>">
                <td width=7px>
                  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $row['id']; ?>" <?php echo $sel?'checked="checked"':''; ?>>
                </td>
                <td>&nbsp;<a href="banlist.php?id=<?php echo $row['id']; ?>"><?php echo Format::htmlchars($row['val']); ?></a></td>
                <td>&nbsp;&nbsp;<?php echo $row['isactive']?'Ativado':'<b>Desativado</b>'; ?></td>
                <td><?php echo Format::db_date($row['created']); ?></td>
                <td><?php echo Format::db_datetime($row['updated']); ?>&nbsp;</td>
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
                echo 'Nenhum e-mail banido encontrado!';
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
    &nbsp;&nbsp;
    <input class="button" type="submit" name="disable" value="Desativar" >
    &nbsp;&nbsp;
    <input class="button" type="submit" name="delete" value="Excluir">
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
        Tem certeza que deseja <b>ativar</b> e-mails selecionados a lista de banidos?
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        Tem certeza que deseja <b>desativar</b> e-mails selecionados da lista de banidos?
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong>Tem certeza que deseja EXCLUIR e-mails selecionados da lista de banidos?</strong></font>
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


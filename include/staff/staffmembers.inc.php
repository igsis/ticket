<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin()) die('Access Denied');
$qstr='';
$select='SELECT staff.*,CONCAT_WS(" ",firstname,lastname) as name, grp.group_name, dept.dept_name as dept,count(m.team_id) as teams ';
$from='FROM '.STAFF_TABLE.' staff '.
      'LEFT JOIN '.GROUP_TABLE.' grp ON(staff.group_id=grp.group_id) '.
      'LEFT JOIN '.DEPT_TABLE.' dept ON(staff.dept_id=dept.dept_id) '.
      'LEFT JOIN '.TEAM_MEMBER_TABLE.' m ON(m.staff_id=staff.staff_id) ';
$where='WHERE 1 ';

if($_REQUEST['did'] && is_numeric($_REQUEST['did'])) {
    $where.=' AND staff.dept_id='.db_input($_REQUEST['did']);
    $qstr.='&did='.urlencode($_REQUEST['did']);
}

if($_REQUEST['gid'] && is_numeric($_REQUEST['gid'])) {
    $where.=' AND staff.group_id='.db_input($_REQUEST['gid']);
    $qstr.='&gid='.urlencode($_REQUEST['gid']);
}

if($_REQUEST['tid'] && is_numeric($_REQUEST['tid'])) {
    $where.=' AND m.team_id='.db_input($_REQUEST['tid']);
    $qstr.='&tid='.urlencode($_REQUEST['tid']);
}

$sortOptions=array('name'=>'staff.firstname,staff.lastname','username'=>'staff.username','status'=>'isactive',
                   'group'=>'grp.group_name','dept'=>'dept.dept_name','created'=>'staff.created','login'=>'staff.lastlogin');
$orderWays=array('DESC'=>'DESC','ASC'=>'ASC');
$sort=($_REQUEST['sort'] && $sortOptions[strtolower($_REQUEST['sort'])])?strtolower($_REQUEST['sort']):'name';
//Sorting options...
if($sort && $sortOptions[$sort]) {
    $order_column =$sortOptions[$sort];
}
$order_column=$order_column?$order_column:'staff.firstname,staff.lastname';

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

$total=db_count('SELECT count(DISTINCT staff.staff_id) '.$from.' '.$where);
$page=($_GET['p'] && is_numeric($_GET['p']))?$_GET['p']:1;
$pageNav=new Pagenate($total,$page,PAGE_LIMIT);
$pageNav->setURL('staff.php',$qstr.'&sort='.urlencode($_REQUEST['sort']).'&order='.urlencode($_REQUEST['order']));
//Ok..lets roll...create the actual query
$qstr.='&order='.($order=='DESC'?'ASC':'DESC');
$query="$select $from $where GROUP BY staff.staff_id ORDER BY $order_by LIMIT ".$pageNav->getStart().",".$pageNav->getLimit();
//echo $query;
?>
<h2>Funcionários</h2>
<div style="width:700px; float:left;">
    <form action="staff.php" method="GET" name="filter">
     <input type="hidden" name="a" value="filter" >
        <select name="did" id="did">
             <option value="0">&mdash; Todos Departaments &mdash;</option>
             <?php
             $sql='SELECT dept.dept_id, dept.dept_name,count(staff.staff_id) as users  '.
                  'FROM '.DEPT_TABLE.' dept '.
                  'INNER JOIN '.STAFF_TABLE.' staff ON(staff.dept_id=dept.dept_id) '.
                  'GROUP By dept.dept_id HAVING users>0 ORDER BY dept_name';
             if(($res=db_query($sql)) && db_num_rows($res)){
                 while(list($id,$name, $users)=db_fetch_row($res)){
                     $sel=($_REQUEST['did'] && $_REQUEST['did']==$id)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>%s (%s)</option>',$id,$sel,$name,$users);
                 }
             }
             ?>
        </select>
        <select name="gid" id="gid">
            <option value="0">&mdash; Todos Grupos &mdash;</option>
             <?php
             $sql='SELECT grp.group_id, group_name,count(staff.staff_id) as users '.
                  'FROM '.GROUP_TABLE.' grp '.
                  'INNER JOIN '.STAFF_TABLE.' staff ON(staff.group_id=grp.group_id) '.
                  'GROUP BY grp.group_id ORDER BY group_name';
             if(($res=db_query($sql)) && db_num_rows($res)){
                 while(list($id,$name,$users)=db_fetch_row($res)){
                     $sel=($_REQUEST['gid'] && $_REQUEST['gid']==$id)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>%s (%s)</option>',$id,$sel,$name,$users);
                 }
             }
             ?>
        </select>
        <select name="tid" id="tid">
            <option value="0">&mdash; Todos Times &mdash;</option>
             <?php
             $sql='SELECT team.team_id, team.name, count(member.staff_id) as users FROM '.TEAM_TABLE.' team '.
                  'INNER JOIN '.TEAM_MEMBER_TABLE.' member ON(member.team_id=team.team_id) '.
                  'GROUP BY team.team_id ORDER BY team.name';
             if(($res=db_query($sql)) && db_num_rows($res)){
                 while(list($id,$name,$users)=db_fetch_row($res)){
                     $sel=($_REQUEST['tid'] && $_REQUEST['tid']==$id)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>%s (%s)</option>',$id,$sel,$name,$users);
                 }
             }
             ?>
        </select>
        &nbsp;&nbsp;
        <input type="submit" name="submit" value="Aplicar"/>
    </form>
 </div>
<div style="float:right;text-align:right;padding-right:5px;"><b><a href="staff.php?a=add" class="Icon newstaff">Adicionar Funcionário</a></b></div>
<div class="clear"></div>
<?php
$res=db_query($query);
if($res && ($num=db_num_rows($res)))        
    $showing=$pageNav->showing();
else
    $showing='Funcionários não encontrado!';
?>
<form action="staff.php" method="POST" name="staff" >
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="mass_process" >
 <input type="hidden" id="action" name="a" value="" >
 <table class="list" border="0" cellspacing="1" cellpadding="0" width="940">
    <caption><?php echo $showing; ?></caption>
    <thead>
        <tr>
            <th width="7px">&nbsp;</th>        
            <th width="200"><a <?php echo $name_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=name">Nome</a></th>
            <th width="100"><a <?php echo $username_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=username">Usuário</a></th>
            <th width="100"><a  <?php echo $status_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=status">Status</a></th>
            <th width="120"><a  <?php echo $group_sort; ?>href="staff.php?<?php echo $qstr; ?>&sort=group">Grupo</a></th>
            <th width="150"><a  <?php echo $dept_sort; ?>href="staff.php?<?php echo $qstr; ?>&sort=dept">Departamento</a></th>
            <th width="100"><a <?php echo $created_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=created">Criado</a></th>
            <th width="145"><a <?php echo $login_sort; ?> href="staff.php?<?php echo $qstr; ?>&sort=login">Último Login</a></th>
        </tr>
    </thead>
    <tbody>
    <?php
        if($res && db_num_rows($res)):
            $ids=($errors && is_array($_POST['ids']))?$_POST['ids']:null;
            while ($row = db_fetch_array($res)) {
                $sel=false;
                if($ids && in_array($row['staff_id'],$ids))
                    $sel=true;
                ?>
               <tr id="<?php echo $row['staff_id']; ?>">
                <td width=7px>
                  <input type="checkbox" class="ckb" name="ids[]" value="<?php echo $row['staff_id']; ?>" <?php echo $sel?'checked="checked"':''; ?> >
                <td><a href="staff.php?id=<?php echo $row['staff_id']; ?>"><?php echo Format::htmlchars($row['name']); ?></a>&nbsp;</td>
                <td><?php echo $row['username']; ?></td>
                <td><?php echo $row['isactive']?'Ativado':'<b>Bloqueado</b>'; ?>&nbsp;<?php echo $row['onvacation']?'<small>(<i>vacation</i>)</small>':''; ?></td>
                <td><a href="groups.php?id=<?php echo $row['group_id']; ?>"><?php echo Format::htmlchars($row['group_name']); ?></a></td>
                <td><a href="departments.php?id=<?php echo $row['dept_id']; ?>"><?php echo Format::htmlchars($row['dept']); ?></a></td>
                <td><?php echo Format::db_date($row['created']); ?></td>
                <td><?php echo Format::db_datetime($row['lastlogin']); ?>&nbsp;</td>
               </tr>
            <?php
            } //end of while.
        endif; ?>
    <tfoot>
     <tr>
        <td colspan="8">
            <?php if($res && $num){ ?>
            Selecionar:&nbsp;
            <a id="selectAll" href="#ckb">Todos</a>&nbsp;&nbsp;
            <a id="selectNone" href="#ckb">Nenhum</a>&nbsp;&nbsp;
            <a id="selectToggle" href="#ckb">Alternar</a>&nbsp;&nbsp;
            <?php }else{
                echo 'Nenhum membro encontrado!';
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
    <input class="button" type="submit" name="enable" value="Permitir" >
    &nbsp;&nbsp;
    <input class="button" type="submit" name="disable" value="Bloquear" >
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
        Tem certeza que deseja <b>permitir</b> (desbloquear) funcionário selecionado?
    </p>
    <p class="confirm-action" style="display:none;" id="disable-confirm">
        Tem certeza que deseja <b>desativar</b> (bloquear) funcionário selecionado?
        <br><br>Funcionário bloqueado não será capaz de acessar Painel de Controle Pessoal.
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong>Tem certeza que deseja EXCLUIR funcionário selecionado?</strong></font>
        <br><br>Funcionário excluído não pode ser recuperado.
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


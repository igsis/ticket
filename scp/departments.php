<?php
/*********************************************************************
    departments.php

    Departments

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('admin.inc.php');
$dept=null;
if($_REQUEST['id'] && !($dept=Dept::lookup($_REQUEST['id'])))
    $errors['err']='ID de Departamento desconhecido ou inválido.';
if($_POST){
    switch(strtolower($_POST['do'])){
        case 'update':
            if(!$dept){
                $errors['err']='Departamento desconhecido ou inválido.';
            }elseif($dept->update($_POST,$errors)){
                $msg='Departamento atualizado com sucesso';
            }elseif(!$errors['err']){
                $errors['err']='Erro ao atualizar departamento. Tente novamente!';
            }
            break;
        case 'create':
            if(($id=Dept::create($_POST,$errors))){
                $msg=Format::htmlchars($_POST['name']).'Adicionado com sucesso';
                $_REQUEST['a']=null;
            }elseif(!$errors['err']){
                $errors['err']='Não é possível adicionar departamento. Corrija erro(s) abaixo e tente novamente.';
            }
            break;
        case 'mass_process':
            if(!$_POST['ids'] || !is_array($_POST['ids']) || !count($_POST['ids'])) {
                $errors['err'] = 'Você deve selecionar pelo menos um departamento';
            }elseif(in_array($cfg->getDefaultDeptId(),$_POST['ids'])) {
                $errors['err'] = 'Você não pode desativar/excluir um departamento padrão. Remova Departamento padrão e tente novamente';//You can not disable/delete a default department. Remove default Dept. and try again.
            }else{
                $count=count($_POST['ids']);
                switch(strtolower($_POST['a'])) {
                    case 'make_public':
                        $sql='UPDATE '.DEPT_TABLE.' SET ispublic=1 '
                            .' WHERE dept_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        if(db_query($sql) && ($num=db_affected_rows())){
                            if($num==$count)
                                $msg='Departamentos selecionados tornado público';
                            else
                                $warn="$num of $count departamentos selecionados tornado público";
                        } else {
                            $errors['err']='Não é possível tornar publico departamento selecionado.';
                        }
                        break;
                    case 'make_private':
                        $sql='UPDATE '.DEPT_TABLE.' SET ispublic=0  '
                            .' WHERE dept_id IN ('.implode(',', db_input($_POST['ids'])).') '
                            .' AND dept_id!='.db_input($cfg->getDefaultDeptId());
                        if(db_query($sql) && ($num=db_affected_rows())) {
                            if($num==$count)
                                $msg = 'Departamentos selecionados tornado privado';
                            else
                                $warn = "$num of $count departamentos selecionados tornado privado";
                        } else {
                            $errors['err'] = 'Não é possível tornar departamento selecionado(s) privado. Possivelmente já é privado!';
                        }
                        break;
                    case 'delete':
                        //Deny all deletes if one of the selections has members in it.
                        $sql='SELECT count(staff_id) FROM '.STAFF_TABLE
                            .' WHERE dept_id IN ('.implode(',', db_input($_POST['ids'])).')';
                        list($members)=db_fetch_row(db_query($sql));
                        if($members)
                            $errors['err']='Departamentos com equipe não podem ser excluídos. Mova Equipe primeiro.';
                        else {
                            $i=0;
                            foreach($_POST['ids'] as $k=>$v) {
                                if($v!=$cfg->getDefaultDeptId() && ($d=Dept::lookup($v)) && $d->delete())
                                    $i++;
                            }
                            if($i && $i==$count)
                                $msg = 'Departamentos selecionados excluído com sucesso';
                            elseif($i>0)
                                $warn = "$i of $count departamentos selecionados excluídos";
                            elseif(!$errors['err'])
                                $errors['err'] = 'Não é possível excluir os departamentos selecionados.';
                        }
                        break;
                    default:
                        $errors['err']='Ação Desconhecida - Obter ajuda técnica';
                }
            }
            break;
        default:
            $errors['err']='Ação/comando Desconhecido';
            break;
    }
}

$page='departments.inc.php';
$tip_namespace = 'staff.department';
if($dept || ($_REQUEST['a'] && !strcasecmp($_REQUEST['a'],'add'))) {
    $page='department.inc.php';
}

$nav->setTabActive('staff');
$ost->addExtraHeader('<meta name="tip-namespace" content="' . $tip_namespace . '" />',
    "$('#content').data('tipNamespace', '".$tip_namespace."');");
require(STAFFINC_DIR.'header.inc.php');
require(STAFFINC_DIR.$page);
include(STAFFINC_DIR.'footer.inc.php');
?>

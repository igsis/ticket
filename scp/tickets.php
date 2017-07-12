<?php
/*************************************************************************
    tickets.php

    Handles all tickets related actions.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

require('staff.inc.php');
require_once(INCLUDE_DIR.'class.ticket.php');
require_once(INCLUDE_DIR.'class.dept.php');
require_once(INCLUDE_DIR.'class.filter.php');
require_once(INCLUDE_DIR.'class.canned.php');
require_once(INCLUDE_DIR.'class.json.php');
require_once(INCLUDE_DIR.'class.dynamic_forms.php');


$page='';
$ticket = $user = null; //clean start.
//LOCKDOWN...See if the id provided is actually valid and if the user has access.
if($_REQUEST['id']) {
    if(!($ticket=Ticket::lookup($_REQUEST['id'])))
         $errors['err']='Unknown or invalid ticket ID';
    elseif(!$ticket->checkStaffAccess($thisstaff)) {
        $errors['err']='Access denied. Contact admin if you believe this is in error';
        $ticket=null; //Clear ticket obj.
    }
}

//Lookup user if id is available.
if ($_REQUEST['uid'])
    $user = User::lookup($_REQUEST['uid']);

//At this stage we know the access status. we can process the post.
if($_POST && !$errors):

    if($ticket && $ticket->getId()) {
        //More coffee please.
        $errors=array();
        $lock=$ticket->getLock(); //Ticket lock if any
        $statusKeys=array('open'=>'Aberto','Reopen'=>'Reaberto','Close'=>'Fechado');
        switch(strtolower($_POST['a'])):
        case 'reply':
            if(!$thisstaff->canPostReply())
                $errors['err'] = 'Ação negada. Contate o administrador';
            else {

                if(!$_POST['response'])
                    $errors['response']='Response required';
                //Use locks to avoid double replies
                if($lock && $lock->getStaffId()!=$thisstaff->getId())
                    $errors['err']='Ação negada. Ticket está bloqueado por outra pessoa!';

                //Make sure the email is not banned
                if(!$errors['err'] && TicketFilter::isBanned($ticket->getEmail()))
                    $errors['err']='Email is in banlist. Must be removed to reply.';
            }

            $wasOpen =($ticket->isOpen());

            //If no error...do the do.
            $vars = $_POST;
            if(!$errors && $_FILES['attachments'])
                $vars['files'] = AttachmentFile::format($_FILES['attachments']);

            if(!$errors && ($response=$ticket->postReply($vars, $errors, $_POST['emailreply']))) {
                $msg='Reply posted successfully';
                $ticket->reload();

                if($ticket->isClosed() && $wasOpen)
                    $ticket=null;
                else
                    // Still open -- cleanup response draft for this user
                    Draft::deleteForNamespace(
                        'ticket.response.' . $ticket->getId(),
                        $thisstaff->getId());

            } elseif(!$errors['err']) {
                $errors['err']='Unable to post the reply. Correct the errors below and try again!';
            }
            break;
        case 'transfer': /** Transfer ticket **/
            //Check permission
            if(!$thisstaff->canTransferTickets())
                $errors['err']=$errors['transfer'] = 'Acesso Negado. Você não tem permissão para transferir tickets.';
            else {

                //Check target dept.
                if(!$_POST['deptId'])
                    $errors['deptId'] = 'Selecionar departamento';
                elseif($_POST['deptId']==$ticket->getDeptId())
                    $errors['deptId'] = 'Ticket já está no departamento';
                elseif(!($dept=Dept::lookup($_POST['deptId'])))
                    $errors['deptId'] = 'Departamento Desconhecido ou Inválido';

                //Transfer message - required.
                if(!$_POST['transfer_comments'])
                    $errors['transfer_comments'] = 'Necessário adicionar comentário para transferência';
                elseif(strlen($_POST['transfer_comments'])<5)
                    $errors['transfer_comments'] = 'Comentário de transferência muito curto!';

                //If no errors - them attempt the transfer.
                if(!$errors && $ticket->transfer($_POST['deptId'], $_POST['transfer_comments'])) {
                    $msg = 'Ticket transferido com sucesso para '.$ticket->getDeptName();
                    //Check to make sure the staff still has access to the ticket
                    if(!$ticket->checkStaffAccess($thisstaff))
                        $ticket=null;

                } elseif(!$errors['transfer']) {
                    $errors['err'] = 'Não é possível concluir a transferência do ticket';
                    $errors['transfer']='Corrija o(s) erro(s) abaixo e tente novamente!';
                }
            }
            break;
        case 'assign':

             if(!$thisstaff->canAssignTickets())
                 $errors['err']=$errors['assign'] = 'Ação negado. Você não tem permissão para atribuir / reatribuir bilhetes';
             else {

                 $id = preg_replace("/[^0-9]/", "",$_POST['assignId']);
                 $claim = (is_numeric($_POST['assignId']) && $_POST['assignId']==$thisstaff->getId());

                 if(!$_POST['assignId'] || !$id)
                     $errors['assignId'] = 'Selecionar administrador';
                 elseif($_POST['assignId'][0]!='s' && $_POST['assignId'][0]!='t' && !$claim)
                     $errors['assignId']='ID administrador inválido - Entre em contato com o suporte técnico';
                 elseif($ticket->isAssigned()) {
                     if($_POST['assignId'][0]=='s' && $id==$ticket->getStaffId())
                         $errors['assignId']='Ticket atribuído ao time.';
                     elseif($_POST['assignId'][0]=='t' && $id==$ticket->getTeamId())
                         $errors['assignId']='Ticket designado ao time.';
                 }

                 //Comments are not required on self-assignment (claim)
                 if($claim && !$_POST['assign_comments'])
                     $_POST['assign_comments'] = 'Ticket reivindicado por '.$thisstaff->getName();
                 elseif(!$_POST['assign_comments'])
                     $errors['assign_comments'] = 'Comentários de atribuição necessário';
                 elseif(strlen($_POST['assign_comments'])<5)
                         $errors['assign_comments'] = 'Comentário muito curto';

                 if(!$errors && $ticket->assign($_POST['assignId'], $_POST['assign_comments'], !$claim)) {
                     if($claim) {
                         $msg = 'Ticket atribuído a você!';
                     } else {
                         $msg='Ticket atribuído com êxito para '.$ticket->getAssigned();
                         TicketLock::removeStaffLocks($thisstaff->getId(), $ticket->getId());
                         $ticket=null;
                     }
                 } elseif(!$errors['assign']) {
                     $errors['err'] = 'Não é possível concluir a atribuição de ticket';
                     $errors['assign'] = 'Corrija o(s) erro(s) abaixo e tente novamente!';
                 }
             }
            break;
        case 'postnote': /* Post Internal Note */
            //Make sure the staff can set desired state
            if($_POST['state']) {
                if($_POST['state']=='closed' && !$thisstaff->canCloseTickets())
                    $errors['state'] = "Você não tem permissão para fechar tickets";
                elseif(in_array($_POST['state'], array('overdue', 'notdue', 'unassigned'))
                        && (!($dept=$ticket->getDept()) || !$dept->isManager($thisstaff)))
                    $errors['state'] = "Você não tem permissão para definir o estado do ticket";
            }

            $vars = $_POST;
            if($_FILES['attachments'])
                $vars['files'] = AttachmentFile::format($_FILES['attachments']);

            $wasOpen = ($ticket->isOpen());
            if(($note=$ticket->postNote($vars, $errors, $thisstaff))) {

                $msg='Nota interna enviada com sucesso';
                if($wasOpen && $ticket->isClosed())
                    $ticket = null; //Going back to main listing.
                else
                    // Ticket is still open -- clear draft for the note
                    Draft::deleteForNamespace('ticket.note.'.$ticket->getId(),
                        $thisstaff->getId());

            } else {

                if(!$errors['err'])
                    $errors['err'] = 'Não é possível postar nota interna - dados em falta ou inválido.';

                $errors['postnote'] = 'Não é possível postar a nota. Corrija o(s) erro(s) abaixo e tente novamente!';
            }
            break;
        case 'edit':
        case 'update':
            $forms=DynamicFormEntry::forTicket($ticket->getId());
            foreach ($forms as $form) {
                // Don't validate deleted forms
                if (!in_array($form->getId(), $_POST['forms']))
                    continue;
                $form->setSource($_POST);
                if (!$form->isValid())
                    $errors = array_merge($errors, $form->errors());
            }
            if(!$ticket || !$thisstaff->canEditTickets())
                $errors['err']='Permissão negada. Você não tem permissão para editar tickets';
            elseif($ticket->update($_POST,$errors)) {
                $msg='Ticket atualizado com sucesso';
                $_REQUEST['a'] = null; //Clear edit action - going back to view.
                //Check to make sure the staff STILL has access post-update (e.g dept change).
                foreach ($forms as $f) {
                    // Drop deleted forms
                    $idx = array_search($f->getId(), $_POST['forms']);
                    if ($idx === false) {
                        $f->delete();
                    }
                    else {
                        $f->set('sort', $idx);
                        $f->save();
                    }
                }
                if(!$ticket->checkStaffAccess($thisstaff))
                    $ticket=null;
            } elseif(!$errors['err']) {
                $errors['err']='Não é possível atualizar o bilhete. Corrija os erros abaixo e tente novamente!';
            }
            break;
        case 'process':
            switch(strtolower($_POST['do'])):
                case 'close':
                    if(!$thisstaff->canCloseTickets()) {
                        $errors['err'] = 'Permissão negada. Você não tem permissão para fechar tickets';
                    } elseif($ticket->isClosed()) {
                        $errors['err'] = 'Ticket já está fechado!!';
                    } elseif($ticket->close()) {
                        $msg='Ticket #'.$ticket->getNumber().' status definido como FECHADO';
                        //Log internal note
                        if($_POST['ticket_status_notes'])
                            $note = $_POST['ticket_status_notes'];
                        else
                            $note='Ticket fechado (sem comentários)';

                        $ticket->logNote('Ticket Closed', $note, $thisstaff);

                        //Going back to main listing.
                        TicketLock::removeStaffLocks($thisstaff->getId(), $ticket->getId());
                        $page=$ticket=null;

                    } else {
                        $errors['err']='Problemas para fechar a ticket. Tente novamente';
                    }
                    break;
                case 'reopen':
                    //if staff can close or create tickets ...then assume they can reopen.
                    if(!$thisstaff->canCloseTickets() && !$thisstaff->canCreateTickets()) {
                        $errors['err']='Permissão negada. Você não tem permissão para reabrir tickets.';
                    } elseif($ticket->isOpen()) {
                        $errors['err'] = 'Ticket já está aberto!';
                    } elseif($ticket->reopen()) {
                        $msg='Ticket REABERTO';

                        if($_POST['ticket_status_notes'])
                            $note = $_POST['ticket_status_notes'];
                        else
                            $note='Ticket reaberto (sem comentários)';

                        $ticket->logNote('Ticket Reopened', $note, $thisstaff);

                    } else {
                        $errors['err']='Problemas para reabrir o ticket. Tente novamente';
                    }
                    break;
                case 'release':
                    if(!$ticket->isAssigned() || !($assigned=$ticket->getAssigned())) {
                        $errors['err'] = 'Ticket não esta atribuído!';
                    } elseif($ticket->release()) {
                        $msg='Ticket released (unassigned) from '.$assigned;
                        $ticket->logActivity('Ticket unassigned',$msg.' by '.$thisstaff->getName());
                    } else {
                        $errors['err'] = 'Problems releasing the ticket. Try again';
                    }
                    break;
                case 'claim':
                    if(!$thisstaff->canAssignTickets()) {
                        $errors['err'] = 'Permissão negada. Você não tem permissão para atribuir tickets.';
                    } elseif(!$ticket->isOpen()) {
                        $errors['err'] = 'Somente tickets em aberto podem ser atribuídos';
                    } elseif($ticket->isAssigned()) {
                        $errors['err'] = 'Ticket está atribuído a'.$ticket->getAssigned();
                    } elseif($ticket->assignToStaff($thisstaff->getId(), ('Ticket claimed by '.$thisstaff->getName()), false)) {
                        $msg = 'Ticket atribuído a você!';
                    } else {
                        $errors['err'] = 'Problemas ao atribuir bilhete.Tente novamente';
                    }
                    break;
                case 'overdue':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']='Permissão negada. Você não tem permissão para definir ticket atrasado';
                    } elseif($ticket->markOverdue()) {
                        $msg='Ticket sinalizado como vencido';
                        $ticket->logActivity('Ticket Marked Overdue',($msg.' by '.$thisstaff->getName()));
                    } else {
                        $errors['err']='Problems marking the the ticket overdue. Try again';
                    }
                    break;
                case 'answered':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']='Permissão negada. Você não tem permissão para definir status do ticket';
                    } elseif($ticket->markAnswered()) {
                        $msg='Ticket marcado como respondido';
                        $ticket->logActivity('Ticket Marked Answered',($msg.' by '.$thisstaff->getName()));
                    } else {
                        $errors['err']='Problems marking the the ticket answered. Try again';
                    }
                    break;
                case 'unanswered':
                    $dept = $ticket->getDept();
                    if(!$dept || !$dept->isManager($thisstaff)) {
                        $errors['err']='Permission Denied. You are not allowed to flag tickets';
                    } elseif($ticket->markUnAnswered()) {
                        $msg='Ticket flagged as unanswered';
                        $ticket->logActivity('Ticket Marked Unanswered',($msg.' by '.$thisstaff->getName()));
                    } else {
                        $errors['err']='Problems marking the the ticket unanswered. Try again';
                    }
                    break;
                case 'banemail':
                    if(!$thisstaff->canBanEmails()) {
                        $errors['err']='Permission Denied. You are not allowed to ban emails';
                    } elseif(BanList::includes($ticket->getEmail())) {
                        $errors['err']='Email already in banlist';
                    } elseif(Banlist::add($ticket->getEmail(),$thisstaff->getName())) {
                        $msg='Email ('.$ticket->getEmail().') added to banlist';
                    } else {
                        $errors['err']='Unable to add the email to banlist';
                    }
                    break;
                case 'unbanemail':
                    if(!$thisstaff->canBanEmails()) {
                        $errors['err'] = 'Permission Denied. You are not allowed to remove emails from banlist.';
                    } elseif(Banlist::remove($ticket->getEmail())) {
                        $msg = 'Email removed from banlist';
                    } elseif(!BanList::includes($ticket->getEmail())) {
                        $warn = 'Email is not in the banlist';
                    } else {
                        $errors['err']='Unable to remove the email from banlist. Try again.';
                    }
                    break;
                case 'changeuser':
                    if (!$thisstaff->canEditTickets()) {
                        $errors['err'] = 'Permission Denied. You are not allowed to EDIT tickets!!';
                    } elseif (!$_POST['user_id'] || !($user=User::lookup($_POST['user_id']))) {
                        $errors['err'] = 'Unknown user selected!';
                    } elseif ($ticket->changeOwner($user)) {
                        $msg = 'Ticket ownership changed to ' . Format::htmlchars($user->getName());
                    } else {
                        $errors['err'] = 'Unable to change tiket ownership. Try again';
                    }
                    break;
                case 'delete': // Dude what are you trying to hide? bad customer support??
                    if(!$thisstaff->canDeleteTickets()) {
                        $errors['err']='Permission Denied. You are not allowed to DELETE tickets!!';
                    } elseif($ticket->delete()) {
                        $msg='Ticket #'.$ticket->getNumber().' deleted successfully';
                        //Log a debug note
                        $ost->logDebug('Ticket #'.$ticket->getNumber().' deleted',
                                sprintf('Ticket #%s deleted by %s',
                                    $ticket->getNumber(), $thisstaff->getName())
                                );
                        $ticket=null; //clear the object.
                    } else {
                        $errors['err']='Problems deleting the ticket. Try again';
                    }
                    break;
                default:
                    $errors['err']='You must select action to perform';
            endswitch;
            break;
        default:
            $errors['err']='Unknown action';
        endswitch;
        if($ticket && is_object($ticket))
            $ticket->reload();//Reload ticket info following post processing
    }elseif($_POST['a']) {

        switch($_POST['a']) {
            case 'mass_process':
                if(!$thisstaff->canManageTickets())
                    $errors['err']='You do not have permission to mass manage tickets. Contact admin for such access';
                elseif(!$_POST['tids'] || !is_array($_POST['tids']))
                    $errors['err']='No tickets selected. You must select at least one ticket.';
                else {
                    $count=count($_POST['tids']);
                    $i = 0;
                    switch(strtolower($_POST['do'])) {
                        case 'reopen':
                            if($thisstaff->canCloseTickets() || $thisstaff->canCreateTickets()) {
                                $note='Ticket reopened by '.$thisstaff->getName();
                                foreach($_POST['tids'] as $k=>$v) {
                                    if(($t=Ticket::lookup($v)) && $t->isClosed() && @$t->reopen()) {
                                        $i++;
                                        $t->logNote('Ticket Reopened', $note, $thisstaff);
                                    }
                                }

                                if($i==$count)
                                    $msg = "Selected tickets ($i) reopened successfully";
                                elseif($i)
                                    $warn = "$i of $count selected tickets reopened";
                                else
                                    $errors['err'] = 'Unable to reopen selected tickets';
                            } else {
                                $errors['err'] = 'You do not have permission to reopen tickets';
                            }
                            break;
                        case 'close':
                            if($thisstaff->canCloseTickets()) {
                                $note='Ticket closed without response by '.$thisstaff->getName();
                                foreach($_POST['tids'] as $k=>$v) {
                                    if(($t=Ticket::lookup($v)) && $t->isOpen() && @$t->close()) {
                                        $i++;
                                        $t->logNote('Ticket Closed', $note, $thisstaff);
                                    }
                                }

                                if($i==$count)
                                    $msg ="Selected tickets ($i) closed succesfully";
                                elseif($i)
                                    $warn = "$i of $count selected tickets closed";
                                else
                                    $errors['err'] = 'Unable to close selected tickets';
                            } else {
                                $errors['err'] = 'You do not have permission to close tickets';
                            }
                            break;
                        case 'mark_overdue':
                            $note='Ticket flagged as overdue by '.$thisstaff->getName();
                            foreach($_POST['tids'] as $k=>$v) {
                                if(($t=Ticket::lookup($v)) && !$t->isOverdue() && $t->markOverdue()) {
                                    $i++;
                                    $t->logNote('Ticket Marked Overdue', $note, $thisstaff);
                                }
                            }

                            if($i==$count)
                                $msg = "Selected tickets ($i) marked overdue";
                            elseif($i)
                                $warn = "$i of $count selected tickets marked overdue";
                            else
                                $errors['err'] = 'Unable to flag selected tickets as overdue';
                            break;
                        case 'delete':
                            if($thisstaff->canDeleteTickets()) {
                                foreach($_POST['tids'] as $k=>$v) {
                                    if(($t=Ticket::lookup($v)) && @$t->delete()) $i++;
                                }

                                //Log a warning
                                if($i) {
                                    $log = sprintf('%s (%s) just deleted %d ticket(s)',
                                            $thisstaff->getName(), $thisstaff->getUserName(), $i);
                                    $ost->logWarning('Tickets deleted', $log, false);

                                }

                                if($i==$count)
                                    $msg = "Selected tickets ($i) deleted successfully";
                                elseif($i)
                                    $warn = "$i of $count selected tickets deleted";
                                else
                                    $errors['err'] = 'Unable to delete selected tickets';
                            } else {
                                $errors['err'] = 'You do not have permission to delete tickets';
                            }
                            break;
                        default:
                            $errors['err']='Unknown or unsupported action - get technical help';
                    }
                }
                break;
            case 'open':
                $ticket=null;
                if(!$thisstaff || !$thisstaff->canCreateTickets()) {
                     $errors['err']='You do not have permission to create tickets. Contact admin for such access';
                } else {
                    $vars = $_POST;
                    $vars['uid'] = $user? $user->getId() : 0;

                    if(($ticket=Ticket::open($vars, $errors))) {
                        $msg='Ticket created successfully';
                        $_REQUEST['a']=null;
                        if (!$ticket->checkStaffAccess($thisstaff) || $ticket->isClosed())
                            $ticket=null;
                        Draft::deleteForNamespace('ticket.staff%', $thisstaff->getId());
                        unset($_SESSION[':form-data']);
                    } elseif(!$errors['err']) {
                        $errors['err']='Unable to create the ticket. Correct the error(s) and try again';
                    }
                }
                break;
        }
    }
    if(!$errors)
        $thisstaff ->resetStats(); //We'll need to reflect any changes just made!
endif;

/*... Quick stats ...*/
$stats= $thisstaff->getTicketsStats();

//Navigation
$nav->setTabActive('tickets');
if($cfg->showAnsweredTickets()) {
    $nav->addSubMenu(array('desc'=>'Abertos ('.number_format($stats['open']+$stats['answered']).')',
                            'title'=>'Abertos',
                            'href'=>'tickets.php',
                            'iconclass'=>'Ticket'),
                        (!$_REQUEST['status'] || $_REQUEST['status']=='open'));
} else {

    if($stats) {
        $nav->addSubMenu(array('desc'=>'Abertos ('.number_format($stats['open']).')',
                               'title'=>'Abertos',
                               'href'=>'tickets.php',
                               'iconclass'=>'Ticket'),
                            (!$_REQUEST['status'] || $_REQUEST['status']=='open'));
    }

    if($stats['answered']) {
        $nav->addSubMenu(array('desc'=>'Respondidos ('.number_format($stats['answered']).')',
                               'title'=>'Respondidos',
                               'href'=>'tickets.php?status=answered',
                               'iconclass'=>'answeredTickets'),
                            ($_REQUEST['status']=='answered'));
    }
}

if($stats['assigned']) {

    $nav->addSubMenu(array('desc'=>'Meus Tickets ('.number_format($stats['assigned']).')',
                           'title'=>'Meus Tickets',
                           'href'=>'tickets.php?status=assigned',
                           'iconclass'=>'assignedTickets'),
                        ($_REQUEST['status']=='assigned'));
}

if($stats['overdue']) {
    $nav->addSubMenu(array('desc'=>'Atrasados ('.number_format($stats['overdue']).')',
                           'title'=>'Atrasados',
                           'href'=>'tickets.php?status=overdue',
                           'iconclass'=>'overdueTickets'),
                        ($_REQUEST['status']=='overdue'));

    if(!$sysnotice && $stats['overdue']>10)
        $sysnotice=$stats['overdue'] .' overdue tickets!';
}

if($thisstaff->showAssignedOnly() && $stats['closed']) {
    $nav->addSubMenu(array('desc'=>'Meus Fechados ('.number_format($stats['closed']).')',
                           'title'=>'Meus Fechados',
                           'href'=>'tickets.php?status=closed',
                           'iconclass'=>'closedTickets'),
                        ($_REQUEST['status']=='closed'));
} else {

    $nav->addSubMenu(array('desc'=>'Fechados ('.number_format($stats['closed']).')',
                           'title'=>'Fechados',
                           'href'=>'tickets.php?status=closed',
                           'iconclass'=>'closedTickets'),
                        ($_REQUEST['status']=='closed'));
}

if($thisstaff->canCreateTickets()) {
    $nav->addSubMenu(array('desc'=>'Novo',
                           'title' => 'Novo',
                           'href'=>'tickets.php?a=open',
                           'iconclass'=>'newTicket',
                           'id' => 'new-ticket'),
                        ($_REQUEST['a']=='open'));
}


$ost->addExtraHeader('<script type="text/javascript" src="js/ticket.js?bba9ccc"></script>');
$ost->addExtraHeader('<meta name="tip-namespace" content="tickets.queue" />',
    "$('#content').data('tipNamespace', 'tickets.queue');");

$inc = 'tickets.inc.php';
if($ticket) {
    $ost->setPageTitle('Ticket #'.$ticket->getNumber());
    $nav->setActiveSubMenu(-1);
    $inc = 'ticket-view.inc.php';
    if($_REQUEST['a']=='edit' && $thisstaff->canEditTickets()) {
        $inc = 'ticket-edit.inc.php';
        if (!$forms) $forms=DynamicFormEntry::forTicket($ticket->getId());
        // Auto add new fields to the entries
        foreach ($forms as $f) $f->addMissingFields();
    } elseif($_REQUEST['a'] == 'print' && !$ticket->pdfExport($_REQUEST['psize'], $_REQUEST['notes']))
        $errors['err'] = 'Internal error: Unable to export the ticket to PDF for print.';
} else {
    $inc = 'tickets.inc.php';
    if($_REQUEST['a']=='open' && $thisstaff->canCreateTickets())
        $inc = 'ticket-open.inc.php';
    elseif($_REQUEST['a'] == 'export') {
        require_once(INCLUDE_DIR.'class.export.php');
        $ts = strftime('%Y%m%d');
        if (!($token=$_REQUEST['h']))
            $errors['err'] = 'Query token required';
        elseif (!($query=$_SESSION['search_'.$token]))
            $errors['err'] = 'Query token not found';
        elseif (!Export::saveTickets($query, "tickets-$ts.csv", 'csv'))
            $errors['err'] = 'Internal error: Unable to dump query results';
    }

    //Clear active submenu on search with no status
    if($_REQUEST['a']=='search' && !$_REQUEST['status'])
        $nav->setActiveSubMenu(-1);

    //set refresh rate if the user has it configured
    if(!$_POST && !$_REQUEST['a'] && ($min=$thisstaff->getRefreshRate())) {
        $js = "clearTimeout(window.ticket_refresh);
               window.ticket_refresh = setTimeout($.refreshTicketView,"
            .($min*60000).");";
        $ost->addExtraHeader('<script type="text/javascript">'.$js.'</script>',
            $js);
    }
}

require_once(STAFFINC_DIR.'header.inc.php');
require_once(STAFFINC_DIR.$inc);
require_once(STAFFINC_DIR.'footer.inc.php');

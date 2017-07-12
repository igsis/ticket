 
<?php
//Note that ticket obj is initiated in tickets.php.
if(!defined('OSTSCPINC') || !$thisstaff || !is_object($ticket) || !$ticket->getId()) die('Invalid path');

//Make sure the staff is allowed to access the page.
if(!@$thisstaff->isStaff() || !$ticket->checkStaffAccess($thisstaff)) die('Access Denied');

//Re-use the post info on error...savekeyboards.org (Why keyboard? -> some people care about objects than users!!)
$info=($_POST && $errors)?Format::input($_POST):array();

//Auto-lock the ticket if locking is enabled.. If already locked by the user then it simply renews.
if($cfg->getLockTime() && !$ticket->acquireLock($thisstaff->getId(),$cfg->getLockTime()))
    $warn.='Unable to obtain a lock on the ticket';

//Get the goodies.
$dept  = $ticket->getDept();  //Dept
$staff = $ticket->getStaff(); //Assigned or closed by..
$user  = $ticket->getOwner(); //Ticket User (EndUser)
$team  = $ticket->getTeam();  //Assigned team.
$sla   = $ticket->getSLA();
$lock  = $ticket->getLock();  //Ticket lock obj
$id    = $ticket->getId();    //Ticket ID.

//Useful warnings and errors the user might want to know!
if($ticket->isAssigned() && (
            ($staff && $staff->getId()!=$thisstaff->getId())
         || ($team && !$team->hasMember($thisstaff))
        ))
    $warn.='&nbsp;&nbsp;<span class="Icon assignedTicket">Ticket is assigned to '.implode('/', $ticket->getAssignees()).'</span>';
if(!$errors['err'] && ($lock && $lock->getStaffId()!=$thisstaff->getId()))
    $errors['err']='This ticket is currently locked by '.$lock->getStaffName();
if(!$errors['err'] && ($emailBanned=TicketFilter::isBanned($ticket->getEmail())))
    $errors['err']='Email is in banlist! Must be removed before any reply/response';

$unbannable=($emailBanned) ? BanList::includes($ticket->getEmail()) : false;

if($ticket->isOverdue())
    $warn.='&nbsp;&nbsp;<span class="Icon overdueTicket">Marked overdue!</span>';

?>
<table width="940" cellpadding="2" cellspacing="0" border="0">
    <tr>
        <td width="50%" class="has_bottom_border">
             <h2><a href="tickets.php?id=<?php echo $ticket->getId(); ?>"
             title="Reload"><i class="icon-refresh"></i> Ticket #<?php echo $ticket->getNumber(); ?></a></h2>
        </td>
        <td width="50%" class="right_align has_bottom_border">
            <?php
            if ($thisstaff->canBanEmails()
                    || $thisstaff->canEditTickets()
                    || ($dept && $dept->isManager($thisstaff))) { ?>
            <span class="action-button" data-dropdown="#action-dropdown-more">
                <span ><i class="icon-cog"></i> Mais</span>
                <i class="icon-caret-down"></i>
            </span>
            <?php
            } ?>
            <?php if($thisstaff->canDeleteTickets()) { ?>
                <a id="ticket-delete" class="action-button confirm-action" href="#delete"><i class="icon-trash"></i> Excluir</a>
            <?php } ?>
            <?php
            if($thisstaff->canCloseTickets()) {
                if($ticket->isOpen()) {?>
                <a id="ticket-close" class="action-button" href="#close"><i class="icon-remove-circle"></i> Fechar</a>
                <?php
                } else { ?>
                <a id="ticket-reopen" class="action-button" href="#reopen"><i class="icon-undo"></i> Reabrir</a>
                <?php
                } ?>
            <?php
            } ?>
            <?php
            if($thisstaff->canEditTickets()) { ?>
                <a class="action-button" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=edit"><i class="icon-edit"></i> Editar</a>
            <?php
            } ?>
            <?php
            if($ticket->isOpen() && !$ticket->isAssigned() && $thisstaff->canAssignTickets()) {?>
                <a id="ticket-claim" class="action-button confirm-action" href="#claim"><i class="icon-user"></i> Reivindicar</a>

            <?php
            }?>
            <span class="action-button" data-dropdown="#action-dropdown-print">
                <a id="ticket-print" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print"><i class="icon-print"></i> Imprimir</a>
                <i class="icon-caret-down"></i>
            </span>
            <div id="action-dropdown-print" class="action-dropdown anchor-right">
              <ul>
                 <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=0"><i
                 class="icon-file-alt"></i> Tópico do Ticket</a>
                 <li><a class="no-pjax" target="_blank" href="tickets.php?id=<?php echo $ticket->getId(); ?>&a=print&notes=1"><i
                 class="icon-file-text-alt"></i> Tópico + Notas Internas</a>
              </ul>
            </div>
            <div id="action-dropdown-more" class="action-dropdown anchor-right">
              <ul>
                <?php
                 if($thisstaff->canEditTickets()) { ?>
                    <li><a class="change-user" href="#tickets/<?php echo $ticket->getId(); ?>/change-user"><i class="icon-user"></i> Alterar proprietário do Ticket</a></li>
                <?php
                 }
                if($ticket->isOpen() && ($dept && $dept->isManager($thisstaff))) {

                    if($ticket->isAssigned()) { ?>
                        <li><a  class="confirm-action" id="ticket-release" href="#release"><i class="icon-user"></i> Liberar (atribuição) de Ticket</a></li>
                    <?php
                    }

                    if(!$ticket->isOverdue()) { ?>
                        <li><a class="confirm-action" id="ticket-overdue" href="#overdue"><i class="icon-bell"></i> Marcar como Atrasado</a></li>
                    <?php
                    }

                    if($ticket->isAnswered()) { ?>
                        <li><a class="confirm-action" id="ticket-unanswered" href="#unanswered"><i class="icon-circle-arrow-left"></i> Marcar como não Respondido</a></li>
                    <?php
                    } else { ?>
                        <li><a class="confirm-action" id="ticket-answered" href="#answered"><i class="icon-circle-arrow-right"></i> Marcar como Respondido</a></li>
                    <?php
                    }
                } ?>
                <li><a href="#ajax.php/tickets/<?php echo $ticket->getId();
                    ?>/forms/manage" onclick="javascript:
                    $.dialog($(this).attr('href').substr(1), 201);
                    return false"
                    ><i class="icon-paste"></i> Gerenciar Formulário</a></li>

<?php           if($thisstaff->canBanEmails()) {
                     if(!$emailBanned) {?>
                        <li><a class="confirm-action" id="ticket-banemail"
                            href="#banemail"><i class="icon-ban-circle"></i> Banir Email (<?php echo $ticket->getEmail(); ?>)</a></li>
                <?php
                     } elseif($unbannable) { ?>
                        <li><a  class="confirm-action" id="ticket-banemail"
                            href="#unbanemail"><i class="icon-undo"></i> Restituir Email (<?php echo $ticket->getEmail(); ?>)</a></li>
                    <?php
                     }
                }?>
              </ul>
            </div>
        </td>
    </tr>
</table>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50%">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100">Status:</th>
                    <td><?php echo ucfirst($ticket->getStatus()); ?></td>
                </tr>
                <tr>
                    <th>Prioridade:</th>
                    <td><?php echo $ticket->getPriority(); ?></td>
                </tr>
                <tr>
                    <th>Departamento:</th>
                    <td><?php echo Format::htmlchars($ticket->getDeptName()); ?></td>
                </tr>
                <tr>
                    <th>Data de Criação:</th>
                    <td><?php echo Format::db_datetime($ticket->getCreateDate()); ?></td>
                </tr>
            </table>
        </td>
        <td width="50%" style="vertical-align:top">
            <table border="0" cellspacing="" cellpadding="4" width="100%">
                <tr>
                    <th width="100">Usuário:</th>
                    <td><a href="#tickets/<?php echo $ticket->getId(); ?>/user"
                        onclick="javascript:
                            $.userLookup('ajax.php/tickets/<?php echo $ticket->getId(); ?>/user',
                                    function (user) {
                                        $('#user-'+user.id+'-name').text(user.name);
                                        $('#user-'+user.id+'-email').text(user.email);
                                        $('#user-'+user.id+'-phone').text(user.phone);
                                        $('select#emailreply option[value=1]').text(user.name+' <'+user.email+'>');
                                    });
                            return false;
                            "><i class="icon-user"></i> <span id="user-<?php echo $ticket->getOwnerId(); ?>-name"
                            ><?php echo Format::htmlchars($ticket->getName());
                        ?></span></a>
                        <?php
                        if($user) {
                            echo sprintf('&nbsp;&nbsp;<a href="tickets.php?a=search&uid=%d" title="Tickets Relacionados" data-dropdown="#action-dropdown-stats">(<b>%d</b>)</a>',
                                    urlencode($user->getId()), $user->getNumTickets());
                        ?>
                            <div id="action-dropdown-stats" class="action-dropdown anchor-right">
                                <ul>
                                    <?php
                                    if(($open=$user->getNumOpenTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=open&uid=%s"><i class="icon-folder-open-alt icon-fixed-width"></i> %d Tickets Abertos</a></li>',
                                                $user->getId(), $open);
                                    if(($closed=$user->getNumClosedTickets()))
                                        echo sprintf('<li><a href="tickets.php?a=search&status=closed&uid=%d"><i class="icon-folder-close-alt icon-fixed-width"></i> %d Tickets Fechados</a></li>',
                                                $user->getId(), $closed);
                                    ?>
                                    <li><a href="tickets.php?a=search&uid=<?php echo $ticket->getOwnerId(); ?>"><i class="icon-double-angle-right icon-fixed-width"></i> Todos Tickets</a></li>
                                    <li><a href="users.php?id=<?php echo
                                    $user->getId(); ?>"><i class="icon-user
                                    icon-fixed-width"></i>Gerenciar Usuário</a></li>
<?php if ($user->getOrgId()) { ?>
                                    <li><a href="orgs.php?id=<?php echo $user->getOrgId(); ?>"><i class="icon-building icon-fixed-width"></i> Organizar</a></li>
<?php } ?>
                                </ul>
                            </div>
                    <?php
                        }
                    ?>
                    </td>
                </tr>
                <tr>
                    <th>E-mail:</th>
                    <td>
                        <span id="user-<?php echo $ticket->getOwnerId(); ?>-email"><?php echo $ticket->getEmail(); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>Telefone:</th>
                    <td>
                        <span id="user-<?php echo $ticket->getOwnerId(); ?>-phone"><?php echo $ticket->getPhoneNumber(); ?></span>
                    </td>
                </tr>
                <tr>
                    <th>Origem:</th>
                    <td><?php
                        echo Format::htmlchars($ticket->getSource());

                        if($ticket->getIP())
                            echo '&nbsp;&nbsp; <span class="faded">('.$ticket->getIP().')</span>';
                        ?>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
    <tr>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <?php
                if($ticket->isOpen()) { ?>
                <tr>
                    <th width="100">Atribuído a:</th>
                    <td>
                        <?php
                        if($ticket->isAssigned())
                            echo Format::htmlchars(implode('/', $ticket->getAssignees()));
                        else
                            echo '<span class="faded">&mdash; Não abribuído &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } else { ?>
                <tr>
                    <th width="100">Fechado Por:</th>
                    <td>
                        <?php
                        if(($staff = $ticket->getStaff()))
                            echo Format::htmlchars($staff->getName());
                        else
                            echo '<span class="faded">&mdash; Desconhecido &mdash;</span>';
                        ?>
                    </td>
                </tr>
                <?php
                } ?>
                <tr>
                    <th>Tipo de SLA:</th>
                    <td><?php echo $sla?Format::htmlchars($sla->getName()):'<span class="faded">&mdash; Nenhum &mdash;</span>'; ?></td>
                </tr>
                <?php
                if($ticket->isOpen()){ ?>
                <tr>
                    <th>Data de Vencimento:</th>
                    <td><?php echo Format::db_datetime($ticket->getEstDueDate()); ?></td>
                </tr>
                <?php
                }else { ?>
                <tr>
                    <th>Data de Fechamento:</th>
                    <td><?php echo Format::db_datetime($ticket->getCloseDate()); ?></td>
                </tr>
                <?php
                }
                ?>
            </table>
        </td>
        <td width="50%">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
                <tr>
                    <th width="100">Motivo:</th>
                    <td><?php echo Format::htmlchars($ticket->getHelpTopic()); ?></td>
                </tr>
                <tr>
                    <th nowrap>Última Mensagem::</th>
                    <td><?php echo Format::db_datetime($ticket->getLastMsgDate()); ?></td>
                </tr>
                <tr>
                    <th nowrap>Última Resposta:</th>
                    <td><?php echo Format::db_datetime($ticket->getLastRespDate()); ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<table class="ticket_info" cellspacing="0" cellpadding="0" width="940" border="0">
<?php
$idx = 0;
foreach (DynamicFormEntry::forTicket($ticket->getId()) as $form) {
    // Skip core fields shown earlier in the ticket view
    // TODO: Rewrite getAnswers() so that one could write
    //       ->getAnswers()->filter(not(array('field__name__in'=>
    //           array('email', ...))));
    $answers = array_filter($form->getAnswers(), function ($a) {
        return !in_array($a->getField()->get('name'),
                array('e-mail','assunto','nome','prioridade'));
        });
    if (count($answers) == 0)
        continue;
    ?>
        <tr>
        <td colspan="2">
            <table cellspacing="0" cellpadding="4" width="100%" border="0">
            <?php foreach($answers as $a) {
                if (!($v = $a->display())) continue; ?>
                <tr>
                    <th width="100"><?php
    echo $a->getField()->get('label');
                    ?>:</th>
                    <td><?php
    echo $v;
                    ?></td>
                </tr>
                <?php } ?>
            </table>
        </td>
        </tr>
    <?php
    $idx++;
    } ?>
</table>
<div class="clear"></div>
<h2 style="padding:10px 0 5px 0; font-size:11pt;"><?php echo Format::htmlchars($ticket->getSubject()); ?></h2>
<?php
$tcount = $ticket->getThreadCount();
$tcount+= $ticket->getNumNotes();
?>
<ul id="threads">
    <li><a class="active" id="toggle_ticket_thread" href="#">Tópico do Ticket (<?php echo $tcount; ?>)</a></li>
</ul>
<div id="ticket_thread">
    <?php
    $threadTypes=array('M'=>'mensagem','R'=>'resposta', 'N'=>'nota');
    /* -------- Messages & Responses & Notes (if inline)-------------*/
    $types = array('M', 'R', 'N');
    if(($thread=$ticket->getThreadEntries($types))) {
       foreach($thread as $entry) { ?>
        <table class="thread-entry <?php echo $threadTypes[$entry['thread_type']]; ?>" cellspacing="0" cellpadding="1" width="940" border="0">
            <tr>
                <th colspan="4" width="100%">
                <div>
                    <span style="display:inline-block"><?php
                        echo Format::db_datetime($entry['criado']);?></span>
                    <span style="display:inline-block;padding-left:1em" class="faded title"><?php
                        echo Format::truncate($entry['título'], 100); ?></span>
                    <span style="float:right;white-space:no-wrap;display:inline-block">
                        <span style="vertical-align:middle;" class="textra"></span>
                        <span style="vertical-align:middle;"
                            class="tmeta faded title"><?php
                            echo Format::htmlchars($entry['nome'] ?: $entry['poster']); ?></span>
                    </span>
                </div>
                </th>''''
            </tr>
            <tr><td colspan="4" class="thread-body" id="thread-id-<?php
                echo $entry['id']; ?>"><div><?php
                echo $entry['body']->toHtml(); ?></div></td></tr>
            <?php
            if($entry['attachments']
                    && ($tentry = $ticket->getThreadEntry($entry['id']))
                    && ($urls = $tentry->getAttachmentUrls())
                    && ($links = $tentry->getAttachmentsLinks())) {?>
            <tr>
                <td class="info" colspan="4"><?php echo $tentry->getAttachmentsLinks(); ?></td>
            </tr> <?php
            }
            if ($urls) { ?>
                <script type="text/javascript">
                    $('#thread-id-<?php echo $entry['id']; ?>')
                        .data('urls', <?php
                            echo JsonDataEncoder::encode($urls); ?>)
                        .data('id', <?php echo $entry['id']; ?>);
                </script>
<?php
            } ?>
        </table>
        <?php
        if($entry['thread_type']=='M')
            $msgId=$entry['id'];
       }
    } else {
        echo '<p>Error fetching ticket thread - get technical help.</p>';
    }?>
</div>
<div class="clear" style="padding-bottom:10px;"></div>
<?php if($errors['err']) { ?>
    <div id="msg_error"><?php echo $errors['err']; ?></div>
<?php }elseif($msg) { ?>
    <div id="msg_notice"><?php echo $msg; ?></div>
<?php }elseif($warn) { ?>
    <div id="msg_warning"><?php echo $warn; ?></div>
<?php } ?>

<div id="response_options">
    <ul class="tabs">
        <?php
        if($thisstaff->canPostReply()) { ?>
        <li><a id="reply_tab" href="#reply">Responder</a></li>
        <?php
        } ?>
        <li><a id="note_tab" href="#note">Nota Interna</a></li>
        <?php
        if($thisstaff->canTransferTickets()) { ?>
        <li><a id="transfer_tab" href="#transfer">Transf. Dept. </a></li>
        <?php
        }

        if($thisstaff->canAssignTickets()) { ?>
        <li><a id="assign_tab" href="#assign"><?php echo $ticket->isAssigned()?'Reatribuir Ticket':'Atribuir Ticket'; ?></a></li>
        <?php
        } ?>
    </ul>
    <?php
    if($thisstaff->canPostReply()) { ?>
    <form id="reply" action="tickets.php?id=<?php echo $ticket->getId(); ?>#reply" name="reply" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="msgId" value="<?php echo $msgId; ?>">
        <input type="hidden" name="a" value="reply">
        <span class="error"></span>
        <table style="width:100%" border="0" cellspacing="0" cellpadding="3">
           <tbody id="to_sec">
            <tr>
                <td width="120">
                    <label><strong>Para:</strong></label>
                </td>
                <td>
                    <?php
                    # XXX: Add user-to-name and user-to-email HTML ID#s
                    $to =sprintf('%s &lt;%s&gt;',
                            Format::htmlchars($ticket->getName()),
                            $ticket->getReplyToEmail());
                    $emailReply = (!isset($info['emailreply']) || $info['emailreply']);
                    ?>
                    <select id="emailreply" name="emailreply">
                        <option value="1" <?php echo $emailReply ?  'selected="selected"' : ''; ?>><?php echo $to; ?></option>
                        <option value="0" <?php echo !$emailReply ? 'selected="selected"' : ''; ?>
                            >&mdash;Não enviar resposta por E-mail&mdash;</option>
                    </select>
                </td>
            </tr>
            </tbody>
            <?php
            if(1) { //Make CC optional feature? NO, for now.
                ?>
            <tbody id="cc_sec"
                style="display:<?php echo $emailReply?  'table-row-group':'none'; ?>;">
             <tr>
                <td width="120">
                    <label><strong>Com Cópia:</strong></label>
                </td>
                <td>
                    <input type='checkbox' value='1' name="emailcollab" id="emailcollab"
                        <?php echo ((!$info['emailcollab'] && !$errors) || isset($info['emailcollab']))?'checked="checked"':''; ?>
                        style="display:<?php echo $ticket->getNumCollaborators() ? 'inline-block': 'none'; ?>;"
                        >
                    <?php
                    $recipients = 'Adicionar destinatários';
                    if ($ticket->getNumCollaborators())
                        $recipients = sprintf('Recipients (%d of %d)',
                                $ticket->getNumActiveCollaborators(),
                                $ticket->getNumCollaborators());

                    echo sprintf('<span><a class="collaborators preview"
                            href="#tickets/%d/collaborators"><span id="recipients">%s</span></a></span>',
                            $ticket->getId(),
                            $recipients);
                   ?>
                </td>
             </tr>
            </tbody>
            <?php
            } ?>
            <tbody id="resp_sec">
            <?php
            if($errors['response']) {?>
            <tr><td width="120">&nbsp;</td><td class="error"><?php echo $errors['response']; ?>&nbsp;</td></tr>
            <?php
            }?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong>Resposta:</strong></label>
                </td>
                <td>
                    <select id="cannedResp" name="cannedResp">
                        <option value="0" selected="selected">Selecione um modelo de resposta</option>
                        <option value='lastmessage'>Mensagem original</option>
                        <option value='original'>Última Mensagem</option>
                        <?php
                        if(($cannedResponses=Canned::responsesByDeptId($ticket->getDeptId()))) {
                            echo '<option value="0" disabled="disabled">
                                ------------- Modelo de Respostas ------------- </option>';
                            foreach($cannedResponses as $id =>$title)
                                echo sprintf('<option value="%d">%s</option>',$id,$title);
                        }
                        ?>
                    </select>
                    <br>
                    <?php
                    $signature = '';
                    switch ($thisstaff->getDefaultSignatureType()) {
                    case 'dept':
                        if ($dept && $dept->canAppendSignature())
                           $signature = $dept->getSignature();
                       break;
                    case 'mine':
                        $signature = $thisstaff->getSignature();
                        break;
                    } ?>
                    <input type="hidden" name="draft_id" value=""/>
                    <textarea name="response" id="response" cols="50"
                        data-draft-namespace="ticket.response"
                        data-signature-field="signature" data-dept-id="<?php echo $dept->getId(); ?>"
                        data-signature="<?php
                            echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                        placeholder="Start writing your response here. Use canned responses from the drop-down above"
                        data-draft-object-id="<?php echo $ticket->getId(); ?>"
                        rows="9" wrap="soft"
                        class="richtext ifhtml draft draft-delete"><?php
                        echo $info['response']; ?></textarea>
                </td>
            </tr>
            <?php
            if($cfg->allowAttachments()) { ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label for="attachment">Anexos:</label>
                </td>
                <td id="reply_form_attachments" class="attachments">
                    <div class="canned_attachments">
                    </div>
                    <div class="uploads">
                    </div>
                    <div class="file_input">
                        <input type="file" class="multifile" name="attachments[]" size="30" value="" />
                    </div>
                </td>
            </tr>
            <?php
            }?>
            <tr>
                <td width="120">
                    <label for="signature" class="left">Assinatura:</label>
                </td>
                <td>
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> Nenhuma</label>
                    <?php
                    if($thisstaff->getSignature()) {?>
                    <label><input type="radio" name="signature" value="mine"
                        <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> Minha Assinatura</label>
                    <?php
                    } ?>
                    <?php
                    if($dept && $dept->canAppendSignature()) { ?>
                    <label><input type="radio" name="signature" value="dept"
                        <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>>
                        Assinatura do Dept. (<?php echo Format::htmlchars($dept->getName()); ?>)</label>
                    <?php
                    } ?>
                </td>
            </tr>
            <?php
            if($ticket->isClosed() || $thisstaff->canCloseTickets()) { ?>
            <tr>
                <td width="120">
                    <label><strong>Status do Ticket :</strong></label>
                </td>
                <td>
                    <?php
                    $statusChecked=isset($info['reply_ticket_status'])?'checked="checked"':'';
                    if($ticket->isClosed()) { ?>
                        <label><input type="checkbox" name="reply_ticket_status" id="reply_ticket_status" value="Open"
                            <?php echo $statusChecked; ?>> Reabrir em Resposta</label>
                   <?php
                    } elseif($thisstaff->canCloseTickets()) { ?>
                         <label><input type="checkbox" name="reply_ticket_status" id="reply_ticket_status" value="Closed"
                              <?php echo $statusChecked; ?>> Fechar em Resposta</label>
                   <?php
                    } ?>
                </td>
            </tr>
            <?php
            } ?>
         </tbody>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="Responder">
            <input class="btn_sm" type="reset" value="Redefinir">
        </p>
    </form>
    <?php
    } ?>
    <form id="note" action="tickets.php?id=<?php echo $ticket->getId(); ?>#note" name="note" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="locktime" value="<?php echo $cfg->getLockTime(); ?>">
        <input type="hidden" name="a" value="postnote">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if($errors['postnote']) {?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['postnote']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong>Nota Interna:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <div>
                        <div class="faded" style="padding-left:0.15em">
                        Título da nota - resumo da nota (opcional)</div>
                        <input type="text" name="title" id="title" size="60" value="<?php echo $info['title']; ?>" >
                        <br/>
                        <span class="error">&nbsp;<?php echo $errors['title']; ?></span>
                    </div>
                    <br/>
                    <textarea name="note" id="internal_note" cols="80"
                        placeholder="Note details"
                        rows="9" wrap="soft" data-draft-namespace="ticket.note"
                        data-draft-object-id="<?php echo $ticket->getId(); ?>"
                        class="richtext ifhtml draft draft-delete"><?php echo $info['note'];
                        ?></textarea>
                        <span class="error"><?php echo $errors['note']; ?></span>
                        <br>
                </td>
            </tr>
            <?php
            if($cfg->allowAttachments()) { ?>
            <tr>
                <td width="120">
                    <label for="attachment">Anexos:</label>
                </td>
                <td class="attachments">
                    <div class="uploads">
                    </div>
                    <div class="file_input">
                        <input type="file" class="multifile" name="attachments[]" size="30" value="" />
                    </div>
                </td>
            </tr>
            <?php
            }
            ?>
            <tr><td colspan="2">&nbsp;</td></tr>
            <tr>
                <td width="120">
                    <label>Status do Ticket:</label>
                </td>
                <td>
                    <div class="faded"></div>
                    <select name="state">
                        <option value="" selected="selected">&mdash; inalterado &mdash;</option>
                        <?php
                        $state = $info['state'];
                        if($ticket->isClosed()){
                            echo sprintf('<option value="open" %s>Ticket Reaberto</option>',
                                    ($state=='reopen')?'selected="selelected"':'');
                        } else {
                            if($thisstaff->canCloseTickets())
                                echo sprintf('<option value="closed" %s>Ticket Fechado</option>',
                                    ($state=='closed')?'selected="selelected"':'');

                            /* Ticket open - states */
                            echo '<option value="" disabled="disabled">&mdash; Status do Ticket &mdash;</option>';

                            //Answer - state
                            if($ticket->isAnswered())
                                echo sprintf('<option value="unanswered" %s>Marcar como Não Respondido</option>',
                                    ($state=='unanswered')?'selected="selelected"':'');
                            else
                                echo sprintf('<option value="answered" %s>Marcar como Respondido</option>',
                                    ($state=='answered')?'selected="selelected"':'');

                            //overdue - state
                            // Only department manager can set/clear overdue flag directly.
                            // Staff with edit perm. can still set overdue date & change SLA.
                            if($dept && $dept->isManager($thisstaff)) {
                                if(!$ticket->isOverdue())
                                    echo sprintf('<option value="overdue" %s>Sinalizar como Atrasado</option>',
                                        ($state=='answered')?'selected="selelected"':'');
                                else
                                    echo sprintf('<option value="notdue" %s>Limpar Sinalizador Atrasado</option>',
                                        ($state=='notdue')?'selected="selelected"':'');

                                if($ticket->isAssigned())
                                    echo sprintf('<option value="unassigned" %s>Release (Unassign) Ticket</option>',
                                        ($state=='unassigned')?'selected="selelected"':'');
                            }
                        }?>
                    </select>
                    &nbsp;<span class='error'>*&nbsp;<?php echo $errors['state']; ?></span>
                </td>
            </tr>
            </div>
        </table>

       <p  style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="Adicionar">
           <input class="btn_sm" type="reset" value="Redefinir">
       </p>
   </form>
    <?php
    if($thisstaff->canTransferTickets()) { ?>
    <form id="transfer" action="tickets.php?id=<?php echo $ticket->getId(); ?>#transfer" name="transfer" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="ticket_id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="transfer">
        <table width="100%" border="0" cellspacing="0" cellpadding="3">
            <?php
            if($errors['transfer']) {
                ?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['transfer']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120">
                    <label for="deptId"><strong>Departamento:</strong></label>
                </td>
                <td>
                    <?php
                        echo sprintf('<span class="faded">O Ticket esta atualmente no departamento de <b>%s</b>.</span>', $ticket->getDeptName());
                    ?>
                    <br>
                    <select id="deptId" name="deptId">
                        <option value="0" selected="selected">&mdash; Selecione o Departamento Alvo&mdash;</option>
                        <?php
                        if($depts=Dept::getDepartments()) {
                            foreach($depts as $id =>$name) {
                                if($id==$ticket->getDeptId()) continue;
                                echo sprintf('<option value="%d" %s>%s</option>',
                                        $id, ($info['deptId']==$id)?'selected="selected"':'',$name);
                            }
                        }
                        ?>
                    </select>&nbsp;<span class='error'>*&nbsp;<?php echo $errors['deptId']; ?></span>
                </td>
            </tr>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong>Comentários:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <textarea name="transfer_comments" id="transfer_comments"
                        placeholder="Enter reasons for the transfer"
                        class="richtext ifhtml no-bar" cols="80" rows="7" wrap="soft"><?php
                        echo $info['transfer_comments']; ?></textarea>
                    <span class="error"><?php echo $errors['transfer_comments']; ?></span>
                </td>
            </tr>
        </table>
        <p style="padding-left:165px;">
           <input class="btn_sm" type="submit" value="Transferir">
           <input class="btn_sm" type="reset" value="Redefinir">
        </p>
    </form>
    <?php
    } ?>
    <?php
    if($thisstaff->canAssignTickets()) { ?>
    <form id="assign" action="tickets.php?id=<?php echo $ticket->getId(); ?>#assign" name="assign" method="post" enctype="multipart/form-data">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="assign">
        <table style="width:100%" border="0" cellspacing="0" cellpadding="3">

            <?php
            if($errors['assign']) {
                ?>
            <tr>
                <td width="120">&nbsp;</td>
                <td class="error"><?php echo $errors['assign']; ?></td>
            </tr>
            <?php
            } ?>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label for="assignId"><strong>Administrador:</strong></label>
                </td>
                <td>
                    <select id="assignId" name="assignId">
                        <option value="0" selected="selected">&mdash; Selecione um Membro ou Equipe &mdash;</option>
                        <?php
                        if($ticket->isOpen() && !$ticket->isAssigned())
                            echo sprintf('<option value="%d">Reivindicação de Ticket (comentários opcional)</option>', $thisstaff->getId());

                        $sid=$tid=0;

                        if ($dept->assignMembersOnly())
                            $users = $dept->getAvailableMembers();
                        else
                            $users = Staff::getAvailableStaffMembers();

                        if ($users) {
                            echo '<OPTGROUP label="Membros ('.count($users).')">';
                            $staffId=$ticket->isAssigned()?$ticket->getStaffId():0;
                            foreach($users as $id => $name) {
                                if($staffId && $staffId==$id)
                                    continue;

                                if (!is_object($name))
                                    $name = new PersonsName($name);

                                $k="s$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''), $name);
                            }
                            echo '</OPTGROUP>';
                        }

                        if(($teams=Team::getActiveTeams())) {
                            echo '<OPTGROUP label="Times ('.count($teams).')">';
                            $teamId=(!$sid && $ticket->isAssigned())?$ticket->getTeamId():0;
                            foreach($teams as $id => $name) {
                                if($teamId && $teamId==$id)
                                    continue;

                                $k="t$id";
                                echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                            }
                            echo '</OPTGROUP>';
                        }
                        ?>
                    </select>&nbsp;<span class='error'>*&nbsp;<?php echo $errors['assignId']; ?></span>
                    <?php
                    if($ticket->isAssigned() && $ticket->isOpen()) {
                        echo sprintf('<div class="faded">Ticket atualmente atribuído a <b>%s</b></div>',
                                $ticket->getAssignee());
                    } elseif ($ticket->isClosed()) { ?>
                        <div class="faded">Atribuir ticket fechado e <b>reabrir</b>!</div>
                    <?php } ?>
                </td>
            </tr>
            <tr>
                <td width="120" style="vertical-align:top">
                    <label><strong>Comentários:</strong><span class='error'>&nbsp;*</span></label>
                </td>
                <td>
                    <textarea name="assign_comments" id="assign_comments"
                        cols="80" rows="7" wrap="soft"
                        placeholder="Enter reasons for the assignment or instructions for assignee"
                        class="richtext ifhtml no-bar"><?php echo $info['assign_comments']; ?></textarea>
                    <span class="error"><?php echo $errors['assign_comments']; ?></span><br>
                </td>
            </tr>
        </table>
        <p  style="padding-left:165px;">
            <input class="btn_sm" type="submit" value="<?php echo $ticket->isAssigned()?'Reatribuir':'Atribuir'; ?>">
            <input class="btn_sm" type="reset" value="Redefinir">
        </p>
    </form>
    <?php
    } ?>
</div>
<div style="display:none;" class="dialog" id="print-options">
    <h3>Opções de Impressão</h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="print-form" name="print-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="a" value="print">
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <fieldset class="notes">
            <label for="notes">Imprimir Notas</label>
            <input type="checkbox" id="notes" name="notes" value="1"> Imprimir <b>Notas/Comentários</b> Internos
        </fieldset>
        <fieldset>
            <label for="psize">Tamanho do Papel:</label>
            <select id="psize" name="psize">
                <option value="">&mdash; Selecione Tamanho &mdash;</option>
                <?php
                  $options=array('Carta', 'Ofício', 'A4', 'A3');
                  $psize =$_SESSION['PAPER_SIZE']?$_SESSION['PAPER_SIZE']:$thisstaff->getDefaultPaperSize();
                  foreach($options as $v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $v,($psize==$v)?'selected="selected"':'', $v);
                  }
                ?>
            </select>
        </fieldset>
        <hr style="margin-top:3em"/>
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="reset" value="Redefinir">
                <input type="button" value="Cancelar" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="Imprimir">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<div style="display:none;" class="dialog" id="ticket-status">
    <h3><?php echo sprintf('%s Ticket #%s', ($ticket->isClosed()?'Reabrir':'Fechar'), $ticket->getNumber()); ?></h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <?php echo sprintf('Tem certeza que deseja <b>%s</b> este ticket?', $ticket->isClosed()?'REABRIR':'FECHAR'); ?>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="status-form" name="status-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" value="<?php echo $ticket->isClosed()?'reabrir':'fechar'; ?>">
        <fieldset>
            <div style="margin-bottom:0.5em">
            <em>Razões para a mudança de estado (nota interna). Opcional, mas altamente recomendado.</em>
            </div>
            <textarea name="ticket_status_notes" id="ticket_status_notes" cols="50" rows="5" wrap="soft"
                style="width:100%"
                class="richtext ifhtml no-bar"><?php echo $info['ticket_status_notes']; ?></textarea>
        </fieldset>
        <hr style="margin-top:1em"/>
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="reset" value="Redefinir">
                <input type="button" value="Cancelar" class="Fechar">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="<?php echo $ticket->isClosed()?'Reabrir':'Fechar'; ?>">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<div style="display:none;" class="dialog" id="confirm-action">
    <h3>Por favor Confirme</h3>
    <a class="close" href=""><i class="icon-remove-circle"></i></a>
    <hr/>
    <p class="confirm-action" style="display:none;" id="claim-confirm">
        Tem certeza que deseja <b>reivindicar</b> (auto atribuir) este ticket?
    </p>
    <p class="confirm-action" style="display:none;" id="answered-confirm">
        Tem certeza que deseja sinalizar o ticket como <b>respondido</b>?
    </p>
    <p class="confirm-action" style="display:none;" id="unanswered-confirm">
        Tem certeza que deseja sinalizar o ticket como <b>sem resposta</b>?
    </p>
    <p class="confirm-action" style="display:none;" id="overdue-confirm">
        Tem certeza que deseja sinalizar o ticket como  <font color="red"><b>atrasado</b></font>?
    </p>
    <p class="confirm-action" style="display:none;" id="banemail-confirm">
        Tem certeza que deseja <b>banir</b> <?php echo $ticket->getEmail(); ?>? <br><br>
        Novos tickets desse endereço de e-mail serão automaticamente rejeitados.
    </p>
    <p class="confirm-action" style="display:none;" id="unbanemail-confirm">
        Tem certeza que deseja <b>remover</b> <?php echo $ticket->getEmail(); ?> da lista de banidos?
    </p>
    <p class="confirm-action" style="display:none;" id="release-confirm">
        Tem certeza que deseja <b>desatribuir</b> ticket de <b><?php echo $ticket->getAssigned(); ?></b>?
    </p>
    <p class="confirm-action" style="display:none;" id="changeuser-confirm">
        <span id="msg_warning" style="display:block;vertical-align:top">
        <b><?php echo Format::htmlchars($ticket->getName()); ?></b> &lt;<?php echo $ticket->getEmail(); ?>&gt;
        <br> não terá mais acesso ao ticket.
        </span>
        Tem certeza que deseja <b>alterar</b> proprietário do ticket para <b><span id="newuser"></span></b>?
    </p>
    <p class="confirm-action" style="display:none;" id="delete-confirm">
        <font color="red"><strong>Tem certeza que deseja EXCLUIR este ticket?</strong></font>
        <br><br>Tickets excluídos NÃO PODEM ser recuperados, incluindo quaisquer anexos associados.
    </p>
    <div>Por favor, confirme para continuar.</div>
    <form action="tickets.php?id=<?php echo $ticket->getId(); ?>" method="post" id="confirm-form" name="confirm-form">
        <?php csrf_token(); ?>
        <input type="hidden" name="id" value="<?php echo $ticket->getId(); ?>">
        <input type="hidden" name="a" value="process">
        <input type="hidden" name="do" id="action" value="">
        <hr style="margin-top:1em"/>
        <p class="full-width">
            <span class="buttons" style="float:left">
                <input type="button" value="Cancelar" class="close">
            </span>
            <span class="buttons" style="float:right">
                <input type="submit" value="OK">
            </span>
         </p>
    </form>
    <div class="clear"></div>
</div>
<script type="text/javascript">
$(function() {
    $(document).on('click', 'a.change-user', function(e) {
        e.preventDefault();
        var tid = <?php echo $ticket->getOwnerId(); ?>;
        var cid = <?php echo $ticket->getOwnerId(); ?>;
        var url = 'ajax.php/'+$(this).attr('href').substr(1);
        $.userLookup(url, function(user) {
            if(cid!=user.id
                    && $('.dialog#confirm-action #changeuser-confirm').length) {
                $('#newuser').html(user.name +' &lt;'+user.email+'&gt;');
                $('.dialog#confirm-action #action').val('changeuser');
                $('#confirm-form').append('<input type=hidden name=user_id value='+user.id+' />');
                $('#overlay').show();
                $('.dialog#confirm-action .confirm-action').hide();
                $('.dialog#confirm-action p#changeuser-confirm')
                .show()
                .parent('div').show().trigger('click');
            }
        });
    });
});
</script>

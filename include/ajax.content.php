<?php
/*********************************************************************
    ajax.content.php

    AJAX interface for content fetching...allowed methods.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/

if(!defined('INCLUDE_DIR')) die('!');

class ContentAjaxAPI extends AjaxController {

    function log($id) {

        if($id && ($log=Log::lookup($id))) {
            $content=sprintf('<div
                    style="width:500px;">&nbsp;<strong>%s</strong><br><p
                    style="white-space:pre-line;">%s</p>
                    <hr><strong>Log Date:</strong> <em>%s</em> <strong>IP Address:</strong> <em>%s</em></div>',
                    $log->getTitle(),
                    Format::display(str_replace(',',', ',$log->getText())),
                    Format::db_daydatetime($log->getCreateDate()),
                    $log->getIP());
        }else {
            $content='<div style="width:295px;">&nbsp;<strong>Error:</strong>Unknown or invalid log ID</div>';
        }

        return $content;
    }

    function ticket_variables() {

        $content='
<div style="width:680px;">
    <h2>Ticket Variables</h2>
    Please note that non-base variables depend on the context of use. Visit osTicket Wiki for up-to-date documentation.
    <br/>
    <table width="100%" border="0" cellspacing=1 cellpadding=2>
        <tr><td width="55%" valign="top"><b>Base Variables</b></td><td><b>Outras Variáveis</b></td></tr>
        <tr>
            <td width="55%" valign="top">
                <table width="100%" border="0" cellspacing=1 cellpadding=1>
                    <tr><td width="130">%{ticket.id}</td><td>Ticket ID (internal ID)</td></tr>
                    <tr><td>%{ticket.number}</td><td>Ticket number (external ID)</td></tr>
                    <tr><td>%{ticket.email}</td><td>Endereço de E-mail</td></tr>
                    <tr><td>%{ticket.name}</td><td>Nome Completo &mdash;
                        <em>see name expansion</em></td></tr>
                    <tr><td>%{ticket.subject}</td><td>Assunto</td></tr>
                    <tr><td>%{ticket.phone}</td><td>Telefone | ext</td></tr>
                    <tr><td>%{ticket.status}</td><td>Status</td></tr>
                    <tr><td>%{ticket.priority}</td><td>Prioridade</td></tr>
                    <tr><td>%{ticket.assigned}</td><td>Assigned staff and/or team</td></tr>
                    <tr><td>%{ticket.create_date}</td><td>Data de criação</td></tr>
                    <tr><td>%{ticket.due_date}</td><td>Data de vencimento</td></tr>
                    <tr><td>%{ticket.close_date}</td><td>Data de fechamento</td></tr>
                    <tr><td>%{ticket.auth_token}</td><td>Auth. token used for auto-login</td></tr>
                    <tr><td>%{ticket.client_link}</td><td>Client\'s ticket view link</td></tr>
                    <tr><td>%{ticket.staff_link}</td><td>Staff\'s ticket view link</td></tr>
                    <tr><td colspan="2" style="padding:5px 0 5px 0;"><em>Expandable Variables (See Wiki)</em></td></tr>
                    <tr><td>%{ticket.<b>topic</b>}</td><td>Tópico de Ajuda</td></tr>
                    <tr><td>%{ticket.<b>dept</b>}</td><td>Departamento</td></tr>
                    <tr><td>%{ticket.<b>staff</b>}</td><td>Assigned/closing staff</td></tr>
                    <tr><td>%{ticket.<b>team</b>}</td><td>Assigned/closing team</td></tr>
                </table>
            </td>
            <td valign="top">
                <table width="100%" border="0" cellspacing=1 cellpadding=1>
                    <tr><td width="100">%{message}</td><td>Incoming message</td></tr>
                    <tr><td>%{response}</td><td>Outgoing response</td></tr>
                    <tr><td>%{comments}</td><td>Assign/transfer comments</td></tr>
                    <tr><td>%{note}</td><td>Internal note <em>(expandable)</em></td></tr>
                    <tr><td>%{assignee}</td><td>Assigned staff/team</td></tr>
                    <tr><td>%{assigner}</td><td>Staff assigning the ticket</td></tr>
                    <tr><td>%{url}</td><td>osTicket\'s base url (FQDN)</td></tr>
                    <tr><td>%{reset_link}</td>
                        <td>Reset link used by the password reset feature</td></tr>
                </table>
                <table width="100%" border="0" cellspacing=1 cellpadding=1>
                    <tr><td colspan="2"><b>Name Expansion</b></td></tr>
                    <tr><td>.first</td><td>First Name</td></tr>
                    <tr><td>.middle</td><td>Middle Name(s)</td></tr>
                    <tr><td>.last</td><td>Last Name</td></tr>
                    <tr><td>.full</td><td>First Last</td></tr>
                    <tr><td>.legal</td><td>First M. Last</td></tr>
                    <tr><td>.short</td><td>First L.</td></tr>
                    <tr><td>.formal</td><td>Mr. Last</td></tr>
                    <tr><td>.shortformal</td><td>F. Last</td></tr>
                    <tr><td>.lastfirst</td><td>Last, First</td></tr>
                </table>
            </td>
        </tr>
    </table>
</div>';

        return $content;
    }

    function getSignature($type, $id=null) {
        global $thisstaff;

        if (!$thisstaff)
            Http::response(403, 'Login Required');

        switch ($type) {
        case 'none':
            break;
        case 'mine':
            echo Format::viewableImages($thisstaff->getSignature());
            break;
        case 'dept':
            if (!($dept = Dept::lookup($id)))
                Http::response(404, 'No such department');
            echo Format::viewableImages($dept->getSignature());
            break;
        default:
            Http::response(400, 'Unknown signature type');
            break;
        }
    }

    function manageContent($id, $lang=false) {
        global $thisstaff;

        if (!$thisstaff)
            Http::response(403, 'Login Required');

        $content = Page::lookup($id, $lang);
        include STAFFINC_DIR . 'templates/content-manage.tmpl.php';
    }

    function manageNamedContent($type, $lang=false) {
        global $thisstaff;

        if (!$thisstaff)
            Http::response(403, 'Login Required');

        $content = Page::lookup(Page::getIdByType($type, $lang));
        include STAFFINC_DIR . 'templates/content-manage.tmpl.php';
    }

    function updateContent($id) {
        global $thisstaff;

        if (!$thisstaff)
            Http::response(403, 'Login Required');
        elseif (!$_POST['name'] || !$_POST['body'])
            Http::response(422, 'Please submit name and body');
        elseif (!($content = Page::lookup($id)))
            Http::response(404, 'No such content');

        $vars = array_merge($content->getHashtable(), $_POST);
        $errors = array();
        if (!$content->save($id, $vars, $errors)) {
            if ($errors['err'])
                Http::response(422, $errors['err']);
            else
                Http::response(500, 'Unable to update content: '.print_r($errors, true));
        }
    }
}
?>

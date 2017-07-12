<?php
if(!defined('OSTADMININC') || !$thisstaff || !$thisstaff->isAdmin() || !$config) die('Access Denied');
if(!($maxfileuploads=ini_get('max_file_uploads')))
    $maxfileuploads=DEFAULT_MAX_FILE_UPLOADS;
?>
<h2>Configurações e opções de Ticket</h2>
<form action="settings.php?t=tickets" method="post" id="save">
<?php csrf_token(); ?>
<input type="hidden" name="t" value="tickets" >
<table class="form_table settings_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4>Configurações Globais de Ticket</h4>
                <em>Configurações e opções de ticket padrão do sistema.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr><td width="220" class="required">IDs Ticket:</td>
            <td>
                <input type="radio" name="random_ticket_ids"  value="0" <?php echo !$config['random_ticket_ids']?'checked="checked"':''; ?> />
                Sequencial
                <input type="radio" name="random_ticket_ids"  value="1" <?php echo $config['random_ticket_ids']?'checked="checked"':''; ?> />
                Randômica 
            </td>
        </tr>

        <tr>
            <td width="180" class="required">
                SLA Padrão:
            </td>
            <td>
                <span>
                <select name="default_sla_id">
                    <option value="0">&mdash; Nenhum &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id => $name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id,
                                    ($config['default_sla_id'] && $id==$config['default_sla_id'])?'selected="selected"':'',
                                    $name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['default_sla_id']; ?></span>  <i class="help-tip icon-question-sign" href="#default_sla"></i>
                </span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">Prioridade Padrão:</td>
            <td>
                <select name="default_priority_id">
                    <?php
                    $priorities= db_query('SELECT priority_id,priority_desc FROM '.TICKET_PRIORITY_TABLE);
                    while (list($id,$tag) = db_fetch_row($priorities)){ ?>
                        <option value="<?php echo $id; ?>"<?php echo ($config['default_priority_id']==$id)?'selected':''; ?>><?php echo $tag; ?></option>
                    <?php
                    } ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['default_priority_id']; ?></span> <i class="help-tip icon-question-sign" href="#default_priority"></i>
             </td>
        </tr>
        <tr>
            <td width="180">Tópico de Ajuda Padrão</td>
            <td>
                <select name="default_help_topic">
                    <option value="0">&mdash; Nenhum &mdash;</option><?php
                    $topics = Topic::getHelpTopics(false, Topic::DISPLAY_DISABLED);
                    while (list($id,$topic) = each($topics)) { ?>
                        <option value="<?php echo $id; ?>"<?php echo ($config['default_help_topic']==$id)?'selected':''; ?>><?php echo $topic; ?></option>
                    <?php
                    } ?>
                </select><br/>
                <span class="error"><?php echo $errors['default_help_topic']; ?></span>
            </td>
        </tr>
        <tr>
            <td>Máximo de Tickets <b>Abertos</b>:</td>
            <td>
                <input type="text" name="max_open_tickets" size=4 value="<?php echo $config['max_open_tickets']; ?>">
                por e-mail/usuário <i class="help-tip icon-question-sign" href="#maximum_open_tickets"></i>
            </td>
        </tr>
        <tr>
            <td>Agente de Anulação de Conflito:</td>
            <td>
                <input type="text" name="autolock_minutes" size=4 value="<?php echo $config['autolock_minutes']; ?>">
                <font class="error"><?php echo $errors['autolock_minutes']; ?></font>&nbsp;minutos&nbsp;<i class="help-tip icon-question-sign" href="#agent_collision_avoidance"></i>
            </td>
        </tr>
        <tr>
            <td>Verificação Humana:</td>
            <td>
                <input type="checkbox" name="enable_captcha" <?php echo $config['enable_captcha']?'checked="checked"':''; ?>>
                Ativar CAPTCHA em novos tickets web &nbsp;<font class="error">&nbsp;<?php echo $errors['enable_captcha']; ?></font>&nbsp;<i class="help-tip icon-question-sign" href="#human_verification"></i>
            </td>
        </tr>
        <tr>
            <td>Reivindicação na Resposta:</td>
            <td>
                <input type="checkbox" name="auto_claim_tickets" <?php echo $config['auto_claim_tickets']?'checked="checked"':''; ?>>
                Ativado&nbsp;<i class="help-tip icon-question-sign" href="#claim_tickets"></i>
            </td>
        </tr>
        <tr>
            <td>Tickets Atribuídos:</td>
            <td>
                <input type="checkbox" name="show_assigned_tickets" <?php
                echo !$config['show_assigned_tickets']?'checked="checked"':''; ?>>
                Excluir tickets atribuídos na fila de entrada <i class="help-tip icon-question-sign" href="#assigned_tickets"></i>
            </td>
        </tr>
        <tr>
            <td>Tickets Respondidos:</td>
            <td>
                <input type="checkbox" name="show_answered_tickets" <?php
                echo !$config['show_answered_tickets']?'checked="checked"':''; ?>>
                Excluir tickets respondidos na fila de entrada <i class="help-tip icon-question-sign" href="#answered_tickets"></i>
            </td>
        </tr>
        <tr>
            <td>Ocultar Identidade Pessoal:</td>
            <td>
                <input type="checkbox" name="hide_staff_name" <?php echo $config['hide_staff_name']?'checked="checked"':''; ?>>
                Ocultar nome dos funcionários nas respostas <i class="help-tip icon-question-sign" href="#staff_identity_masking"></i>
            </td>
        </tr>
        <tr>
            <td>Habilitar HTML de Segmento:</td>
            <td>
                <input type="checkbox" name="enable_html_thread" <?php
                echo $config['enable_html_thread']?'checked="checked"':''; ?>>
                Ativar Editor de texto em tickets e auto-resposta de e-mails <i class="help-tip icon-question-sign" href="#enable_html_ticket_thread"></i>
            </td>
        </tr>
        <tr>
            <td>Permitir atualizações de Cliente::</td>
            <td>
                <input type="checkbox" name="allow_client_updates" <?php
                echo $config['allow_client_updates']?'checked="checked"':''; ?>>
                Permitir que clientes atualizem detalhes do ticket através do portal web
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><b>Anexos</b>:  Tamanho e Max. de Uploads, aplica-se principalmente a tickets via web.</em>
            </th>
        </tr>
        <tr>
            <td width="180">Permitir Anexos:</td>
            <td>
              <input type="checkbox" name="allow_attachments" <?php echo
              $config['allow_attachments']?'checked="checked"':''; ?>> <b>Permitir Anexos</b>
                &nbsp; <em>(Configuração Global)</em>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['allow_attachments']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180">Anexos Emailed/API:</td>
            <td>
                <input type="checkbox" name="allow_email_attachments" <?php echo $config['allow_email_attachments']?'checked="checked"':''; ?>> Aceitar anexos emailed/API .
                    &nbsp;<font class="error">&nbsp;<?php echo $errors['allow_email_attachments']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180">Anexos Online/Web:</td>
            <td>
                <input type="checkbox" name="allow_online_attachments" <?php echo $config['allow_online_attachments']?'checked="checked"':''; ?> >
                    Permitir Upload via web &nbsp;&nbsp;&nbsp;&nbsp;
                <input type="checkbox" name="allow_online_attachments_onlogin" <?php echo $config['allow_online_attachments_onlogin'] ?'checked="checked"':''; ?> >
                    Limitar somente a usuários autenticados. <em>(O usuário deve estar logado para fazer upload de arquivos)</em>
                    <font class="error">&nbsp;<?php echo $errors['allow_online_attachments']; ?></font>
            </td>
        </tr>
        <tr>
            <td>Max. Upload por Usuário:</td>
            <td>
                <select name="max_user_file_uploads">
                    <?php
                    for($i = 1; $i <=$maxfileuploads; $i++) {
                        ?>
                        <option <?php echo $config['max_user_file_uploads']==$i?'selected="selected"':''; ?> value="<?php echo $i; ?>">
                            <?php echo $i; ?>&nbsp;<?php echo ($i>1)?'files':'file'; ?></option>
                        <?php
                    } ?>
                </select>
                <em>(Número de arquivos que o usuário tem permissão de enviar simultaneamente)</em>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['max_user_file_uploads']; ?></font>
            </td>
        </tr>
        <tr>
            <td>Max. Upload por Funcionário:</td>
            <td>
                <select name="max_staff_file_uploads">
                    <?php
                    for($i = 1; $i <=$maxfileuploads; $i++) {
                        ?>
                        <option <?php echo $config['max_staff_file_uploads']==$i?'selected="selected"':''; ?> value="<?php echo $i; ?>">
                            <?php echo $i; ?>&nbsp;<?php echo ($i>1)?'files':'file'; ?></option>
                        <?php
                    } ?>
                </select>
                <em>(Número de arquivos que a equipe tem permissão de enviar simultaneamente)</em>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['max_staff_file_uploads']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180">Tamanho Máximo do Arquivo:</td>
            <td>
                <select name="max_file_size">
                    <option value="262144">&mdash; Small &mdash;</option>
                    <?php $next = 512 << 10;
                    $max = strtoupper(ini_get('upload_max_filesize'));
                    $limit = (int) $max;
                    if (!$limit) $limit = 2 << 20; # 2M default value
                    elseif (strpos($max, 'K')) $limit <<= 10;
                    elseif (strpos($max, 'M')) $limit <<= 20;
                    elseif (strpos($max, 'G')) $limit <<= 30;
                    while ($next <= $limit) {
                        // Select the closest, larger value (in case the
                        // current value is between two)
                        $diff = $next - $config['max_file_size'];
                        $selected = ($diff >= 0 && $diff < $next / 2)
                            ? 'selected="selected"' : ''; ?>
                        <option value="<?php echo $next; ?>" <?php echo $selected;
                             ?>><?php echo Format::file_size($next);
                             ?></option><?php
                        $next *= 2;
                    }
                    // Add extra option if top-limit in php.ini doesn't fall
                    // at a power of two
                    if ($next < $limit * 2) {
                        $selected = ($limit == $config['max_file_size'])
                            ? 'selected="selected"' : ''; ?>
                        <option value="<?php echo $limit; ?>" <?php echo $selected;
                             ?>><?php echo Format::file_size($limit);
                             ?></option><?php
                    }
                    ?>
                </select>
                <font class="error">&nbsp;<?php echo $errors['max_file_size']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="180">Arquivos de Resposta:</td>
            <td>
                <input type="checkbox" name="email_attachments" <?php echo $config['email_attachments']?'checked="checked"':''; ?> >Anexos de e-mail para o usuário <i class="help-tip icon-question-sign" href="#ticket_response_files"></i>
            </td>
        </tr>
        <?php if (($bks = FileStorageBackend::allRegistered())
                && count($bks) > 1) { ?>
        <tr>
            <td width="180">Store Attachments:</td>
            <td><select name="default_storage_bk"><?php
                foreach ($bks as $char=>$class) {
                    $selected = $config['default_storage_bk'] == $char
                        ? 'selected="selected"' : '';
                    ?><option <?php echo $selected; ?> value="<?php echo $char; ?>"
                    ><?php echo $class::$desc; ?></option><?php
                } ?>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <th colspan="2">
                <em><strong>Tipos de arquivos aceitos:</strong>: Limitar o tipo de arquivos os usuários podem enviar.
                <font class="error">&nbsp;<?php echo $errors['allowed_filetypes']; ?></font></em>
            </th>
        </tr>
        <tr>
            <td colspan="2">
                <em>Digite extensões de arquivos permitidas separadas por uma vírgula. por exemplo doc, pdf. Para aceitar todos os arquivos entrar com asterisco <b><i>.*</i></b>&nbsp;i.e dotStar (NÃO Recomendado).</em><br>
                <textarea name="allowed_filetypes" cols="21" rows="4" style="width: 65%;" wrap="hard" ><?php echo $config['allowed_filetypes']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:250px;">
    <input class="button" type="submit" name="submit" value="Salvar">
    <input class="button" type="reset" name="reset" value="Redefinir">
</p>
</form>


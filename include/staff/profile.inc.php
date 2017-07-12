<?php
if(!defined('OSTSTAFFINC') || !$staff || !$thisstaff) die('Access Denied');

$info=$staff->getInfo();
$info['signature'] = Format::viewableImages($info['signature']);
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
$info['id']=$staff->getId();
?>
<form action="profile.php" method="post" id="save" autocomplete="off">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="update">
 <input type="hidden" name="id" value="<?php echo $info['id']; ?>">
 <h2>Meu Perfil</h2>
 <table class="form_table" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
        <tr>
            <th colspan="2">
                <h4>Informação da Conta</h4>
                <em>Informação de Contato.</em>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td width="180" class="required">
                Nome de usuário:
            </td>
            <td><b><?php echo $staff->getUserName(); ?></b>&nbsp;<i class="help-tip icon-question-sign" href="#username"></i></td>
        </tr>

        <tr>
            <td width="180" class="required">
                Primeiro Nome:
            </td>
            <td>
                <input type="text" size="34" name="firstname" value="<?php echo $info['firstname']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['firstname']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Último Nome:
            </td>
            <td>
                <input type="text" size="34" name="lastname" value="<?php echo $info['lastname']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['lastname']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180" class="required">
                Endereço de E-mail:
            </td>
            <td>
                <input type="text" size="34" name="email" value="<?php echo $info['email']; ?>">
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['email']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                Telefone Res:
            </td>
            <td>
                <input type="text" size="22" name="phone" value="<?php echo $info['phone']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone']; ?></span>
                Recado <input type="text" size="5" name="phone_ext" value="<?php echo $info['phone_ext']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['phone_ext']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                Telefone Celular:
            </td>
            <td>
                <input type="text" size="22" name="mobile" value="<?php echo $info['mobile']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['mobile']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Preferências</strong>: Preferências e configurações de perfil.</em>
            </th>
        </tr>
        <tr>
            <td width="180" class="required">
                Fuso horário:
            </td>
            <td>
                <select name="timezone_id" id="timezone_id">
                    <option value="0">&mdash; Selecione o Fuso horário: &mdash;</option>
                    <?php
                    $sql='SELECT id, offset,timezone FROM '.TIMEZONE_TABLE.' ORDER BY id';
                    if(($res=db_query($sql)) && db_num_rows($res)){
                        while(list($id,$offset, $tz)=db_fetch_row($res)){
                            $sel=($info['timezone_id']==$id)?'selected="selected"':'';
                            echo sprintf('<option value="%d" %s>GMT %s - %s</option>',$id,$sel,$offset,$tz);
                        }
                    }
                    ?>
                </select>
                &nbsp;<span class="error">*&nbsp;<?php echo $errors['timezone_id']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                Idioma Preferido:
            </td>
            <td>
        <?php
        $langs = Internationalization::availableLanguages(); ?>
                <select name="lang">
                    <option value="">&mdash; Usar Preferido do Navegador &mdash;</option>
<?php foreach($langs as $l) {
    $selected = ($info['lang'] == $l['code']) ? 'selected="selected"' : ''; ?>
                    <option value="<?php echo $l['code']; ?>" <?php echo $selected;
                        ?>><?php echo $l['desc']; ?></option>
<?php } ?>
                </select>
                <span class="error">&nbsp;<?php echo $errors['lang']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
               Horário de Verão:
            </td>
            <td>
                <input type="checkbox" name="daylight_saving" value="1" <?php echo $info['daylight_saving']?'checked="checked"':''; ?>>
                Observe o horário de verão
                <em>(Hora Atual: <strong><?php echo Format::date($cfg->getDateTimeFormat(),Misc::gmtime(),$info['tz_offset'],$info['daylight_saving']); ?></strong>)</em>
            </td>
        </tr>
        <tr>
            <td width="180">Tamanho máx. de Páginas:</td>
            <td>
                <select name="max_page_size">
                    <option value="0">&mdash; Padrão do Sistema &mdash;</option>
                    <?php
                    $pagelimit=$info['max_page_size']?$info['max_page_size']:$cfg->getPageSize();
                    for ($i = 5; $i <= 50; $i += 5) {
                        $sel=($pagelimit==$i)?'selected="selected"':'';
                         echo sprintf('<option value="%d" %s>mostrar %s registros</option>',$i,$sel,$i);
                    } ?>
                </select> por página.
            </td>
        </tr>
        <tr>
            <td width="180">Taxa de Atualização:</td>
            <td>
                <select name="auto_refresh_rate">
                  <option value="0">&mdash; desativado &mdash;</option>
                  <?php
                  $y=1;
                   for($i=1; $i <=30; $i+=$y) {
                     $sel=($info['auto_refresh_rate']==$i)?'selected="selected"':'';
                     echo sprintf('<option value="%d" %s>Cada %s %s</option>',$i,$sel,$i,($i>1?'mins':'min'));
                     if($i>9)
                        $y=2;
                   } ?>
                </select>
                <em>(Taxa de atualização da página em minutos.)</em>
            </td>
        </tr>
        <tr>
            <td width="180">Assinatura Padrão:</td>
            <td>
                <select name="default_signature_type">
                  <option value="none" selected="selected">&mdash; Nenhum &mdash;</option>
                  <?php
                  $options=array('mine'=>'Minha Assinatura','dept'=>'Assinatura do Dept. (se definida)');
                  foreach($options as $k=>$v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $k,($info['default_signature_type']==$k)?'selected="selected"':'',$v);
                  }
                  ?>
                </select>
                <em>(Você pode mudar a seleção na página de tickets)</em>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['default_signature_type']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">Tamanho Padrão do Papel:</td>
            <td>
                <select name="default_paper_size">
                  <option value="none" selected="selected">&mdash; Nenhum &mdash;</option>
                  <?php
                  $options=array('Carta', 'Oficío', 'A4', 'A3');
                  foreach($options as $v) {
                      echo sprintf('<option value="%s" %s>%s</option>',
                                $v,($info['default_paper_size']==$v)?'selected="selected"':'',$v);
                  }
                  ?>
                </select>
                <em>Tamanho do papel usado na impressão de tickets em PDF</em>
                &nbsp;<span class="error">&nbsp;<?php echo $errors['default_paper_size']; ?></span>
            </td>
        </tr>
        <tr>
            <td>Mostrar Tickets Atribuídos:</td>
            <td>
                <input type="checkbox" name="show_assigned_tickets" <?php echo $info['show_assigned_tickets']?'checked="checked"':''; ?>>
                <em>Mostrar tickets atribuído em fila aberta.&nbsp;<i class="help-tip icon-question-sign" href="#show_assigned_tickets"></i></em>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Senha</strong>: Para redefinir sua senha, forneça sua senha atual e a nova senha abaixo.&nbsp;<span class="error">&nbsp;<?php echo $errors['passwd']; ?></span></em>
            </th>
        </tr>
        <?php if (!isset($_SESSION['_staff']['reset-token'])) { ?>
        <tr>
            <td width="180">
                Senha Atual:
            </td>
            <td>
                <input type="password" size="18" name="cpasswd" value="<?php echo $info['cpasswd']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['cpasswd']; ?></span>
            </td>
        </tr>
        <?php } ?>
        <tr>
            <td width="180">
                Nova Senha:
            </td>
            <td>
                <input type="password" size="18" name="passwd1" value="<?php echo $info['passwd1']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd1']; ?></span>
            </td>
        </tr>
        <tr>
            <td width="180">
                Confirme Nova Senha:
            </td>
            <td>
                <input type="password" size="18" name="passwd2" value="<?php echo $info['passwd2']; ?>">
                &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd2']; ?></span>
            </td>
        </tr>
        <tr>
            <th colspan="2">
                <em><strong>Assinatura</strong>: Opcional usado em envios de e-mails.
                &nbsp;<span class="error">&nbsp;<?php echo $errors['signature']; ?></span>&nbsp;<i class="help-tip icon-question-sign" href="#signature"></i></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea class="richtext no-bar" name="signature" cols="21"
                    rows="5" style="width: 60%;"><?php echo $info['signature']; ?></textarea>
                <br><em>Assinatura é disponibilizado como escolha, em resposta de tickets.</em>
            </td>
        </tr>
    </tbody>
</table>
<p style="padding-left:200px;">
    <input type="submit" name="submit" value="Salvar">
    <input type="reset"  name="reset"  value="Redefinir">
    <input type="button" name="cancel" value="Cancelar" onclick='window.location.href="index.php"'>
</p>
</form>

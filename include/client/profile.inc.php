<?php

?>
<h1>Gerenciar Informações do Perfil</h1>
<p>
Utilize o formulário abaixo para atualizar as informações da sua conta
</p>
<form action="profile.php" method="post">
  <?php csrf_token(); ?>
<table width="800" class="padded">
<?php
foreach ($user->getForms() as $f) {
    $f->render(false);
}
if ($acct = $thisclient->getAccount()) {
    $info=$acct->getInfo();
    $info=Format::htmlchars(($errors && $_POST)?$_POST:$info);
?>
<tr>
    <td colspan="2">
        <div><hr><h3>Preferências</h3>
        </div>
    </td>
</tr>
    <td>Fuso Horário:</td>
    <td>
        <select name="timezone_id" id="timezone_id">
            <option value="0">&mdash; Selecionar Fuso Horário: &mdash;</option>
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
        &nbsp;<span class="error"><?php echo $errors['timezone_id']; ?></span>
    </td>
</tr>
<tr>
    <td width="180">
       Horário de Verão:
    </td>
    <td>
        <input type="checkbox" name="dst" value="1" <?php echo $info['dst']?'checked="checked"':''; ?>>
        Observar o horário de verão
        <em>(Hora Atual: <strong><?php echo Format::date($cfg->getDateTimeFormat(),Misc::gmtime(),$info['tz_offset'],$info['dst']); ?></strong>)</em>
    </td>
</tr>
<?php if ($acct->isPasswdResetEnabled()) { ?>
<tr>
    <td colspan=2">
        <div><hr><h3>Credenciais de Acesso</h3></div>
    </td>
</tr>
<?php if (!isset($_SESSION['_client']['reset-token'])) { ?>
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
        Confirmar Nova Senha:
    </td>
    <td>
        <input type="password" size="18" name="passwd2" value="<?php echo $info['passwd2']; ?>">
        &nbsp;<span class="error">&nbsp;<?php echo $errors['passwd2']; ?></span>
    </td>
</tr>
<?php } ?>
<?php } ?>
</table>
<hr>
<p style="text-align: center;">
    <input type="submit" value="Atualizar"/>
    <input type="reset" value="Redefinir"/>
    <input type="button" value="Cancelar" onclick="javascript:
        window.location.href='index.php';"/>
</p>
</form>


<h2>Instalar um Novo Plugin</h2>
<p>
Para adicionar um plug-in no sistema, faça o download e coloque o plugin na pasta
<code>include/plugins</code>. Uma vez que o plugin estiver na pasta
<code>plugins/</code> ele será mostrado na lista abaixo.
</p>

<form method="post" action="?">
    <?php echo csrf_token(); ?>
    <input type="hidden" name="do" value="install"/>
<table class="list" width="100%"><tbody>
<?php

$installed = $ost->plugins->allInstalled();
foreach ($ost->plugins->allInfos() as $info) {
    // Ignore installed plugins
    if (isset($installed[$info['install_path']]))
        continue;
    ?>
        <tr><td><button type="submit" name="install_path"
            value="<?php echo $info['install_path'];
            ?>">Instalar</button></td>
        <td>
    <div><strong><?php echo $info['name']; ?></strong><br/>
        <div><?php echo $info['description']; ?></div>
        <div class="faded"><em>Versão: <?php echo $info['version']; ?></em></div>
        <div class="faded"><em>Autor: <?php echo $info['author']; ?></em></div>
    </div>
    </td></tr>
    <?php
}
?>
</tbody></table>
</form>

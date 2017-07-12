<?php
/*********************************************************************
    dashboard.php

    Staff's Dashboard - basic stats...etc.

    Peter Rotich <peter@osticket.com>
    Copyright (c)  2006-2013 osTicket
    http://www.osticket.com

    Released under the GNU General Public License WITHOUT ANY WARRANTY.
    See LICENSE.TXT for details.

    vim: expandtab sw=4 ts=4 sts=4:
**********************************************************************/
require('staff.inc.php');
$nav->setTabActive('dashboard');
$ost->addExtraHeader('<meta name="tip-namespace" content="dashboard.dashboard" />',
    "$('#content').data('tipNamespace', 'dashboard.dashboard');");
require(STAFFINC_DIR.'header.inc.php');
?>

<script type="text/javascript" src="js/raphael-min.js?bba9ccc"></script>
<script type="text/javascript" src="js/g.raphael.js?bba9ccc"></script>
<script type="text/javascript" src="js/g.line-min.js?bba9ccc"></script>
<script type="text/javascript" src="js/g.dot-min.js?bba9ccc"></script>
<script type="text/javascript" src="js/bootstrap-tab.js?bba9ccc"></script>
<script type="text/javascript" src="js/dashboard.inc.js?bba9ccc"></script>

<link rel="stylesheet" type="text/css" href="css/bootstrap.css?bba9ccc"/>
<link rel="stylesheet" type="text/css" href="css/dashboard.css?bba9ccc"/>

<h2>Atividade dos Tickets&nbsp;<i class="help-tip icon-question-sign" href="#ticket_activity"></i></h2>
<p>Selecione o dia de início e período para o gráfico de atividade do sistema</p>
<form class="well form-inline" id="timeframe-form">
    <label>
        <i class="help-tip icon-question-sign" href="#report_timeframe"></i>&nbsp;&nbsp;Intervalo de tempo:
        <input type="text" class="dp input-medium search-query"
            name="start" placeholder="Selecione"/>
    </label>
    <label>
        Período:
        <select name="period">
            <option value="now" selected="selected">Selecione</option>
            <option value="+7 days">Uma Semana</option>
            <option value="+14 days">Duas Semanas</option>
            <option value="+1 month">Um Mês</option>
            <option value="+3 months">Três Meses</option>
        </select>
    </label>
    <button class="btn" type="submit">Atualizar</button>
</form>

<!-- Create a graph and fetch some data to create pretty dashboard -->
<div style="position:relative">
    <div id="line-chart-here" style="height:300px"></div>
    <div style="position:absolute;right:0;top:0" id="line-chart-legend"></div>
</div>

<hr/>
<h2>Estatísticas&nbsp;<i class="help-tip icon-question-sign" href="#statistics"></i></h2>
<p>Estatísticas de tickets organizados por departamento, tópico de ajuda e equipe.</p>
<ul class="nav nav-tabs" id="tabular-navigation"></ul>

<div id="table-here"></div>

<?php
include(STAFFINC_DIR.'footer.inc.php');
?>

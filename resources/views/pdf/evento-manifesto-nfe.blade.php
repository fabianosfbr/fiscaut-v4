<html>

<head>
    <title>Evento</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <style type="text/css" media="all">
        p.txAvisoRed tCenter {
            width: 100%;
            background-color: darksalmon;
            color: darkred;
            text-align: center;
            padding: 4px;

        }

        .GeralXslt {
            font-size: 11px;
            font-family: Arial, Helvetica, sans-serif;
            background-color: transparent;
            padding: 0px;
        }

        .XSLTNFeResumida {
            font-size: 11px;
            font-family: Arial, Helvetica, sans-serif;
            background-color: transparent;
            padding: 15px 0px 0px 0px;
        }


        .XSLTNFeResumida p.txAvisoRed {
            width: 100%;
            background-color: #f6cca3;
            color: darkred;
            text-align: center;
            padding: 4px;
            font-weight: bold;
            font-size: 13px;
        }

        .GeralXslt table,
        .XSLTNFeResumida table {
            width: 100%;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
            /*padding-top: 5px;*/
        }

        .GeralXslt a:link,
        .XSLTNFeResumida a:link {
            font-size: 11px;
            color: #d2965c;
            /*text-decoration: underline;*/
        }


        .GeralXslt fieldset,
        .XSLTNFeResumida fieldset {
            border: solid 0px transparent;
            margin-top: 0px;
            padding: 0px;
            /*clear: both;*/
            background-color: transparent;
        }



        .GeralXslt h5.toggle,
        .XSLTNFeResumida h5.toggle {
            padding-left: 20px;
            background-position: 0px 0px;
        }



        .GeralXslt h5.opened,
        .XSLTNFeResumida h5.opened {
            padding-left: 20px;
            background-position: 0px 0px;
        }

        .titulo-aba,
        fieldset div .titulo-aba {
            text-align: center !important;
            border: 0px solid transparent;
            padding: 15px 2px 10px 2px;
            margin: 0px 0px 7px 0px;
            font-family: Trebuchet MS, Arial, Verdana, Helvetica, sans-serif;
        }

        .GeralXslt fieldset legend,
        .XSLTNFeResumida .THead_NFe {
            text-align: left;
            border: none;
            font-size: 14px;
            height: 15px;
            font-weight: bold;
            width: 98%;
            display: block;
            color: #b27235;
            border: none;
            font-family: Trebuchet MS, Arial, Verdana, Helvetica, sans-serif;
        }

        fieldset legend {
            padding: 15px 2px 10px 2px;
        }

        .fieldset-internal {
            color: #302E2E;
            /*font-weight: bolder;*/
            background-color: #FFF;
            border: solid 1px #c7a460;
        }



        .box {
            width: 100%;
            border: 1px solid #c7a460;
            background-color: #f4edd5;
            margin: 0px 0px 0px 0px;
            padding: 0px 0px 0px 0px;
        }

        .rowTP01 {
            width: 100%;
            border: 1px solid #c7a460;
            background-color: #f4edd5;
            margin: 10px 0px 10px 0px;
            padding: 0px 0px 0px 3px;
        }

        .tabNFe {
            width: 100%;
            border: 1px solid #c7a460;
            background-color: #f4edd5;
            margin: 10px 0px 10px 0px;
            padding: 3px;
        }

        .rowTP01 div {
            display: inline-block;
            vertical-align: top;
        }

        .box label,
        .rowTP01 div label {
            display: block;
            /*min-height: 19px;
    height: auto;*/
            text-transform: none;
            /*padding: 4px 0px 0px 6px;*/
            font-family: Trebuchet MS, Arial, Verdana, Helvetica, sans-serif;
            font-size: 11px;
            min-height: 15px;
            font-weight: normal;
            color: #6f5e39;
            text-align: left;
            padding: 2px;
        }

        .tabNFe th {
            /*min-height: 19px;
    height: auto;*/
            text-transform: none;
            vertical-align: middle;
            /*padding: 4px 0px 0px 6px;*/
            font-family: Trebuchet MS, Arial, Verdana, Helvetica, sans-serif;
            font-size: 11px;
            min-height: 15px;
            font-weight: normal;
            color: #6f5e39;
            text-align: left;
            padding: 2px;
        }

        .box span {
            height: auto !important;
            height: 15px;
            min-height: 15px;
            display: block;
            background-position: center;
            border: solid 1px #d6c39e;
            background-color: #fbfbf5;
            padding: 2px 2px 2px 6px;
        }



        .tabNFe tr {

            padding: 3px;
        }

        .tabNFe td {
            height: auto !important;
            height: 15px;
            min-height: 15px;
            background-position: center;
            border: solid 1px #d6c39e;
            background-color: #fbfbf5;
            padding: 2px 2px 2px 3px;
            empty-cells: hide;
        }

        .txtNF_blueB {
            font-weight: bold;
        }

        .rowTP01 p {
            height: auto !important;
            height: 15px;
            min-height: 15px;
            display: block;
            background-position: center;
            border: solid 1px #d6c39e;
            background-color: #fbfbf5;
            padding: 2px 2px 2px 3px;
            margin: 3px 0px 4px 0px;
        }


        .box td,
        .rowTP01 div,
        .tabNFe td {
            vertical-align: top;
        }



        tr.col-10 td,
        .rowTP01 div.wID10 {
            width: 10%;
        }


        .rowTP01 div.wID15 {
            width: 15%;
        }

        tr.col-5 td {
            width: 20%;
        }

        .rowTP01 div.wID20 {
            width: 19%;
        }

        tr.col-4 td {
            width: 25%;
        }

        .rowTP01 div.wID30 {
            width: 29%;
        }

        tr.col-3 td {
            width: 33.333%;
        }



        tr.col-2 td,
        .rowTP01 div.wID50 {
            width: 50%;
        }

        .rowTP01 div.wID50 {
            width: 49%;
        }

        .rowTP01 div.wID60 {
            width: 59%;
        }



        .rowTP01 div.wID65 {
            width: 64%;
        }

        .rowTP01 div.wID70 {
            width: 69%;
        }


        .rowTP01 div.wID100 {
            width: 99%;
        }

        .notprint {
            overflow: hidden;
            display: none;
        }

        i.eng {
            font-size: 9px;
        }

        #Versao {
            font-size: 10px;
            text-align: right;
        }

        #print-header {
            display: block;
            height: 100px;
        }

        #print-header-logo {
            display: block;
            float: left;
            width: 96px;
            height: 96px;
            background: no-repeat scroll;
        }

        #print-header-titulo {
            display: block;
            float: left;
            padding-top: 40px;
            font-family: Verdana, Arial, Helvetica;
            font-size: 18px;
        }

        #print-header-titulo-param {
            display: block;
            text-align: center;
            font-family: Verdana, Arial, Helvetica;
            font-size: 22px;
            font-weight: bolder;
        }

        #print-nfe-info {
            font-size: 11px;
            font-family: Verdana, Arial, Helvetica;
        }

        #footer {
            font-family: Verdana, Arial, Helvetica;
            font-size: 11px;
        }

        div.tbl-aut-uso {
            background-color: #eee;
            border-radius: 5px;
            padding: 2px 2px 2px 2px;
        }

        div.tbl-aut-uso span.subtitle {
            font-weight: bolder;
            background-color: #eee;
            font-size: 11px;
        }

        div.tbl-aut-uso table {
            background-color: White;
            padding-top: 5px;
            margin-bottom: 5px;
            border-radius: 5px;
        }

        div.tbl-aut-uso table td {
            font-family: Verdana, Arial, Helvetica;
            font-size: 11px;
        }

        div.tbl-aut-uso table td.itm {
            padding-left: 20px;
            width: 25%;
        }

        div.tbl-aut-uso table td.val {
            padding-left: 20px;
        }



        span#tipo-ambiente {
            border: 1px solid #880;
            margin-left: 5px;
            margin-top: 5px;
            font-weight: bolder;
            background-color: #ffc;
            height: 20px;
        }

        span#tipo-ambiente img {
            margin-top: 2px;
        }

        .toggle {
            background: url('../imagens/ico_mais.gif') no-repeat 8px 0px;
            /* background-color: #ece8df;*/
            cursor: pointer;
            background-position: 6px 6px;
            padding-left: 20px;
            text-indent: 0px;
            border: solid 1px rgb(236, 236, 236);
        }

        /*.tabNFe {
    background-color: #ece8df;
    text-indent: 0px;
    border: solid 1px rgb(236, 236, 236);
    margin: 10px 0px 10px 0px;
    padding: 0px 0px 0px 3px;
}*/



        .toggle span {
            height: auto !important;
            min-height: 15px;
            display: block;
            background-position: center;
            border: solid 0px transparent;
            background-color: transparent;
            padding: 2px 2px 2px 6px;
        }


        .toggle td {
            border: solid 1px #d6c39e;
            background-color: #fff;
            /* #fbfbf5;*/
        }

        .togglable .box {
            border: solid 0px transparent;
        }

        .opened {
            background: url('../imagens/ico_menos.gif') no-repeat 8px 0px;
            background-position: 6px 6px;
        }

        fieldset legend.toggle {
            padding: 7px 20px 15px 20px;
        }

        /* Visualização Produtos e Serviços */

        td.fixo-prod-serv-numero {
            width: 4%;
            padding-left: 3px;
        }


        table.toggle tr.highlighted td {
            background-color: #f9f5ea;
        }


        td.fixo-prod-serv-descricao {
            width: 57%;
            font-size: 11px;
        }

        td.fixo-prod-serv-qtd {
            width: 11%;
        }



        td.fixo-prod-serv-uc {
            width: 15%;
        }

        td.fixo-prod-serv-vb {
            width: 13%;
        }

        /*  Visualização Internet  */
        #botoes_nft {
            list-style-type: none;
            list-style-image: none;
            margin: 0;
            padding: 0;
        }

        #botoes_nft li,
        .nftselected {
            cursor: pointer;
            float: left;
            display: inline;
            clear: none;
            font-family: Arial, Verdana, Helvetica, sans-serif;
            font-size: 11px;
            color: #6d6e71;
            margin: 0px 1px 0px 0px;
            /*padding: 5px 2px 0px 2px;*/
            border-left: 1px solid #E5E3DA;
            border-right: 1px solid #E5E3DA;
            border-top: 1px solid #E5E3DA;
            /*border-bottom: 1px solid #f7e2c2;*/
            border-radius: 3px 3px 0px 0px;
            -moz-border-radius: 3px 3px 0px 0px;
            -webkit-border-radius: 3px 3px 0px 0px;
            background-color: #f8f8f8;
            cursor: pointer;
        }

        #botoes_nft li b {
            display: block;
            font-size: 12px !important;
            font-family: tahoma, arial, helvetica;
            padding: 5px 2px 1px 2px;
            font-weight: 200;
        }

        #botoes_nft li.nftselected b {
            margin: -2px -1px -2px -1px;
            background-color: #fcfaf6;
            border-left: 1px solid #f7e2c2;
            border-top: 3px solid #f7e2c2;
            border-right: 1px solid #f7e2c2;
            border-bottom: 1px solid #fcfaf6;
            color: #811c0d;
            font-weight: bolder;
            padding: 5px 2px 0px 2px;
            border-radius: 3px 3px 0px 0px;
            -moz-border-radius: 3px 3px 0px 0px;
            -webkit-border-radius: 3px 3px 0px 0px;
        }


        .nft {
            display: none;
        }

        .bigger {
            width: 200px;
        }

        .aba_container {
            padding: 15px 2px 10px 2px;
            margin: 0px 0px 7px 0px;
            background-color: #fcfaf6;
            border: solid 1px #f7e2c2;
            margin-top: 21px;
        }

        footer {
            position: fixed;
            bottom: -50px;
            left: 0px;
            right: 0px;
            height: 50px;

            /** Extra personal styles **/
            color: black;
            text-align: left;
            line-height: 35px;
        }
    </style>
</head>

<body class="GeralXslt">
    <fieldset>
        <div class="titulo-aba" style="padding: 15px 2px 10px 2px; color: #b27235; font-weight: bold; font-size: 14px; height: 15px;">Operação não Realizada</div>
        <table class="box">
            <tbody>
                <tr>
                    <td class="col-2" colspan="3"><label>Orgão Recepção do Evento</label><span>91 -
                            AMBIENTE NACIONAL</span></td>
                    <td class="col-4" colspan="3"><label>Ambiente</label><span>1 - Produção</span></td>
                    <td class="col-4" colspan="3"><label>Versão</label><span>1.00</span></td>
                </tr>
            </tbody>
        </table><br>
        <table class="box">
            <tbody>
                <tr class="col-2">
                    <td colspan="2"><label>Chave de Acesso</label><span>{{$event->chave}}</span></td>
                    <td colspan="2"><label>Id do Evento</label><span>ID2102403523055250250700669255001000201664191662172001</span></td>
                </tr>
                <tr class="col-2">
                    <td colspan="2"><label>Autor Evento (CNPJ / CPF)</label><span>{{formatar_cnpj_cpf($xml->retEvento->infEvento->CNPJDest)}}</span></td>
                    <td colspan="2"><label>Data Evento</label><span>{{ dateTimeFormat(str_replace('T', ' ', $xml->retEvento->infEvento->dhRegEvento), 'd/m/Y') }} às {{ dateTimeFormat(str_replace('T', ' ', $xml->retEvento->infEvento->dhRegEvento), 'H:s:i') }}</span></td>
                </tr>
            </tbody>
        </table><br>
        <table class="box">
            <tbody>
                <tr class="col-2">
                    <td colspan="2"><label>Tipo de Evento</label><span>{{$xml->retEvento->infEvento->tpEvento}} - {{tipoEventoManifesto($xml->retEvento->infEvento->tpEvento)}}
                        </span></td>
                    <td colspan="2"><label>Sequencial do Evento</label><span>{{$xml->retEvento->infEvento->nSeqEvento}}</span></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <fieldset>
        <div style="padding: 15px 2px 10px 2px; color: #b27235; font-weight: bold; font-size: 14px; height: 15px;">Detalhes do Evento</div>
        <table class="box">
            <tbody>
                <tr class="col-2">
                    <td colspan="2"><label>Descrição do Evento</label><span>{{tipoEventoManifesto($xml->retEvento->infEvento->tpEvento)}}</span></td>
                    <td colspan="2"><label>Versão</label><span>1.00</span></td>
                </tr>
            </tbody>
        </table><br>
        <table class="box">
            <tbody>
                <tr class="col-2">
                    <td colspan="2"><label>Justificativa</label><span>{{$event->justificativa}}</span></td>
                </tr>
            </tbody>
        </table>
    </fieldset>
    <fieldset>
        <div class="titulo-aba" style="padding: 15px 2px 10px 2px; color: #b27235; font-weight: bold; font-size: 14px; height: 15px;">Autorização pela SEFAZ</div>
        <table class="box">
            <tbody>
                <tr>
                    <td class="col-2" colspan="3"><label>Mensagem de Autorização</label><span>{{$xml->retEvento->infEvento->cStat}} - {{$xml->retEvento->infEvento->xMotivo}} </span></td>
                    <td class="col-5" colspan="3"><label>Protocolo</label><span>{{$xml->retEvento->infEvento->nProt}}</span></td>
                    <td class="col-3" colspan="3"><label>Data/Hora Autorização</label><span>{{ dateTimeFormat(str_replace('T', ' ', $xml->retEvento->infEvento->dhRegEvento), 'd/m/Y') }} às {{ dateTimeFormat(str_replace('T', ' ', $xml->retEvento->infEvento->dhRegEvento), 'H:s:i') }}</span></td>
                </tr>
            </tbody>
        </table>
    </fieldset>

    <footer>
        <p style="font-style: italic; font-size: 10px;"> {{$creditos}}</p>
    </footer>
</body>

</html>
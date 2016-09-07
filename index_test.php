<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <!-- Arquivos JavaScript -->
        <script src="include/jquery-2.2.0.min.js"></script> <!-- Importar jQuery -->
        <script src="include/bootstrap.min.js"></script> <!-- Importar bootstrap.js -->
        <script src="include/myJS.js"></script> <!-- Importar meus JavaScripts -->

        <!-- Arquivos CSS -->
        <link rel="stylesheet" href="include/bootstrap.css">
        <link rel="stylesheet" href="include/estilos.css">
    </head>
    <body>
        <div id="principal">
            <div style="float: left;">
                <button class="echo-button" id="conectar">Conectar</button>
                <button class="echo-button" id="desconectar">Desconectar</button>
                <br>
                <br>
                <strong>Mensagem:</strong><br>
                <input class="draw-border" id="enviarMensagem" size="35">
                <br>
                <button class="echo-button" id="btnEnviar">Enviar</button>
            </div>
            <div id="echo-log" style="float: left; margin-left: 20px; padding-left: 20px; width: 350px; border-left: solid 1px #cccccc;"> <strong>Log:</strong>
                <div id="consoleLog"></div>
                <button class="echo-button" id="limparLog" style="position: relative; top: 3px;">Limpar Log</button>
            </div>
        </div>
    </body>
</html>
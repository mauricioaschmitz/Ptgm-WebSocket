var conexao;
var desconexao;
var encaminhar;
var enviarMensagem;
var consoleLogVar;
var limpaLog;

function pageLoad()
{
    conexao = document.getElementById("conectar");
    conexao.onclick = conectarWS;
    
    desconexao = document.getElementById("desconectar");
    desconexao.onclick = desconectarWS;
    
    enviarMensagem = document.getElementById("enviarMensagem");
    
    encaminhar = document.getElementById("btnEnviar");
    encaminhar.onclick = enviar;
    
    consoleLogVar = document.getElementById("consoleLog");
    
    limpaLog = document.getElementById("limparLog");
    limpaLog.onclick = limparLog;
    
}

function conectarWS()
{
    websocket = new WebSocket('ws://localhost:8080');
    websocket.onopen = function (e) {
        onOpen(e);
    };
    websocket.onclose = function (e) {
        onClose(e);
    };
    websocket.onmessage = function (e) {
        onMessage(e);
    };
    websocket.onerror = function (e) {
        onError(e);
    };


    //return algo;
}

function onOpen(e)
{
    consoleLog("Conectado!!!");
}
function onClose(e)
{
    consoleLog("Desconectado!!!");
    
}

function onMessage(e)
{
    consoleLog('<span style="color: blue;">Resposta: ' + e.data+'</span>');
}

function onError(e)
{
    consoleLog('<span style="color: red;">Erro:</span> ' + e.data);
}

function enviar() {
    consoleLog("Enviado: " + enviarMensagem.value);
    websocket.send(enviarMensagem.value);
}

function consoleLog(mensagem)
{
    var pre = document.createElement("p");
    pre.style.wordWrap = "break-word";
    pre.innerHTML = mensagem;
    consoleLogVar.appendChild(pre);

    while (consoleLogVar.childNodes.length > 50)
    {
        consoleLogVar.removeChild(consoleLog.firstChild);
    }

    consoleLogVar.scrollTop = consoleLog.scrollHeight;
}

function limparLog()
{
    while (consoleLogVar.childNodes.length > 0)
    {
        consoleLogVar.removeChild(consoleLogVar.lastChild);
    }
}

function desconectarWS()
{
    websocket.close();
}

window.addEventListener("load", pageLoad, false);
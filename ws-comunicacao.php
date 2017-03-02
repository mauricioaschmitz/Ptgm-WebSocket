<?php

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

require_once("include/bancoFunc.php");
require_once("include/funcoes.php");

class Comunicacao implements MessageComponentInterface {

    protected $clientes;

    public function __construct() {
        $this->clientes = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages later
        $this->clientes->attach($conn);

        echo "Nova conexao do IP {$conn->remoteAddress}\n";
        //echo "\trecebeu ID WebSocket: {$conn->resourceId} \n";
    }

    public function onMessage(ConnectionInterface $from, $msgRecebida) {
        //echo "Mensagem Recebida: {$msgRecebida}\n";
        $msg = examinaMensagem($msgRecebida);
        if ($msg !== false) {
            $comando = $msg['comando'];
            $mensagem = $msg['mensagem'];

            if ($comando === "InsertCaptureDB") {

                $fp = fsockopen('localhost', 80);
                $response = fwrite($fp, $mensagem);
                fclose($fp);
                //echo "Caracteres Enviados para o Capture inserir no banco: " . $response . "\n";
            }
            if ($comando === "MBEDStart") {
                try {
                    $conn = mysqli_connect("localhost", "root", "senha.123", "protegemed");
                    if ($conn) {
                        $modulo = mysqli_query($conn, "SELECT idModulo FROM modulo WHERE ip = '{$from->remoteAddress}'");
                        if ($modulo->num_rows > 0) {
                            $idModulo = mysqli_fetch_assoc($modulo);
                            $date = date("Y-m-d H:i:s");
                            $update = mysqli_query($conn, "UPDATE modulo SET ultimoLiga='{$date}', idWebSocket='{$from->resourceId}' WHERE idModulo = {$idModulo['idModulo']}");
                            if($update != "1"){
                                echo "Erro ao atualizar modulo!\n";
                            }
                            //echo "\nModulo = {$idModulo['idModulo']}\n";
                            $tomada = mysqli_query($conn, "SELECT codTomada, limiteFase, limiteFuga, limiteStandByFase, limiteStandByFuga FROM tomada WHERE codModulo = {$idModulo['idModulo']}");
                            if ($tomada->num_rows > 0) {
                                while ($dados = mysqli_fetch_assoc($tomada)) {
                                    //echo "Tomada = {$dados['codTomada']}\n";
                                    $from->send("setLimit:" . $dados['codTomada'] . ":p:" . $dados['limiteFase']);
                                    $from->send("setLimit:" . $dados['codTomada'] . ":d:" . $dados['limiteFuga']);
                                    $from->send("setStandByLimit:" . $dados['codTomada'] . ":p:" . $dados['limiteStandByFase']);
                                    $from->send("setStandByLimit:" . $dados['codTomada'] . ":d:" . $dados['limiteStandByFuga']);
                                }
                            } else {
                                throw new Exception("Tomada nao encontrada!\n");
                            }
                        } else {
                            throw new Exception("Modulo nao encontrado!\n");
                        }
                        mysqli_close($conn);
                    } else {
                        throw new Exception("Erro: nao foi possivel se conectar ao banco de dados!" . PHP_EOL
                        . "Debugging errno: " . mysqli_connect_errno() . PHP_EOL
                        . "Debugging error: " . mysqli_connect_error() . PHP_EOL);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
            if ($comando === "setLimit") {
                $ip = antes(":", $mensagem);
                $mensagem = depois(":", $mensagem);
                $equipment = antes(":", $mensagem);
                $tomada = depois(":", $mensagem);
                try {
                    $conn = mysqli_connect("localhost", "root", "senha.123", "protegemed");
                    if ($conn) {
                        $limites = mysqli_query($conn, "SELECT limiteFase, limiteFuga, limiteStandByFase, limiteStandByFuga FROM equipamento WHERE codEquip = '{$equipment}'");
                        if ($limites->num_rows > 0) {
                            while ($dados = mysqli_fetch_assoc($limites)) {
                                foreach ($this->clientes as $client) {
                                    if ($client->remoteAddress === $ip) {
                                        $client->send("setLimit:" . $tomada . ":p:" . $dados['limiteFase']);
                                        $client->send("setLimit:" . $tomada . ":d:" . $dados['limiteFuga']);
                                        $client->send("setStandByLimit:" . $tomada . ":p:" . $dados['limiteStandByFase']);
                                        $client->send("setStandByLimit:" . $tomada . ":d:" . $dados['limiteStandByFuga']);
                                    }
                                }
                            }
                        } else {
                            throw new Exception("Limites nao encontrados!\n");
                        }
                        mysqli_close($conn);
                    } else {
                        throw new Exception("Erro: nao foi possivel se conectar ao banco de dados!" . PHP_EOL
                        . "Debugging errno: " . mysqli_connect_errno() . PHP_EOL
                        . "Debugging error: " . mysqli_connect_error() . PHP_EOL);
                    }
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }
            if ($comando === "capture") {
                $ip = antes(":", $mensagem);
                $mensagem = depois(":", $mensagem);
                //echo $ip;
                //echo $mensagem;
                foreach ($this->clientes as $client) {
                    if ($client->remoteAddress === $ip) {
                        $client->send($comando . ":" . $mensagem);
                        echo "Enviou Comando capture para o IP: {$ip}\n";
                    }
                }
            }
            if ($comando === "reset") {
                foreach ($this->clientes as $client) {
                    if ($client->remoteAddress === $mensagem) {
                        echo "Enviou Reset para o IP: {$mensagem}\n";
                        $client->send($comando);
                    }
                }
            }
            if ($comando === "test") {
                foreach ($this->clientes as $client) {
                    if ($client->remoteAddress === $mensagem) {
                        echo "Enviou mensagem de teste para o IP: {$mensagem}\n";
                        $client->send($comando);
                    }
                }
            }
            if ($comando === "checkConnection") {
                //$from->send(checkConnection($mensagem));
                //testar se conexão com o modulo está ativa
            }
            //echo "Comando: {$comando}\n";
            //echo "Mensagem: {$mensagem}\n";
            //echo strlen($mensagem);
            /* if ($comando === "MacAddress") {
              if (strlen($mensagem) === 17) {
              //testa se já está cadastrado e atualiza horario de conexao
              if (leituraBD("*", "microcontrolador", "WHERE macAddress = '$mensagem'")) {
              $atualizar = atualizarBD("microcontrolador", "ativo='1', ip='$from->remoteAddress'", "WHERE macAddress = '$mensagem'");
              echo $atualizar;
              }
              //se não estiver cadastrado, cadastra
              else {
              $dados = array(
              'ip' => $from->remoteAddress,
              'macAddress' => $mensagem,
              'ativo' => 1
              );
              if (inserirBD("microcontrolador", $dados)) {
              echo "Inseriu OK";
              }
              }
              }
              } */
        } else {
            echo "Mensagem com padrão incorreto!!!\n";
        }
    }

    public function onBinaryMessage(ConnectionInterface $from, $msg) {
        // There binary message
        $data = "";
        $from->sendBinary($data);
    }

    public function checkConnection($ip) {
        alert($ip);
        foreach ($this->clientes as $client) {
            if ($client->remoteAddress === $ip) {
                return true;
            }
        }
        return false;
    }

    //quando a conexao e encerrada
    public function onClose(ConnectionInterface $conn) {
        //exclui o cliente
        $this->clientes->detach($conn);

        //atualiza tempo da conexao no banco de dados
        /* $atualizar = atualizarBD("microcontrolador", "ativo='0'", "WHERE ip = '$conn->remoteAddress'");

          if ($atualizar) {
          echo "Atualizou corretamente!\n";
          }
         */
        echo "Conexao com IP {$conn->remoteAddress} desconectada\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "Ocorreu o erro: {$e->getMessage()}\n";

        $conn->close();
    }

}

//funções para tratar strings
//antes retorna string antes do padrão informado
//depois retorna string depois do padrão informado
//entre retorna string entre dois padrões informados
function antes($first, $second) {
    return substr($second, 0, strpos($second, $first));
}

function depois($first, $second) {
    if (!is_bool(strpos($second, $first))) {
        return substr($second, strpos($second, $first) + strlen($first));
    }
}

function entre($first, $second, $three) {
    return antes($second, depois($first, $three));
}

//funcao para examinar a mensagem
//retorna um array contendo o comando e a mensagem em caso positivo
//retorna false em erro de padrao
function examinaMensagem($mensagem) {
    $ok = 0;
    //teste inicio da mensagem se contém o formato correto
    if (substr($mensagem, 0, 2) === "#*") {
        $msg = substr($mensagem, 2);
        //echo "Mensagem Sem o Inicio: {$msg}\n";
        //testa fim do comando se está correto
        $comando = antes("*#", $msg);
        if ($comando !== "") {
            //echo "Comando: {$comando}\n";
            //pega a mensagem do comando
            $msgComando = depois("*#", $msg);
            //echo "Mensagem de comando: {$msgComando}\n";
        } else {
            $ok = 2;
        }
    } else {
        $ok = 1;
    }
    //mensagem com padrão incorreto
    if ($ok !== 0) {
        //echo "Formato da Mensagem Incorreto.\n\n";
        if ($ok === 1) {
            //echo "Padrão de inicio do comando incorreto!\n";
        }
        if ($ok === 2) {
            //echo "Padrão de fim do comando incorreto!\n";
        }
        //echo "Padrao: #*Comando*#Conteudo\n\n";

        return false;
    } else {
        return array('comando' => $comando, 'mensagem' => $msgComando);
    }
}

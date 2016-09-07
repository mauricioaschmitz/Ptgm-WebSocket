<?php

/* * ***************************************
  FUNÇÃO DE CADASTRO NO BANCO DE DADOS
 * 
 * Exemplo:
 * $dados = array(
        'campo1' => $variavel,
        'campo2' => "dados"
        );
   $variavel = inserirBD("tabela", $dados);
 * *************************************** */

function inserirBD($tabela, array $dados) {
    $conexao = mysqli_connect('127.0.0.1', 'root', 'senha.123', 'protegemed') or die('Erro ao conectar: ' . mysqli_error($conexao));
    mysqli_query($conexao, "SET NAMES 'utf8'");
    $campos = implode(", ", array_keys($dados));
    $valores = "'" . implode("', '", array_values($dados)) . "'";
    $valores = str_replace("''", "NULL", $valores);
    $inserir = "INSERT INTO $tabela ($campos) VALUES ($valores)";
    $query = mysqli_query($conexao, $inserir) or die('Erro ao cadastrar em ' . $tabela . ' ' . mysqli_error($conexao));
    mysqli_close($conexao);
    
    return $query;
}

/* * ***************************************
  FUNÇÃO DE LEITURA NO BANCO DE DADOS
 * *************************************** */

function leituraBD($campos, $tabela, $condicao) {
    $conexao = mysqli_connect('127.0.0.1', 'root', 'senha.123', 'protegemed') or die('Erro ao conectar: ' . mysqli_error($conexao));
    mysqli_query($conexao, "SET NAMES 'utf8'");
    $query = "SELECT " . $campos . " FROM {$tabela} {$condicao}";
    $dados = mysqli_query($conexao, $query) or die('Erro ao ler em ' . $tabela . ' ' . mysqli_error($conexao));
    if (mysqli_num_rows($dados) <= 0) {
        mysqli_close($conexao);
        return false;
    }
    $resultado = '';
    $numCampos = mysqli_num_fields($dados);
    $numLinhas = mysqli_num_rows($dados);

    for ($y = 0; $y < $numCampos; $y++) {
        $nomes[$y] = mysqli_fetch_field($dados)->name;
    }
    for ($x = 0; $x < $numLinhas; $x++) {
        $res = mysqli_fetch_array($dados);
        for ($i = 0; $i < $numCampos; $i++) {
            $resultado[$x][$nomes[$i]] = $res[$nomes[$i]];
        }
    }
    mysqli_close($conexao);
    
    return $resultado;
}

/* * ***************************************
  FUNÇÃO DE EDIÇÃO DE DADOS DO BANCO DE DADOS
 * 
 * Exemplo
 * $atualizar = atualizarBD("tabela", "vagas='$novasVagas'", "WHERE id = '$id'");
 * 
 * *************************************** */

function atualizarBD($tabela, $dados, $condicao) {
    $conexao = mysqli_connect('127.0.0.1', 'root', 'senha.123', 'protegemed') or die('Erro ao conectar: ' . mysqli_error($conexao));
    mysqli_query($conexao, "SET NAMES 'utf8'");
    $atualizar = "UPDATE {$tabela} SET {$dados} {$condicao}";
    echo $atualizar;
    $query = mysqli_query($conexao, $atualizar) or die('Erro ao atualizar em ' . $tabela . ' ' . mysqli_error($conexao));
    mysqli_close($conexao);
    
    return $query;
}

/* * ***************************************
  FUNÇÃO DELETAR DADOS DO BANCO DE DADOS
 * *************************************** */

function deletarBD($tabela, $condicao) {
    $conexao = mysqli_connect('127.0.0.1', 'root', 'senha.123', 'protegemed') or die('Erro ao conectar: ' . mysqli_error($conexao));
    mysqli_query($conexao, "SET NAMES 'utf8'");
    $deletar = "DELETE FROM {$tabela} {$condicao}";
    
    $query = mysqli_query($conexao, $deletar) or die('Erro ao atualizar em ' . $tabela . ' ' . mysqli_error($conexao));
    mysqli_close($conexao);
    
    return $query;
}

/* * ***************************************
  FUNÇÃO: FORMATA DATA PARA BANCO DE DADOS
 * *************************************** */

function formataDataBD($recebeData) {
    $defineData = explode('/', $recebeData);
    $dia = $defineData[0];
    $mes = $defineData[1];
    $ano = $defineData[2];
    $resultado = $ano . '-' . $mes . '-' . $dia;

    return $resultado;
}

/* * ***************************************
  FUNÇÃO: FORMATA DATA DO BANCO PARA HTML
 * *************************************** */

function formataDataHTML($recebeData) {
    $defineData = explode('-', $recebeData);
    $dia = $defineData[2];
    $mes = $defineData[1];
    $ano = $defineData[0];
    $resultado = $dia . '/' . $mes . '/' . $ano;

    return $resultado;
}
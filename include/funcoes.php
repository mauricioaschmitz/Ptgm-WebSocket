<?php

/**
 * 2011
 * Desenvolvido por: Igor Spanholi
 * Email: igor@orange.net.br
 * Projeto de conclus�o de curso
 * UPF - Ci�ncia da Computa��o
 */
// Funcao que transforma os valores float 32 para valores hexadecimais (IEEE-754)
function ieee_float($f) {
    $f = (float) $f;
    $b = pack("f", $f);
    //$hexa = array();
    for ($i = 0; $i < strlen($b); $i++) {
        $c = ord($b{$i});
        $hexa[] = sprintf("%02X", $c);
    }
    $hex = '';
    for ($i = strlen($hexa); $i >= 0; $i--) {
        $hex.=$hexa[$i];
    }

    return $hex;
}

// Funcao que transforma os valores hexadecimais para valores float 32 (IEEE-754)
function hex2float32($hex) {
    // Gera sequencia bin�ria. OBS: concatena '1' no in�cio para n�o perder ZEROS, mas logo ap�s retira-o com SUBSTR
    $binario = substr(base_convert('1' . $hex, 16, 2), 1);

    $sinal = substr($binario, 0, 1); // 1 bit 0 ou 1
    $exp = substr($binario, 1, 8); // 8 bits para o expoente
    $valor = substr($binario, 9); // Inicia do bit 9 para a mantissa

    $fracional = 0;
    for ($i = 0; $i < strlen($valor); $i++)
    // Aplica a formula:  2**-1  +  2**-2  +  2**-3  +  ...  +  2**-n  ::  IEEE-754
        $fracional += pow(2, ($i + 1) * -1) * substr($valor, $i, 1);

    $mant = 1;
    if (bindec($exp) == 0)
        $mant = 0;
    // Aplica a formula:  -1**sign  *  1 + fractional  *  2**exp-127  ::  IEEE-754
    //FIXME: Eliminar 1 + do $fracional quando o expoente for -127
    return pow(-1, $sinal) * ( $mant + $fracional ) * pow(2, bindec($exp) - 127);
}

// Funcao para insercao dos valores no banco de dados
if (!function_exists("GetSQLValueString")) {

    function GetSQLValueString($theValue, $theType, $theDefinedValue = "", $theNotDefinedValue = "") {
        if (PHP_VERSION < 6) {
            $theValue = get_magic_quotes_gpc() ? stripslashes($theValue) : $theValue;
        }

        $theValue = function_exists("mysql_real_escape_string") ? mysql_real_escape_string($theValue) : mysql_escape_string($theValue);

        switch ($theType) {
            case "text":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;
            case "long":
            case "int":
                $theValue = ($theValue != "") ? intval($theValue) : "NULL";
                break;
            case "double":
                $theValue = ($theValue != "") ? doubleval($theValue) : "NULL";
                break;
            case "date":
                $theValue = ($theValue != "") ? "'" . $theValue . "'" : "NULL";
                break;
            case "defined":
                $theValue = ($theValue != "") ? $theDefinedValue : $theNotDefinedValue;
                break;
        }
        return $theValue;
    }

}

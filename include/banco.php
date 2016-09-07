<?php

/**
	* 2013
	* Desenvolvido por: Marcelo Trindade Rebonatto
	* Email: rebonatto@upf.br
	* Projeto de Doutorado
	* UPF - Ci�ncia da Computa��o / PUCRS - Ci�ncia da Computa��o
*/


// Fun��o que recebe valores e insere ondas/harmonicas no banco de dados
function insereonda($conn, $RFID, $TYPE, $OUTLET, $OFFSET, $GAIN, $RMS, $SINE, $COSINE) {
	// Pesquisa o tipo de evento na tabela tipos_eventos para o evento da mensagem
	mysql_select_db($database_conn, $conn);
	$query_rsTipoEvento = "SELECT * FROM eventos WHERE codEvento = '$EVT'";
	$rsTipoEvento = mysql_query($query_rsTipoEvento, $conn) or die(mysql_error());
	$row_rsTipoEvento = mysql_fetch_assoc($rsTipoEvento);
	$totalRows_rsTipoEvento = mysql_num_rows($rsTipoEvento);

	if($totalRows_rsTipoEvento)
	{
		$IDEVT = $row_rsTipoEvento['codEvento'];
		if($IDEVT==1 || $IDEVT==3 || $IDEVT==6)
			$TIPOEVT = 2;
		else
			$TIPOEVT = 1;
	}
	else
		$error .= 'error: Tipo de evento nao localizado!<br>';

	// Pesquisa pelo RFID o ID do equipamento informado

	mysql_select_db($database_conn, $conn);
	$query_rsEquipamento = "SELECT * FROM equipamento WHERE rfid = $RFID";
	$rsEquipamento = mysql_query($query_rsEquipamento, $conn) or die(mysql_error());
	$row_rsEquipamento = mysql_fetch_assoc($rsEquipamento);
	$totalRows_rsEquipamento = mysql_num_rows($rsEquipamento);

	if($totalRows_rsEquipamento)
		$IDEQ = $row_rsEquipamento['codEquip'];
	else
		$error .= 'error: RFID nao encontrado na tabela EQUIPAMENTO!<br>';

	//echo $IDEQ;

	// Pesquisa pela TOMADA

	$IDX = $OUTLET;

	mysql_select_db($database_conn, $conn);
	$query_rsTomada = "SELECT * FROM tomada WHERE codTomada = $OUTLET";
	$rsTomada = mysql_query($query_rsTomada, $conn) or die(mysql_error());
	$row_rsTomada = mysql_fetch_assoc($rsTomada);
	$totalRows_rsTomada = mysql_num_rows($rsTomada);

	if($totalRows_rsTomada)
		$IDTOMADA = $row_rsTomada['codTomada'];
	else
		$error .= 'error: tomada nao encontrada na tabela TOMADAS!<br>';

	//echo $IDTOMADA;


	if($error == '') {

		//CADASTRA ONDA ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

		// 2 - inserir registro na tabela captura
		$captureSQL = sprintf("INSERT INTO capturaatual (codTomada,codTipoOnda,codEquip,codEvento,valorMedio,offset,gain,eficaz,dataAtual) VALUES (%s,%s,%s,%s,%s,%s,%s,%s,NOW())",
			GetSQLValueString($IDTOMADA,'int'), GetSQLValueString($TIPOEVT,'int'), GetSQLValueString($IDEQ,'int'), GetSQLValueString($IDEVT,'int'),
			GetSQLValueString(hex2float32($MEAN_VAL),'double'), GetSQLValueString($OFFSET,'int'),GetSQLValueString(hex2float32($GAIN),'double'),GetSQLValueString(hex2float32($RMS),'double'));
		//execute sql
		mysql_select_db($database_conn,$conn) or die(mysql_error());
		$result1 = mysql_query($captureSQL,$conn) or die(mysql_error());
		$lastid = mysql_insert_id();

	    if((intval($TYPE) <= 3) && (intval($TYPE) > 0))
		{
			// QUEBRA OS VALORES SEPARADOS POR ';' :::::::::::::::::::
			$SINE = explode(';', $SINE);
			if(count($SINE) != 12)
				$error .= 'error: SENO nao contem 12 valores!<br>';

			$COSINE = explode(';', $COSINE);
			if(count($COSINE) != 12)
				$error .= 'error: COSSENO nao contem 12 valores!<br>';
            if($error == '') {
			    // 3 - inserir dados na tabela onda
			    for($i=0; $i<12; $i++)
			    {
				    $ondaSQL = sprintf("INSERT INTO harmatual (codCaptura,codHarmonica,sen,cos) VALUES (%s,%s,%s,%s)",
					    GetSQLValueString($lastid,'int'),GetSQLValueString($i+1,'int'),GetSQLValueString(hex2float32( $SINE[$i] ),'double'),
					    GetSQLValueString(hex2float32( $COSINE[$i] ),'double'));
				    //Execute SQL
				    mysql_select_db($database_conn,$conn) or die(mysql_error());
				    $result1 = mysql_query($ondaSQL,$conn) or die(mysql_error());
                }
			}
            else
               echo $error;
		}
		echo "Success!!";
	}
	else
		echo $error;

	mysql_free_result($rsEquipamento);
}

?>

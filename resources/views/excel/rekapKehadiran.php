<?php
$dataObj = $return;
$nama_ruang = $nama_ruang;
$bulan = $bulan;
$tahun = $tahun;
$tipe = $tipe;

// echo var_dump($dataObj);die;

$arrBulan = array(
    'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
);

$arrAlfabet = array(
    '',
    'A',
    'B',
    'C'
    ,'D'
    ,'E'
    ,'F'
    ,'G'
    ,'H'
    ,'I'
    ,'J'
    ,'K'
    ,'L'
    ,'M'
    ,'N'
    ,'O'
    ,'P'
    ,'Q'
    ,'R'
    ,'S'
    ,'T'
    ,'U'
    ,'V'
    ,'W'
    ,'X'
    ,'Y'
    ,'Z'
    ,'AA'
    ,'AB'
    ,'AC'
    ,'AD'
    ,'AE'
    ,'AF'
    ,'AG'
    ,'AH'
    ,'AI'
    ,'AJ'
    ,'AK'
    ,'AL'
    ,'AM'
    ,'AN'
    ,'AO'
    ,'AP'
    ,'AQ'
    ,'AR'
    ,'AS'
    ,'AT'
    ,'AU'
    ,'AV'
    ,'AW'
    ,'AX'
    ,'AY'
    ,'AZ'
    ,'BA'
    ,'BB'
    ,'BC'
    ,'BD'
    ,'BE'
    ,'BF'
    ,'BG'
    ,'BH'
    ,'BI'
    ,'BJ'
    ,'BK'
    ,'BL'
    ,'BM'
    ,'BN'
    ,'BO'
    ,'BP'
    ,'BQ'
    ,'BR'
    ,'BS'
    ,'BT'
    ,'BU'
    ,'BV'
    ,'BW'
    ,'BX'
    ,'BY'
    ,'BZ'
    ,'CA'
    ,'CB'
    ,'CC'
    ,'CD'
    ,'CE'
    ,'CF'
    ,'CG'
    ,'CH'
    ,'CI'
    ,'CJ'
    ,'CK'
    ,'CL'
    ,'CM'
    ,'CN'
    ,'CO'
    ,'CP'
    ,'CQ'
    ,'CR'
    ,'CS'
    ,'CT'
);

// echo json_encode($dataObj[0]);die;
// $Sekolah = $sekolah;
$headerTable = 3;
// $logo = "lumajang.png";

/** PHPExcel */
require_once ('Classes/PHPExcel.php');
// Create new PHPExcel object
$object = new PHPExcel();

//style untuk row
$style_row = array(
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, // Set text jadi di tengah secara vertical (middle)
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi di tengah secara vertical (middle)
  ),
  'borders' => array(
      'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('rgb' => '000000')
      )
  )
);

$object->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$object->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$object->getActiveSheet()->getPageSetup()->setFitToWidth(1);

//set FontSize
$object->getActiveSheet()->getStyle("A1")->getFont()->setSize(14);

// Style
$style_header = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => 'A6A6A6')
  ),
  'font'  => array(
      'color' => array('rgb' => '000000'),
  ),
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
  ),
);

// $table_header = array(
//   'fill' => array(
//       'type' => PHPExcel_Style_Fill::FILL_SOLID,
//       'color' => array('rgb' => '92D050')
//   ),
// );

// $object->getActiveSheet()->getStyle('A1:M1')->applyFromArray($style_header);
// $object->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->applyFromArray($table_header);

//set Width Colum
if(1==1){

    $object->getActiveSheet()->getColumnDimension('A')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('B')->setWidth(20);
    $object->getActiveSheet()->getColumnDimension('C')->setWidth(20);
    $object->getActiveSheet()->getColumnDimension('D')->setWidth(20);
    $object->getActiveSheet()->getColumnDimension('E')->setWidth(20);
    
    $object->getActiveSheet()->getColumnDimension('F')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('G')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('H')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('I')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('J')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('K')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('L')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('M')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('N')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('O')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('P')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('Q')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('R')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('S')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('T')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('U')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('V')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('W')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('X')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('Y')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('Z')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AA')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AB')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AC')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AD')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AE')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AF')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AG')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AH')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AI')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AJ')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AK')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AL')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AM')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AN')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AO')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AP')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AQ')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AR')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AS')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AT')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AU')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AV')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AW')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AX')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AY')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('AZ')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BA')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BB')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BC')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BD')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BE')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BF')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BG')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BH')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BI')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BJ')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BK')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BL')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BM')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BN')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BO')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BP')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BQ')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BR')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BS')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BT')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BU')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BV')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BW')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BX')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BY')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('BZ')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CA')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CB')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CC')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CD')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CE')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CF')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CG')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CH')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CI')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CJ')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CK')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CL')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CM')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CN')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CO')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CP')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CQ')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CR')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CS')->setWidth(7);
    $object->getActiveSheet()->getColumnDimension('CT')->setWidth(7);
}

//set Height Row
// $object->getActiveSheet()->getRowDimension('4')->setRowHeight(8);

//set font bold
$object->getActiveSheet()->getStyle('B2')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A3:A4');
$object->getActiveSheet()->mergeCells('B3:B4');
$object->getActiveSheet()->mergeCells('C3:C4');
$object->getActiveSheet()->mergeCells('D3:D4');
$object->getActiveSheet()->mergeCells('E3:E4');
// $object->getActiveSheet()->mergeCells('B3:M3');
// $object->getActiveSheet()->mergeCells('B4:M4');

//set colum text center
// $object->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$object->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
// $object->getActiveSheet()->getStyle('A1:C10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$ex = $object->setActiveSheetIndex(0);

//Add a drawing to the worksheet
// $objDrawing = new PHPExcel_Worksheet_Drawing();
// $objDrawing->setName('Logo');
// $objDrawing->setDescription('Logo');
// $objDrawing->setPath(__DIR__.'/img/lumajang.png');
// $objDrawing->setCoordinates('A1');
// $objDrawing->setHeight(62);
// // $objDrawing->setWidth(650);
// $objDrawing->setOffsetX(3);
// $objDrawing->setOffsetY(10);
// $objDrawing->setWorksheet($object->getActiveSheet());

$object->setActiveSheetIndex(0)
->setCellValue('A1', 'Laporan Rekap Kehadiran')
->setCellValue('A2', $arrBulan[($bulan-1)]." ".$tahun)
// ->setCellValue('A2', $nama_ruang)
->setCellValue('A'.$headerTable, 'No')
->setCellValue('B'.$headerTable, 'Nama')
->setCellValue('C'.$headerTable, 'NIK')
->setCellValue('D'.$headerTable, ($tipe === 'guru' ? 'NUPTK' : 'NISN'))
->setCellValue('E'.$headerTable, ($tipe === 'guru' ? 'Guru/Mapel' : 'Ruang'))
;

$ex->getStyle('A1')->getFont()->setBold(true);
$ex->getStyle('B1')->getFont()->setBold(true);

$ex->mergeCells('A1:F1');
$ex->mergeCells('A2:F2');

// $object->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->applyFromArray($style_row);

// set Judul
if(sizeof($dataObj) > 0){
  // setCellValueByColumnAndRow
//   for ($iJudul=0; $iJudul < sizeof($dataObj[0]); $iJudul++) { 
//     // $dataObj[0][$iJudul]->judul
//     $ex->setCellValueByColumnAndRow(($iJudul+2),4, $dataObj[0][$iJudul]['judul']);
//   }
$iJudul = 0;

foreach ($dataObj[0] as $key => $value) {
    $mystring = $key;
    $findme   = 'tanggal_';
    $pos = strpos($mystring, $findme);

    if ($pos === false) {
        // echo "The string '$findme' was not found in the string '$mystring'";
    } else {
        // echo "The string '$findme' was found in the string '$mystring'";
        // echo " and exists at position $pos";
        // $mystring2 = $key;
        $findme2   = 'datang';
        $findme3   = 'pulang';
        $pos2 = strpos($mystring, $findme2);
        $pos3 = strpos($mystring, $findme3);

        if ($pos2 === false && $pos3 === false) {
            // echo "The string '$findme' was not found in the string '$mystring'";
            // $ex->setCellValueByColumnAndRow(($iJudul+2),4, str_replace("_","",substr($key, (strlen($key)-2), 2)));
            $ex->setCellValueByColumnAndRow(($iJudul+5),3, str_replace("_","",substr($key, (strlen($key)-2), 2)));
            $ex->setCellValueByColumnAndRow(($iJudul+5),4, 'Hadir');
            $object->getActiveSheet()->mergeCells($arrAlfabet[($iJudul+6)].'3'.':'.$arrAlfabet[($iJudul+8)].'3');
        } else {
            $ex->setCellValueByColumnAndRow(($iJudul+5),4, str_replace("_"," ",substr($key, (strlen($key)-6), 6)));
        }


        $iJudul++;
    }
}
  
  $ex->getStyle($arrAlfabet[3].'4'.':'.$arrAlfabet[($iJudul+5)].'4')->getFont()->setBold(true);

  $ex->getStyle($arrAlfabet[3].'4'.':'.$arrAlfabet[($iJudul+5)].'4')->applyFromArray($style_row);
  $ex->getStyle($arrAlfabet[1].'3'.':'.$arrAlfabet[($iJudul+5)].'4')->applyFromArray($style_row);

//   $ex->setCellValue("C3", $arrBulan[($bulan-1)]." ".$tahun);
//   $object->getActiveSheet()->mergeCells('C3:'.$arrAlfabet[($iJudul+2)].'3');
}

// //Table
$num = $headerTable + 2;
$no='1';
foreach ($dataObj as $value) {
//   $ortu = $value->orang_tua_utama;
//   $nama_orangtua = 'nama_'.$ortu;
//   $no_telepon_orangtua = 'no_telepon_'.$ortu;

    $ex->setCellValue("A".$num, $no);
    $ex->setCellValue("B".$num, $value->nama);
    $ex->setCellValue("C".$num, $value->att_1);
    $ex->setCellValue("D".$num, $value->att_2);
    $ex->setCellValue("E".$num, $value->att_3);

//   for ($iKuis=0; $iKuis < sizeof($value->kuis); $iKuis++) { 
//     $ex->setCellValueByColumnAndRow(($iKuis+2),$num, $value->kuis[$iKuis]['skor']);
//   }
    $iKuis = 0;

    foreach ($value as $keye => $valuee) {
        $mystring = $keye;
        $findme   = 'tanggal_';
        $pos = strpos($mystring, $findme);

        if ($pos === false) {
            // echo "The string '$findme' was not found in the string '$mystring'";
        } else {
            // echo "The string '$findme' was found in the string '$mystring'";
            // echo " and exists at position $pos";
            $findme2   = 'datang';
            $findme3   = 'pulang';
            $pos2 = strpos($mystring, $findme2);
            $pos3 = strpos($mystring, $findme3);

            if ($pos2 === false && $pos3 === false) {
                // echo "The string '$findme' was not found in the string '$mystring'";
                $ex->setCellValueByColumnAndRow(($iKuis+5),$num, $valuee == 1 ? "✅" : "•");
            } else {
                $ex->setCellValueByColumnAndRow(($iKuis+5),$num, $valuee ? substr($valuee,10,6) : "-");
            }

            $iKuis++;
        }
    }

    $num ++;
    $no++;
}

$object->getActiveSheet()->getStyle('A1:CQ'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 
$object->getActiveSheet()->getStyle('A1:CQ'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); 
$object->getActiveSheet()->getStyle('B5:B'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT); 
$object->getActiveSheet()->getStyle('B5:B'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(false); 
$object->getActiveSheet()->getStyle('A1:A2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT); 
// $ex->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="rekap_kehadiran_'.$nama_ruang.'-'.date('Y-m-d H:i:s').'.xls"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel5');
// $data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
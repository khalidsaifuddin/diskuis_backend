<?php
$dataObj = $return;
// $nama_ruang = $nama_ruang;

$arrAlfabet = array('','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

// echo json_encode($dataObj[0]->kuis);die;
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
$object->getActiveSheet()->getColumnDimension('A')->setWidth(10);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(40);
$object->getActiveSheet()->getColumnDimension('C')->setWidth(40);
$object->getActiveSheet()->getColumnDimension('D')->setWidth(40);
$object->getActiveSheet()->getColumnDimension('E')->setWidth(40);
$object->getActiveSheet()->getColumnDimension('F')->setWidth(40);
$object->getActiveSheet()->getColumnDimension('G')->setWidth(40);
$object->getActiveSheet()->getColumnDimension('H')->setWidth(40);
$object->getActiveSheet()->getColumnDimension('I')->setWidth(40);
$object->getActiveSheet()->getColumnDimension('J')->setWidth(40);
$object->getActiveSheet()->getColumnDimension('K')->setWidth(10);
$object->getActiveSheet()->getColumnDimension('L')->setWidth(10);
$object->getActiveSheet()->getColumnDimension('M')->setWidth(10);

//set Height Row
// $object->getActiveSheet()->getRowDimension('4')->setRowHeight(8);

//set font bold
$object->getActiveSheet()->getStyle('B2')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->getFont()->setBold(true);

//set margeCell
// $object->getActiveSheet()->mergeCells('A3:A4');
// $object->getActiveSheet()->mergeCells('B3:B4');
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
->setCellValue('A1', 'Laporan Hasil Pengisan Kuis')
// ->setCellValue('A2', $nama_ruang)
->setCellValue('A'.$headerTable, 'Kode')
->setCellValue('B'.$headerTable, 'Pertanyaan')
->setCellValue('C'.$headerTable, 'Pilihan Jawaban')
->setCellValue('D'.$headerTable, 'Isian')
->setCellValue("E".$headerTable, 'jawaban_kuis_id')
->setCellValue("f".$headerTable, 'pertanyaan_kuis_id')
->setCellValue("G".$headerTable, 'pilihan_pertanyaan_kuis_id')
->setCellValue("H".$headerTable, 'kuis_id')
->setCellValue("I".$headerTable, 'sesi_kuis_id')
;

$ex->getStyle('A1')->getFont()->setBold(true);
$ex->getStyle('B1')->getFont()->setBold(true);

$ex->mergeCells('A1:F1');
$ex->mergeCells('A2:F2');

// $object->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->applyFromArray($style_row);

// // set Judul
// if(sizeof($dataObj) > 0){
//   // setCellValueByColumnAndRow
//   for ($iJudul=0; $iJudul < sizeof($dataObj[0]->kuis); $iJudul++) { 
//     // $dataObj[0]->kuis[$iJudul]->judul
//     $ex->setCellValueByColumnAndRow(($iJudul+2),4, $dataObj[0]->kuis[$iJudul]['judul']);
//   }
  
//   $ex->getStyle($arrAlfabet[3].'4'.':'.$arrAlfabet[($iJudul+2)].'4')->getFont()->setBold(true);

//   $ex->getStyle($arrAlfabet[3].'4'.':'.$arrAlfabet[($iJudul+2)].'4')->applyFromArray($style_row);
//   $ex->getStyle($arrAlfabet[1].'3'.':'.$arrAlfabet[($iJudul+2)].'4')->applyFromArray($style_row);

//   $ex->setCellValue("C3", 'Judul Kuis');
//   $object->getActiveSheet()->mergeCells('C3:'.$arrAlfabet[($iJudul+2)].'3');
// }

// //Table
$num = $headerTable + 1;
$no='1';
foreach ($dataObj as $value) {
//   $ortu = $value->orang_tua_utama;
//   $nama_orangtua = 'nama_'.$ortu;
//   $no_telepon_orangtua = 'no_telepon_'.$ortu;

  $ex->setCellValue("A".$num, $value->kode_pertanyaan);
  $ex->setCellValue("B".$num, strip_tags($value->pertanyaan));
  $ex->setCellValue("C".$num, $value->pilihan_jawaban);
  $ex->setCellValue("D".$num, $value->isian);
  $ex->setCellValue("E".$num, $value->jawaban_kuis_id);
  $ex->setCellValue("f".$num, $value->pertanyaan_kuis_id);
  $ex->setCellValue("G".$num, $value->pilihan_pertanyaan_kuis_id);
  $ex->setCellValue("H".$num, $value->kuis_id);
  $ex->setCellValue("I".$num, $value->sesi_kuis_id);

//   for ($iKuis=0; $iKuis < sizeof($value->kuis); $iKuis++) { 
//     $ex->setCellValueByColumnAndRow(($iKuis+2),$num, $value->kuis[$iKuis]['skor']);
//   }
//   $ex->setCellValue("C".$num, $value->nik);
//   $ex->setCellValue("D".$num, $value->urutan);
//   $ex->setCellValue("E".$num, $value->jenis_kelamin);
//   $ex->setCellValue("F".$num, $value->tempat_lahir);
//   $ex->setCellValue("G".$num, date("d/m/Y", strtotime($value->tanggal_lahir)));
//   $ex->setCellValue("H".$num, intval($value->jarak));
//   $ex->setCellValue("I".$num, $value->urut_pilihan);
//   $ex->setCellValue("J".$num, date("d/m/Y", strtotime($value->tanggal_konfirmasi)));
//   $ex->setCellValue("K".$num, $value->status_terima !== null ? $value->status_terima : "-");
//   $ex->setCellValue("L".$num, $value->$nama_orangtua);
//   $ex->setCellValue("M".$num, $value->$no_telepon_orangtua);

//   $object->getActiveSheet()->getStyle('A'.$num.':M'.$num)->applyFromArray($style_row);
//   $object->getActiveSheet()->getStyle('A'.$num.':M'.$num)->getFont()->setSize(9);

//   $object->getActiveSheet()->getStyle('A'.$num.':M'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
//   $object->getActiveSheet()->getStyle('B'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
//   $object->getActiveSheet()->getStyle("C".$num)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
//   $object->getActiveSheet()->getStyle("C".$num)->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);

  $num ++;
  $no++;
}

// $object->getActiveSheet()->getStyle('A1:M'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="hasil_pengisian_kuis-'.date('Y-m-d H:i:s').'.xls"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel5');
// $data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
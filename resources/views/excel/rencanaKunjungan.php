<?php
$sekolah = $return['sekolah'][0];
$rencana_kunjungan = $return['rencana_kunjungan']['rows'];

$headerTable = 9;

/** PHPExcel */
require_once ('Classes/PHPExcel.php');
// Create new PHPExcel object
$object = new PHPExcel();

//style untuk row
$style_row = array(
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
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

//set FontSize
$object->getActiveSheet()->getStyle("A1")->getFont()->setSize(14);

// Style
$style_header = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => '0000FF')
  ),
  'font'  => array(
      'color' => array('rgb' => 'FFFFFF'),
  ),
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
  ),
);

$table_header = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => 'FFC000')
  ),
);


$object->getActiveSheet()->getStyle('A1:G1')->applyFromArray($style_header);
$object->getActiveSheet()->getStyle('A'.$headerTable.':G'.$headerTable)->applyFromArray($table_header);

//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(9);
$object->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$object->getActiveSheet()->getColumnDimension('D')->setWidth(36);
$object->getActiveSheet()->getColumnDimension('E')->setWidth(17);
$object->getActiveSheet()->getColumnDimension('F')->setWidth(26);
$object->getActiveSheet()->getColumnDimension('G')->setWidth(10);

//set Height Row
$object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(31);

//set font bold
// $object->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:G1');
$object->getActiveSheet()->mergeCells('A3:B3');
$object->getActiveSheet()->mergeCells('A4:B4');
$object->getActiveSheet()->mergeCells('A5:B5');
$object->getActiveSheet()->mergeCells('A6:B6');
$object->getActiveSheet()->mergeCells('A7:B7');
$object->getActiveSheet()->mergeCells('C3:E3');
$object->getActiveSheet()->mergeCells('C4:E4');
$object->getActiveSheet()->mergeCells('C5:E5');
$object->getActiveSheet()->mergeCells('C6:E6');
$object->getActiveSheet()->mergeCells('C7:E7');

//set colum text center
$object->getActiveSheet()->getStyle('A'.$headerTable.':G'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
$object->getActiveSheet()->getStyle('A1:G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$ex = $object->setActiveSheetIndex(0);

$object->setActiveSheetIndex(0)
->setCellValue('A1', 'RENCANA KUNJUNGAN SEKOLAH '.$sekolah->nama)
->setCellValue('A3', 'Nama Sekolah')
->setCellValue('C3', ': '.$sekolah->nama)
->setCellValue('A4', 'NPSN')
->setCellValue('C4', ': '.$sekolah->npsn)
->setCellValue('A5', 'Alamat')
->setCellValue('C5', ': '.$sekolah->alamat_jalan)
->setCellValue('A6', 'Email')
->setCellValue('C6', ': '.$sekolah->email)
->setCellValue('A7', 'Fax')
->setCellValue('C7', ': '.$sekolah->nomor_fax)
->setCellValue('A'.$headerTable, 'No')
->setCellValue('B'.$headerTable, 'NPSN')
->setCellValue('C'.$headerTable, 'Nama Sekolah')
->setCellValue('D'.$headerTable, 'Alamat')
->setCellValue('E'.$headerTable, 'Tanggal Rencana Kunjungan')
->setCellValue('F'.$headerTable, 'Keterangan')
->setCellValue('G'.$headerTable, 'Status');

$object->getActiveSheet()->getStyle('A'.$headerTable.':G'.$headerTable)->applyFromArray($style_row);

//Table
$num = $headerTable + 1;
$no='1';
foreach ($rencana_kunjungan as $value) {
  switch ($value->jenis_kunjungan_id) {
    case '1': $status = "Terlaksana"; break;
    case '2': $status = "Terjadwal"; break;
    
    default: $status = ""; break;
  }

  $ex->setCellValue("A".$num, $no);
  $ex->setCellValue("B".$num, $value->npsn);
  $ex->setCellValue("C".$num, $value->nama);
  $ex->setCellValue("D".$num, $value->alamat_jalan);
  $ex->setCellValue("E".$num, $value->tanggal);
  $ex->setCellValue("F".$num, $value->hasil_pengamatan);
  $ex->setCellValue("G".$num, $status);

  $ex->getStyle('A'.$num.':G'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('C'.$num.':D'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
  $object->getActiveSheet()->getStyle('A'.$num.':G'.$num)->applyFromArray($style_row);
  $object->getActiveSheet()->getStyle('A'.$num.':G'.$num)->getFont()->setSize(9);

  $num ++;
  $no++;
}

$object->getActiveSheet()->getStyle('A1:H'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Rencana Kunjungan Sekolah '.$sekolah->nama.'.xlsx"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
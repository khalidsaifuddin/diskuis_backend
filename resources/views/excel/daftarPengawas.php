<?php
$daftarPengawas = $return['pengawas']['data'];

$wilayah = @$daftarPengawas[0]->provinsi;

$headerTable = 3;

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
      'color' => array('rgb' => 'A6A6A6')
  ),
  'font'  => array(
      'color' => array('rgb' => '000000'),
  ),
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
  ),
);

$table_header = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => 'F2F2F2')
  ),
);

$object->getActiveSheet()->getStyle('A1:G1')->applyFromArray($style_header);
$object->getActiveSheet()->getStyle('A'.$headerTable.':G'.$headerTable)->applyFromArray($table_header);

//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(4);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(17);
$object->getActiveSheet()->getColumnDimension('C')->setWidth(20);
$object->getActiveSheet()->getColumnDimension('D')->setWidth(17);
$object->getActiveSheet()->getColumnDimension('E')->setWidth(25);
$object->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('G')->setWidth(30);

//set Height Row
// $object->getActiveSheet()->getRowDimension('4')->setRowHeight(8);

//set font bold
// $object->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:G1');

//set colum text center
$object->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$object->getActiveSheet()->getStyle('A'.$headerTable.':G'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
// $object->getActiveSheet()->getStyle('A1:C10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$ex = $object->setActiveSheetIndex(0);

$object->setActiveSheetIndex(0)
->setCellValue('A1', 'DAFTAR PENGAWAS')
->setCellValue('A'.$headerTable, 'No')
->setCellValue('B'.$headerTable, 'NIP')
->setCellValue('C'.$headerTable, 'Nama Pengawas')
->setCellValue('D'.$headerTable, 'Tanggal Lahir')
->setCellValue('E'.$headerTable, 'Jenis Pengawas')
->setCellValue('F'.$headerTable, 'Jumlah Sekolah Binaan')
->setCellValue('G'.$headerTable, 'Wilayah');

$object->getActiveSheet()->getStyle('A'.$headerTable.':G'.$headerTable)->applyFromArray($style_row);

//Table
$num = $headerTable + 1;
$no='1';
foreach ($daftarPengawas as $value) {
  $ex->setCellValue("A".$num, $value->no);
  $ex->setCellValue("B".$num, $value->nip);
  $ex->setCellValue("C".$num, $value->nama);
  $ex->setCellValue("D".$num, date("d F Y", strtotime($value->tanggal_lahir)));
  $ex->setCellValue("E".$num, $value->bidang_studi_id_str);
  $ex->setCellValue("F".$num, $value->jml);
  $ex->setCellValue("G".$num, $value->kabupaten . " " . $value->provinsi);

  $object->getActiveSheet()->getStyle('A'.$num.':G'.$num)->applyFromArray($style_row);
  $object->getActiveSheet()->getStyle('A'.$num.':G'.$num)->getFont()->setSize(9);

  $object->getActiveSheet()->getStyle('A'.$num.':G'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $object->getActiveSheet()->getStyle('C'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
  $object->getActiveSheet()->getStyle('E'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
  $object->getActiveSheet()->getStyle('G'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

  $object->getActiveSheet()->getStyle('B'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
  $object->getActiveSheet()->getStyle('D'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);

  $num ++;
  $no++;
}

$object->getActiveSheet()->getStyle('A1:G'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Daftar Pengawas '.$wilayah.'.xlsx"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
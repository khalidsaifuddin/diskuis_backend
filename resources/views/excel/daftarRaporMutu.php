<?php
$daftarRaporMutu = $return['list']['rows'];

$headerTable = 3;

/** PHPExcel */
require_once ('Classes/PHPExcel.php');
// Create new PHPExcel object
$object = new PHPExcel();

//style untuk colomn
$style_col = array(
  'font' => array('bold' => true), // Set font nya jadi bold
  'alignment' => array(
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
  ),
  'borders' => array(
    'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
    'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
    'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
  )
);

//style untuk row
$style_row = array(
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
  ),
  'borders' => array(
    'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
    'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
    'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
  )
);

$object->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$object->getActiveSheet()->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

//set FontSize
$object->getActiveSheet()->getStyle("A1")->getFont()->setSize(14);

// $styleArray = array(
//     'font'  => array(
//         'color' => array('rgb' => 'FFFFFF'),
//     ));
// $object->getActiveSheet()->getStyle('F6:J14')->applyFromArray($styleArray);

//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(12);
$object->getActiveSheet()->getColumnDimension('C')->setWidth(27);
$object->getActiveSheet()->getColumnDimension('D')->setWidth(23);
$object->getActiveSheet()->getColumnDimension('E')->setWidth(20);
$object->getActiveSheet()->getColumnDimension('F')->setWidth(8);
$object->getActiveSheet()->getColumnDimension('G')->setWidth(8);
$object->getActiveSheet()->getColumnDimension('H')->setWidth(8);
$object->getActiveSheet()->getColumnDimension('I')->setWidth(8);

//set Height Row
// $object->getActiveSheet()->getRowDimension('4')->setRowHeight(8);
// $object->getActiveSheet()->getRowDimension('9')->setRowHeight(7);
$object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(21);

//set font bold
$object->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:I1');

//set colum text center
// $object->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
// $object->getActiveSheet()->getStyle('A1:C10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$ex = $object->setActiveSheetIndex(0);

$object->setActiveSheetIndex(0)
->setCellValue('A1', 'RaporMutu Sekolah Binaan')
->setCellValue('A'.$headerTable, 'No')
->setCellValue('B'.$headerTable, 'NPSN')
->setCellValue('C'.$headerTable, 'Nama Sekolah')
->setCellValue('D'.$headerTable, 'Kecamatan')
->setCellValue('E'.$headerTable, 'Kabupaten')
->setCellValue('F'.$headerTable, 'R.2016')
->setCellValue('G'.$headerTable, 'R.2017')
->setCellValue('H'.$headerTable, 'R.2018')
->setCellValue('I'.$headerTable, 'R.2019');

$object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->applyFromArray($style_row);
$object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->getFont()->setBold(true);

//Table
$num = $headerTable + 1;
$no='1';
foreach ($daftarRaporMutu as $value) {
  $ex->setCellValue("A".$num, $no);
  $ex->setCellValue("B".$num, $value->npsn);
  $ex->setCellValue("C".$num, $value->nama);
  $ex->setCellValue("D".$num, $value->kecamatan);
  $ex->setCellValue("E".$num, $value->kabupaten);
  $ex->setCellValue("F".$num, number_format((float)$value->r16, 2, '.', ''));
  $ex->setCellValue("G".$num, number_format((float)$value->r17, 2, '.', ''));
  $ex->setCellValue("H".$num, number_format((float)$value->r18, 2, '.', ''));
  $ex->setCellValue("I".$num, number_format((float)$value->r19, 2, '.', ''));

  $ex->getStyle('A'.$num.':I'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('B'.$num.':E'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
  $object->getActiveSheet()->getStyle('A'.$num.':I'.$num)->applyFromArray($style_row);
  $object->getActiveSheet()->getStyle('A'.$num.':I'.$num)->getFont()->setSize(10);

  $num ++;
  $no++;
}

$object->getActiveSheet()->getStyle('A1:G'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="RaporMutu Sekolah Binaan.xlsx"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
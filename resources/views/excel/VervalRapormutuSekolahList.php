<?php
$list_sekolah = $return['list_sekolah']['rows'];
// print_r($list_sekolah); exit;
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
$object->getActiveSheet()->getColumnDimension('A')->setWidth(4);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(9);
$object->getActiveSheet()->getColumnDimension('C')->setWidth(28);
$object->getActiveSheet()->getColumnDimension('D')->setWidth(35);
$object->getActiveSheet()->getColumnDimension('E')->setWidth(12);
$object->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('G')->setWidth(12);
$object->getActiveSheet()->getColumnDimension('H')->setWidth(10);

//set Height Row
// $object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(45);

//set font bold
$object->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A'.$headerTable.':H'.($headerTable+1))->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:H1');
$object->getActiveSheet()->mergeCells('A3:A4');
$object->getActiveSheet()->mergeCells('B3:B4');
$object->getActiveSheet()->mergeCells('C3:C4');
$object->getActiveSheet()->mergeCells('D3:D4');
$object->getActiveSheet()->mergeCells('E3:F3');
$object->getActiveSheet()->mergeCells('G3:G3');

//set colum text center
// $object->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$object->getActiveSheet()->getStyle('A'.$headerTable.':H'.($headerTable + 1))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
$object->getActiveSheet()->getStyle('A1:C10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$ex = $object->setActiveSheetIndex(0);

$object->setActiveSheetIndex(0)
->setCellValue('A1', 'Verval Rapormutu Sekolah')
->setCellValue('A'.$headerTable, 'No')
->setCellValue('B'.$headerTable, 'NPSN')
->setCellValue('C'.$headerTable, 'Nama Sekolah')
->setCellValue('D'.$headerTable, 'Alamat')
->setCellValue('E'.$headerTable, 'Rapor Mutu')
->setCellValue('E'.($headerTable + 1), 'Hitung Terakhir')
->setCellValue('F'.($headerTable + 1), 'Status Rapor Mutu')
->setCellValue('G'.$headerTable, 'Veval')
->setCellValue('G'.($headerTable + 1), 'Verval Terakhir')
->setCellValue('H'.($headerTable + 1), 'Status Veval');

$object->getActiveSheet()->getStyle('A'.$headerTable.':H'.($headerTable+1) )->applyFromArray($style_row);
$object->getActiveSheet()->getStyle('A'.$headerTable.':H'.($headerTable+1) )->getFont()->setBold(true);

//Table
$num = $headerTable + 2;
$no = '1';
foreach ($list_sekolah as $value) {
  $ex->setCellValue("A".$num, $no);
  $ex->setCellValue("B".$num, $value->npsn);
  $ex->setCellValue("C".$num, $value->nama);
  $ex->setCellValue("D".$num, $value->kecamatan." ".$value->kabupaten);
  $ex->setCellValue("E".$num, $value->tanggal != "" ? date("Y-m-d", strtotime($value->tanggal)) : "-");
  $ex->setCellValue("F".$num, $value->timeline_id != "" ? "Selesai Dihitung" : "Belum Dihitung");
  $ex->setCellValue("G".$num, $value->tanggal_verval_terakhir != "" ? date("Y-m-d", strtotime($value->tanggal_verval_terakhir)) : "-");
  $ex->setCellValue("H".$num, $value->tanggal_verval_terakhir != "" ? "Sudah" : "Belum");

  $ex->getStyle('A'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('E'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('F'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('H'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $object->getActiveSheet()->getStyle('A'.$num.':H'.$num)->applyFromArray($style_row);
  $object->getActiveSheet()->getStyle('A'.$num.':H'.$num)->getFont()->setSize(10);

  $num ++;
  $no++;
}

$object->getActiveSheet()->getStyle('A1:H'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Daftar Verval Rapormmutu sekolah.xlsx"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
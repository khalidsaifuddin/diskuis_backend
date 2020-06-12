<?php
$pengawas = $return['pengawas'];
$sekolah_binaan = $return['sekolah_binaan']['rows'];

$headerTable = 8;

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
  )
);

$table_header = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => 'FFC000')
  ),
);


$object->getActiveSheet()->getStyle('A1:G1')->applyFromArray($style_header);
$object->getActiveSheet()->getStyle('A'.$headerTable.':G'.$headerTable)->applyFromArray($table_header);

//set FontSize
$object->getActiveSheet()->getStyle("A1")->getFont()->setSize(14);

//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(4);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(10);
$object->getActiveSheet()->getColumnDimension('C')->setWidth(29);
$object->getActiveSheet()->getColumnDimension('D')->setWidth(31);
$object->getActiveSheet()->getColumnDimension('E')->setWidth(22);
$object->getActiveSheet()->getColumnDimension('F')->setWidth(16);
$object->getActiveSheet()->getColumnDimension('G')->setWidth(18);

//set Height Row
// $object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(21);

//set font bold
// $object->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:G1');
$object->getActiveSheet()->mergeCells('A3:B3');
$object->getActiveSheet()->mergeCells('A4:B4');
$object->getActiveSheet()->mergeCells('A5:B5');
$object->getActiveSheet()->mergeCells('A6:B6');
$object->getActiveSheet()->mergeCells('C3:D3');
$object->getActiveSheet()->mergeCells('C4:D4');
$object->getActiveSheet()->mergeCells('C5:D5');
$object->getActiveSheet()->mergeCells('C6:D6');

//set colum text center
// $object->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$object->getActiveSheet()->getStyle('A'.$headerTable.':G'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
$object->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$ex = $object->setActiveSheetIndex(0);

$object->setActiveSheetIndex(0)
->setCellValue('A1', 'SEKOLAH BINAAN PENGAWAS')
->setCellValue('A3', 'Nama')
->setCellValue('C3', ': '.$pengawas['nama'])
->setCellValue('A4', 'NUPTK')
->setCellValue('C4', ': '.$pengawas['nuptk'])
->setCellValue('A5', 'Jenis PTK')
->setCellValue('C5', ': '.$pengawas['bidang_studi_id_str'])
->setCellValue('A6', 'Wilayah')
->setCellValue('C6', ': '.$pengawas['kabupaten'].', '.$pengawas['provinsi'])
->setCellValue('A'.$headerTable, 'No')
->setCellValue('B'.$headerTable, 'NPSN')
->setCellValue('C'.$headerTable, 'Nama Sekolah')
->setCellValue('D'.$headerTable, 'Alamat')
->setCellValue('E'.$headerTable, 'Kecamatan')
->setCellValue('F'.$headerTable, 'Kabupaten')
->setCellValue('G'.$headerTable, 'Propinsi');

$object->getActiveSheet()->getStyle('A'.$headerTable.':G'.$headerTable)->applyFromArray($style_row);

//Table
$num = $headerTable + 1;
$no='1';
foreach ($sekolah_binaan as $value) {
  $ex->setCellValue("A".$num, $no);
  $ex->setCellValue("B".$num, $value->npsn);
  $ex->setCellValue("C".$num, $value->nama);
  $ex->setCellValue("D".$num, $value->alamat_jalan);
  $ex->setCellValue("E".$num, $value->kecamatan);
  $ex->setCellValue("F".$num, $value->kabupaten);
  $ex->setCellValue("G".$num, $value->provinsi);

  $ex->getStyle('A'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('B'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
  $object->getActiveSheet()->getStyle('A'.$num.':G'.$num)->applyFromArray($style_row);
  $object->getActiveSheet()->getStyle('A'.$num.':G'.$num)->getFont()->setSize(9);

  $num ++;
  $no++;
}

$object->getActiveSheet()->getStyle('A1:G'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Sekolah Binaan Pengawas '.$pengawas['nama'].'.xlsx"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
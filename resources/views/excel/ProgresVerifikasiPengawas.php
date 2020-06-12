<?php
$getProgresVerval = @$return['getProgresVerval']['rows'];

$getProgresVervalPeriodik = @$return['getProgresVervalPeriodik'];
$label = $getProgresVervalPeriodik['labels'];
$name = "Progres Vertivikasi Pengawas";
$data = $getProgresVervalPeriodik['datasets'][0]['data'];

$jumlah_pengawas_ttl = 0;
$jumlah_pengawas_selesai_ttl = 0;
foreach ($getProgresVerval as $key) {
	$jumlah_pengawas_ttl = $jumlah_pengawas_ttl + $key->jumlah_pengawas;
	$jumlah_pengawas_selesai_ttl = $jumlah_pengawas_selesai_ttl + $key->jumlah_pengawas_selesai;
}

$persen = ($jumlah_pengawas_selesai_ttl / $jumlah_pengawas_ttl) * 100;
$headerTable = 27;

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

//set FontSize
$object->getActiveSheet()->getStyle("A1")->getFont()->setSize(18);

// $styleArray = array(
//     'font'  => array(
//         'color' => array('rgb' => 'FFFFFF'),
//     ));
// $object->getActiveSheet()->getStyle('F6:J14')->applyFromArray($styleArray);

//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(46);

//set Height Row
$object->getActiveSheet()->getRowDimension('1')->setRowHeight(24);
$object->getActiveSheet()->getRowDimension('6')->setRowHeight(1);
$object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(30);

//set font bold
$object->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:F1');
$object->getActiveSheet()->mergeCells('A2:B2');
$object->getActiveSheet()->mergeCells('A3:F3');
$object->getActiveSheet()->mergeCells('A4:F4');

//set colum text center
$object->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
$object->getActiveSheet()->getStyle('B7:B10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
$ex = $object->setActiveSheetIndex(0);

//Add a drawing to the worksheet
// $objDrawing = new PHPExcel_Worksheet_Drawing();
// $objDrawing->setName('Logo');
// $objDrawing->setDescription('Logo');
// $objDrawing->setPath(__DIR__.'/../../../resource/img/header.jpg');
// $objDrawing->setCoordinates('A1');
// $objDrawing->setHeight(10);
// $objDrawing->setWidth(650);
// $objDrawing->setWorksheet($object->getActiveSheet());

$object->setActiveSheetIndex(0)
->setCellValue('A1', 'Progres Verifikasi Pengawas')
->setCellValue('A2', 'Progres Verifikasi Pengawas')
->setCellValue('A3', number_format($persen, 3)."%")
->setCellValue('A4', $jumlah_pengawas_selesai_ttl.' dari '.$jumlah_pengawas_ttl.' pengawas telah selesai melakukan verval rapor mutu')
->setCellValue('C6', $name)
->setCellValue('A'.$headerTable, 'No')
->setCellValue('B'.$headerTable, 'Nama Wilayah')
->setCellValue('C'.$headerTable, 'Sekolah Total')
->setCellValue('D'.$headerTable, 'Sekolah Selesai')
->setCellValue('E'.$headerTable, 'Sisa')
->setCellValue('F'.$headerTable, 'Progres Verifikasi Pengawas')
;


//Border Table
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->applyFromArray($style_row);
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->getFont()->setBold(true);

// CHART
/* Label */
$num_lbl = 7;
foreach ($label as $lbl) {
	$ex->setCellValue("B".$num_lbl, $lbl);
	$object->getActiveSheet()->getRowDimension($num_lbl)->setRowHeight(1);
	$num_lbl++;
}

$num_lbl = 7;
foreach ($data as $lbl) {
	$ex->setCellValue("C".$num_lbl, $lbl);
	$num_lbl++;
}

$rowNum = $num_lbl;
$labels = array(
    new PHPExcel_Chart_DataSeriesValues('String', "'Worksheet'!C6", null, 1),
);
$chrtCols = "'Worksheet'!B7:B$rowNum"; //label
$chrtVals = "'Worksheet'!C7:C$rowNum"; //data
$periods = new PHPExcel_Chart_DataSeriesValues('String', $chrtCols, null, $rowNum-1);
$values = new PHPExcel_Chart_DataSeriesValues('Number', $chrtVals, null, $rowNum-1);
$legend = null; //new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_TOP, NULL, false);
$series = new PHPExcel_Chart_DataSeries(
    PHPExcel_Chart_DataSeries::TYPE_LINECHART,       
    PHPExcel_Chart_DataSeries::GROUPING_STANDARD,  
    array(0,1),                                       
    $labels,                                       
    array($periods,$periods),                               
    array($values),
    $legend
);
$series->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);
$layout = new PHPExcel_Chart_Layout();

$plotarea = new PHPExcel_Chart_PlotArea($layout, array($series));
$chart = new PHPExcel_Chart('sample', null, null, $plotarea);
$chart->setTopLeftPosition('A6', 0, 0);
$chart->setBottomRightPosition('E25', 0, 0);
$object->getActiveSheet()->addChart($chart);

// Table
$num = $headerTable + 1;
$no='1';
$jam = "9";
foreach ($getProgresVerval as $value) {

  $ex->setCellValue("A".$num, $no);
  $ex->setCellValue("B".$num, $value->nama);
  $ex->setCellValue("C".$num, number_format($value->jumlah_pengawas));
  $ex->setCellValue("D".$num, number_format($value->jumlah_pengawas_selesai));
  $ex->setCellValue("E".$num, number_format($value->jumlah_pengawas - $value->jumlah_pengawas_selesai));
  $ex->setCellValue("F".$num, number_format($value->persen, 2)."%");

  $ex->getStyle('A'.$num.':F'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('B'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
  $object->getActiveSheet()->getStyle('A'.$num.':F'.$num)->applyFromArray($style_row);
  $num ++;
  $no++;
}

$object->getActiveSheet()->getStyle('A1:F'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true);

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Progres Verifikasi Pengawas Pengawas.xlsx');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
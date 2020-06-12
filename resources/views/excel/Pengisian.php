<?php
$ProgresPengisian = @$return['ProgresPengisian']['rows'];

$ProgresPengisianPeriodik = @$return['ProgresPengisianPeriodik'];
$label = $ProgresPengisianPeriodik['labels'];
$name = "Progres Pengisian";
$data = $ProgresPengisianPeriodik['datasets'][0]['data'];

$total_ttl = 0;
$timeline_5_ttl = 0;
foreach ($ProgresPengisian as $key) {
  $total_ttl = $total_ttl + $key->total;
  $timeline_5_ttl = $timeline_5_ttl + $key->timeline_5;
}

$persen = ($timeline_5_ttl / $total_ttl) * 100;

$headerTable = 11;

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
$object->getActiveSheet()->getStyle("A1:H1")->getFont()->setSize(18);
$object->getActiveSheet()->getStyle('H'.$headerTable.':H'.($headerTable + 2))->getFont()->setSize(12);

// Style
$style_header = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => '00B0F0')
  ),
  'font'  => array(
      'color' => array('rgb' => 'FFFFFF'),
  )
);

$table_header = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => 'FFC000')
  ),
);

$widget = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => '00AC50')
  ),
  'font'  => array(
      'color' => array('rgb' => 'FFFFFF'),
  )
);


$object->getActiveSheet()->getStyle('A1:H1')->applyFromArray($style_header);
$object->getActiveSheet()->getStyle('H'.$headerTable.':H'.($headerTable + 2))->applyFromArray($widget);
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->applyFromArray($table_header);

//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(25);
$object->getActiveSheet()->getColumnDimension('G')->setWidth(4);
$object->getActiveSheet()->getColumnDimension('H')->setWidth(60);

//set Height Row
$object->getActiveSheet()->getRowDimension('1')->setRowHeight(24);
$object->getActiveSheet()->getRowDimension('6')->setRowHeight(1);
$object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(30);

//set font bold
$object->getActiveSheet()->getStyle('A1:F1')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('H'.$headerTable.':H'.($headerTable + 2))->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:H1');

//set colum text center
$object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$object->getActiveSheet()->getStyle('H'.$headerTable.':H'.($headerTable + 2))->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
$object->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
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
->setCellValue('A1', 'PROGRES PENGISIAN')
->setCellValue('H'.$headerTable, 'PROGRES PENGISIAN')
->setCellValue('H'.($headerTable + 1), number_format($persen, 3)." %")
->setCellValue('H'.($headerTable + 2), $timeline_5_ttl.' dari '.$total_ttl.' sekolah telah selesai mengisi PMP')
// ->setCellValue('C6', $name)
->setCellValue('A'.$headerTable, 'No')
->setCellValue('B'.$headerTable, 'Nama Wilayah')
->setCellValue('C'.$headerTable, 'Sekolah Total')
->setCellValue('D'.$headerTable, 'Sekolah Selesai')
->setCellValue('E'.$headerTable, 'Sisa')
->setCellValue('F'.$headerTable, 'Progres Pengisian')
;


//Border Table
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->applyFromArray($style_row);
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->getFont()->setBold(true);

// CHART
/* Label */
$num_lbl = 2;
foreach ($label as $lbl) {
	$ex->setCellValue("B".$num_lbl, $lbl);
	$object->getActiveSheet()->getRowDimension($num_lbl)->setRowHeight(1);
	$num_lbl++;
}

$num_lbl = 2;
foreach ($data as $lbl) {
	$ex->setCellValue("C".$num_lbl, $lbl);
	$num_lbl++;
}

$rowNum = $num_lbl;
$labels = array(
    new PHPExcel_Chart_DataSeriesValues('String', "'Worksheet'!C2", null, 1),
);
$chrtCols = "'Worksheet'!B3:B$rowNum"; //label
$chrtVals = "'Worksheet'!C3:C$rowNum"; //data
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
$chart->setTopLeftPosition('H15', 0, 0);
$chart->setBottomRightPosition('I25', 0, 0);
$object->getActiveSheet()->addChart($chart);

// Table
$num = $headerTable + 1;
$no='1';
$jam = "9";
foreach ($ProgresPengisian as $value) {

  $ex->setCellValue("A".$num, $no);
  $ex->setCellValue("B".$num, $value->nama);
  $ex->setCellValue("C".$num, number_format($value->total));
  $ex->setCellValue("D".$num, number_format($value->timeline_5));
  $ex->setCellValue("E".$num, number_format($value->total - $value->timeline_5));
  $ex->setCellValue("F".$num, number_format($value->persen_bulat, 2)." %");

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
header('Content-Disposition: attachment;filename="Progres Pengisian Pengawas.xlsx');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
<?php
$headerTable = '26';
$raporMutu = $return['raporMutu']['rows'];
$radar = $return['radar'];
$nama_wilayah = @$raporMutu[0]->induk;
$indikator = $radar['datasets'][2]['data'];

$nilai = (
  (float)$indikator[1] +
  (float)$indikator[7] +
  (float)$indikator[6] +
  (float)$indikator[2] +
  (float)$indikator[0] +
  (float)$indikator[4] +
  (float)$indikator[3] +
  (float)$indikator[5]
) / 8;

if($nilai>=0 && $nilai<=2.04){
    $predikat = 'Menuju SNP 1';
    $bintang = '★';
}else if($nilai>2.04 && $nilai <=3.70){
    $predikat = 'Menuju SNP 2';
    $bintang = '★★';
}else if($nilai>3.70 && $nilai <=5.06){
    $predikat = 'Menuju SNP 3';
    $bintang = '★★★';
}else if($nilai>5.06 && $nilai <=6.66){
    $predikat = 'Menuju SNP 4';
    $bintang = '★★★★';
}else if($nilai>6.66){
    $predikat = 'SNP';
    $bintang = '';
}

// print_r($detail);
// print_r($indikator);
// exit;
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

// $styleArray = array(
//     'font'  => array(
//         'color' => array('rgb' => 'FFFFFF'),
//     ));
// $object->getActiveSheet()->getStyle('F6:J14')->applyFromArray($styleArray);


//set FontSize
$object->getActiveSheet()->getStyle("A1")->getFont()->setSize(16);
$object->getActiveSheet()->getStyle("B12")->getFont()->setSize(20);

//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(41);
$object->getActiveSheet()->getColumnDimension('C')->setWidth(10);
$object->getActiveSheet()->getColumnDimension('D')->setWidth(10);
$object->getActiveSheet()->getColumnDimension('E')->setWidth(10);
$object->getActiveSheet()->getColumnDimension('F')->setWidth(10);

//set Height Row
$object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(24);
$object->getActiveSheet()->getRowDimension(3)->setRowHeight(1);

//set font bold
$object->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('B12')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:F1');
$object->getActiveSheet()->mergeCells('B12:B13');

//set colum text center
$object->getActiveSheet()->getStyle('B12:B15')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
// $object->getActiveSheet()->getStyle('B'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

// set Border
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->applyFromArray($style_row);
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->getFont()->setBold(true);

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
->setCellValue('A1', 'Pencapaian Per Wilayah di '.$nama_wilayah)
->setCellValue('B12', number_format((float)$nilai, 2, '.', ''))
->setCellValue('B14', 'Predikat : '.$predikat)
->setCellValue('B15', $bintang)
->setCellValue('A'. $headerTable, 'No')
->setCellValue('B'. $headerTable, 'Nama Wilayah')
->setCellValue('C'. $headerTable, 'R.2016')
->setCellValue('D'. $headerTable, 'R.2017')
->setCellValue('E'. $headerTable, 'R.2018')
->setCellValue('F'. $headerTable, 'R.2019');
$ex = $object->setActiveSheetIndex(0);

//Chart
$ii = 4;
/* label */
$i = $ii;
$n = 0;
$label = 17;
foreach ($radar['labels'] as $key) {
  $ex->setCellValue("D".$i, $key);
  $value = $radar['datasets'][2]['data'];
  $ex->setCellValue("B".$label, $key.' : '.$value[$n]);

  $object->getActiveSheet()->getRowDimension($i)->setRowHeight(1);
  
  $i++;
  $n++;
  $label++;
}

/* data */
$abjad = ['E', 'F', 'G', 'H', 'I', 'J'];
$a = 0;
$b = $ii;
foreach ($radar['datasets'] as $key1) {
  $ex->setCellValue($abjad[$a].($b-1), $key1['label']);
  foreach ($key1['data'] as $key2) {
    $ex->setCellValue($abjad[$a].$b, $key2);
    $b++;
  }

  $a++;
  $b = $ii;
}

/* radar */
//MERAH
$dataseriesLabels = array(
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$E$3', NULL, 1),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$F$3', NULL, 1),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$G$3', NULL, 1),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$H$3', NULL, 1),
);

//UNGGU
$xAxisTickValues = array(
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$D$4:$D$11', NULL, 8),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$D$4:$D$11', NULL, 8),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$D$4:$D$11', NULL, 8),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$D$4:$D$11', NULL, 8),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$D$4:$D$11', NULL, 8),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$D$4:$D$11', NULL, 8),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$D$4:$D$11', NULL, 8),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$D$4:$D$11', NULL, 8),
);
//BIRU
$dataSeriesValues = array(
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$E$4:$E$11', NULL, 8),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$F$4:$F$11', NULL, 8),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$G$4:$G$11', NULL, 8),
  new PHPExcel_Chart_DataSeriesValues('String','worksheet!$H$4:$H$11', NULL, 8),
);

$series = new PHPExcel_Chart_DataSeries(
    PHPExcel_Chart_DataSeries::TYPE_RADARCHART,
    null,
    range(0, count($dataSeriesValues)-1),
    $dataseriesLabels,
    $xAxisTickValues,
    $dataSeriesValues,
    null,
    null,
    PHPExcel_Chart_DataSeries::STYLE_MARKER
);
$plotarea = new PHPExcel_Chart_PlotArea(null, array($series));
$legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_TOP,
    NULL, false);
$title = null;//new PHPExcel_Chart_Title('Pencapaian Per Wilayah di {nama_wilayah}');
$chart = new PHPExcel_Chart(
    'chart1',
    $title,
    $legend,
    $plotarea,
    true,
    0,
    null,
    null
);
$chart->setTopLeftPosition('D12', 0, 0);
$chart->setBottomRightPosition('K25', 0, 0);
$object->getActiveSheet()->addChart($chart);


// Table
$num = $headerTable + 1;
$no = '1';

foreach ($raporMutu as $value) {
  $ex->setCellValue("A".$num, $no + 1);
  $ex->setCellValue("B".$num, $value->nama);
  $ex->setCellValue("C".$num, number_format((float)$value->r16, 2, '.', ''));
  $ex->setCellValue("D".$num, number_format((float)$value->r17, 2, '.', ''));
  $ex->setCellValue("E".$num, number_format((float)$value->r18, 2, '.', ''));
  $ex->setCellValue("F".$num, number_format((float)$value->r19, 2, '.', ''));

  $ex->getStyle('A'.$num.':F'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('B'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
  $object->getActiveSheet()->getStyle('A'.$num.':F'.$num)->applyFromArray($style_row);

  $no++;
  $num++;
}

$object->getActiveSheet()->getStyle('A12:M'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Pencapaian Per Wilayah di '.$nama_wilayah.'.xlsx"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
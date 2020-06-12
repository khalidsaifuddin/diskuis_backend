<?php
$headerTable = '12';
$detail = $return['detail'];
$indikator = $return['indikator']['rows'];
$color = [
  'parent' => 'Blue',
  'child' => 'aqua',
  'grandchild' => 'yellow',
];
$nilai = (
  (float)$indikator[1]->r18 +
  (float)$indikator[7]->r18 +
  (float)$indikator[6]->r18 +
  (float)$indikator[2]->r18 +
  (float)$indikator[0]->r18 +
  (float)$indikator[4]->r18 +
  (float)$indikator[3]->r18 +
  (float)$indikator[5]->r18
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

// print_r($indikator); exit;
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
$object->getActiveSheet()->getStyle("A1")->getFont()->setSize(18);
$object->getActiveSheet()->getStyle("G3")->getFont()->setSize(8);
$object->getActiveSheet()->getStyle("G4")->getFont()->setSize(14);
$object->getActiveSheet()->getStyle("G5")->getFont()->setSize(8);

//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(7);
$object->getActiveSheet()->getColumnDimension('M')->setWidth(21);

//set Height Row
$object->getActiveSheet()->getRowDimension('2')->setRowHeight(8);
$object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(30);

//set font bold
$object->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:M1');
$object->getActiveSheet()->mergeCells('G3:I3');
$object->getActiveSheet()->mergeCells('G4:I4');
$object->getActiveSheet()->mergeCells('G5:I5');
$object->getActiveSheet()->mergeCells('G6:I6');
$object->getActiveSheet()->mergeCells('E7:G7');
$object->getActiveSheet()->mergeCells('E8:G8');
$object->getActiveSheet()->mergeCells('E9:G9');
$object->getActiveSheet()->mergeCells('E10:G10');
$object->getActiveSheet()->mergeCells('I7:K7');
$object->getActiveSheet()->mergeCells('I8:K8');
$object->getActiveSheet()->mergeCells('I9:K9');
$object->getActiveSheet()->mergeCells('I10:K10');
$object->getActiveSheet()->mergeCells('B'.$headerTable.':K'.$headerTable);

//set colum text center
$object->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$object->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$object->getActiveSheet()->getStyle('G3:G6')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
$object->getActiveSheet()->getStyle('B'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

// set Border
$object->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->applyFromArray($style_row);
$object->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->getFont()->setBold(true);

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
->setCellValue('A1', 'Verifikasi Rapor Mutu '.$detail['sekolah']->nama.'')
->setCellValue('G3', 'Rapor Mutu '.$detail['sekolah']->nama.'')
->setCellValue('G4', number_format($nilai, 2, '.', ''))
->setCellValue('G5', 'Predikat : '.$predikat)
->setCellValue('G6', $bintang)
->setCellValue('E7', 'Isi : '.                    number_format((float)$indikator[1]->r18, 2, '.', ''))
->setCellValue('E8', 'Pembiayaan : '.             number_format((float)$indikator[7]->r18, 2, '.', ''))
->setCellValue('E9', 'Pengelolaan Pendidikan : '. number_format((float)$indikator[6]->r18, 2, '.', ''))
->setCellValue('E10', 'Proses : '.                number_format((float)$indikator[2]->r18, 2, '.', ''))
->setCellValue('I7', 'Kompetensi Lulusan : '.     number_format((float)$indikator[0]->r18, 2, '.', ''))
->setCellValue('I8', 'PTK : '.                    number_format((float)$indikator[4]->r18, 2, '.', ''))
->setCellValue('I9', 'Penilaian Pendidikan : '.   number_format((float)$indikator[3]->r18, 2, '.', ''))
->setCellValue('I10', 'Sarpras Pendidikan : '.    number_format((float)$indikator[5]->r18, 2, '.', ''))
->setCellValue('A'.$headerTable, 'No')
->setCellValue('B'.$headerTable, 'Standar / Indikator / Sub Indokator')
->setCellValue('L'.$headerTable, 'Nilai')
->setCellValue('M'.$headerTable, 'Vertifikasi');

// Table
$num = 13;
// $no = '1';
$ex = $object->setActiveSheetIndex(0);

foreach ($indikator as $value) {
  $ex->setCellValue("A".$num, $value->nomor);
  $ex->setCellValue("B".$num, $value->uraian);
  $ex->setCellValue("L".$num, number_format((float)$value->r18, 2, '.', ''));

  $object->getActiveSheet()->mergeCells('B'.$num.':K'.$num);
  $ex->getStyle('A'.$num.':M'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('A'.$num.':B'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
  $object->getActiveSheet()->getStyle('A'.$num.':M'.$num)->applyFromArray($style_row);
  $object->getActiveSheet()->getStyle('A'.$num.':M'.$num)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => '03A9F4'))));
  $num ++;
  
  if(count($value->children) != 0){
    foreach ($value->children as $children) {
      $ex->setCellValue("A".$num, $children->nomor);
      $ex->setCellValue("B".$num, $children->uraian);
      $ex->setCellValue("L".$num, number_format((float)$children->r18, 2, '.', ''));

      $object->getActiveSheet()->mergeCells('B'.$num.':K'.$num);
      $ex->getStyle('A'.$num.':M'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
      $ex->getStyle('A'.$num.':B'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
      $object->getActiveSheet()->getStyle('A'.$num.':M'.$num)->applyFromArray($style_row);
      $object->getActiveSheet()->getStyle('A'.$num.':M'.$num)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'E1F5FE'))));
      $num ++;

      if(count($children->children) != 0){
        foreach ($children->children as $grandchildren) {
          $ex->setCellValue("A".$num, $grandchildren->nomor);
          $ex->setCellValue("B".$num, $grandchildren->uraian);
          $ex->setCellValue("L".$num, number_format((float)$grandchildren->r18, 2, '.', ''));
          $ex->setCellValue("M".$num, $grandchildren->verifikasi_pengawas_id === null ? 'Belum Divertifikasi' : $grandchildren->verifikasi_pengawas_id);

          $object->getActiveSheet()->mergeCells('B'.$num.':K'.$num);
          $ex->getStyle('A'.$num.':M'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
          $ex->getStyle('A'.$num.':B'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
          $object->getActiveSheet()->getStyle('A'.$num.':M'.$num)->applyFromArray($style_row);
          $object->getActiveSheet()->getStyle('A'.$num.':M'.$num)->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'F1F8E9'))));
          $num ++;
        }
      }
    }
  }
}

$object->getActiveSheet()->getStyle('A12:M'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Verval Rapor Mutu '.$detail['sekolah']->nama.'.xlsx"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
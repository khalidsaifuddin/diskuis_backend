<?php

$data = $return['data'];
$headerTable = 3;
$nama_pengguna = "";
$bulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "November", "Desember"];

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
$object->getActiveSheet()->getStyle("A1")->getFont()->setSize(16);

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

$color_riwayat = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => '70D52B')
  ),
);

$color_rencana = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => 'D9D9D9')
  ),
);

$isi_table = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => 'FDE9D9')
  ),
);


$object->getActiveSheet()->getStyle('A1:F1')->applyFromArray($style_header);
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->applyFromArray($table_header);

//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(14);
$object->getActiveSheet()->getColumnDimension('C')->setWidth(35);
$object->getActiveSheet()->getColumnDimension('D')->setWidth(19);
$object->getActiveSheet()->getColumnDimension('E')->setWidth(19);
$object->getActiveSheet()->getColumnDimension('F')->setWidth(22);

//set Height Row
$object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(23);

//set font bold
$object->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:F1');

//set colum text center
// $object->getActiveSheet()->getStyle('A')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
$object->getActiveSheet()->getStyle('A1:D2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$ex = $object->setActiveSheetIndex(0);

$object->setActiveSheetIndex(0)
->setCellValue('A1', 'Hasil Supervisi Pengawas')
->setCellValue('A'.$headerTable, 'No')
->setCellValue('B'.$headerTable, 'NPSN')
->setCellValue('C'.$headerTable, 'Nama Sekolah')
->setCellValue('D'.$headerTable, 'Tanggal Kunjungan')
->setCellValue('E'.$headerTable, 'Waktu Kunjungan')
->setCellValue('F'.$headerTable, 'Jenis Kunjungan');

$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->applyFromArray($style_row);
$object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->getFont()->setBold(true);

//Table
$num = $headerTable + 1;
$no = '1';
foreach ($data as $value) {
  if($no % 2 == 0){
    $object->getActiveSheet()->getStyle('A'.$num.':F'.$num)->applyFromArray($isi_table);
  }

  switch ($value->jenis_kunjungan_id) {
    case '1':
      $status = "Riwayat Kunjungan";
      $object->getActiveSheet()->getStyle('F'.$num)->applyFromArray($color_riwayat);
    break;
    case '2':
      $status = "Rencana Kunjungan";
      $object->getActiveSheet()->getStyle('F'.$num)->applyFromArray($color_rencana);
    break;
    
    default: $status = ""; break;
  }

  $nama_pengguna = $value->nama_pengguna;

  $ex->setCellValue("A".$num, $no);
  $ex->setCellValue("B".$num, $value->npsn);
  $ex->setCellValue("C".$num, $value->nama);
  $ex->setCellValue("D".$num, date("d F Y", strtotime($value->tanggal)));
  $ex->setCellValue("E".$num, date("d F Y", strtotime($value->create_date)));
  $ex->setCellValue("F".$num, $status);

  $ex->getStyle('A'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('F'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('B'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
  $object->getActiveSheet()->getStyle('A'.$num.':F'.$num)->applyFromArray($style_row);
  $object->getActiveSheet()->getStyle('A'.$num.':F'.$num)->getFont()->setSize(10);

  $num ++;
  $no++;
}

$object->getActiveSheet()->getStyle('A1:G'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Hasil Supervisi Pengawas '.$nama_pengguna.'.xlsx"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
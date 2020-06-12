<?php
$pengawas = $return['pengawas'];
$kunjungan_supervisi = $return['kunjungan_supervisi']['rows'];

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


$object->getActiveSheet()->getStyle('A1:H1')->applyFromArray($style_header);
$object->getActiveSheet()->getStyle('A'.$headerTable.':H'.$headerTable)->applyFromArray($table_header);

//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(4);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(10);
$object->getActiveSheet()->getColumnDimension('C')->setWidth(30);
$object->getActiveSheet()->getColumnDimension('D')->setWidth(35);
$object->getActiveSheet()->getColumnDimension('E')->setWidth(12);
$object->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('H')->setWidth(7);

//set Height Row
$object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(30);

//set font bold
// $object->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:C1');
$object->getActiveSheet()->mergeCells('A3:B3');
$object->getActiveSheet()->mergeCells('A4:B4');
$object->getActiveSheet()->mergeCells('A5:B5');
$object->getActiveSheet()->mergeCells('A6:B6');

//set colum text center
$object->getActiveSheet()->getStyle('A'.$headerTable.':H'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
$object->getActiveSheet()->getStyle('A1:H1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

$ex = $object->setActiveSheetIndex(0);

$object->setActiveSheetIndex(0)
->setCellValue('A1', 'KUNJUNGAN SUPERVISI SEKOLAH')
->setCellValue('A3', 'Nama')
->setCellValue('C3', ': '.$pengawas['nama'])
->setCellValue('A4', 'NPTK')
->setCellValue('C4', ': '.$pengawas['nuptk'])
->setCellValue('A5', 'Jenis PTK')
->setCellValue('C5', ': '.$pengawas['bidang_studi_id_str'])
->setCellValue('A6', 'Wilayah')
->setCellValue('C6', ': '.$pengawas['kabupaten'].', '.$pengawas['provinsi'])
->setCellValue('A'.$headerTable, 'No')
->setCellValue('B'.$headerTable, 'NPSN')
->setCellValue('C'.$headerTable, 'Nama Sekolah')
->setCellValue('D'.$headerTable, 'Alamat')
->setCellValue('E'.$headerTable, 'Jumlah Kunjungan')
->setCellValue('F'.$headerTable, 'Kunjungan Terakhir')
->setCellValue('G'.$headerTable, 'Rencana Kunjungan')
->setCellValue('H'.$headerTable, 'Status');

$object->getActiveSheet()->getStyle('A'.$headerTable.':H'.$headerTable)->applyFromArray($style_row);

//Table
$num = $headerTable + 1;
$no='1';
foreach ($kunjungan_supervisi as $value) {
  if($value->jumlah_kunjungan >= 1){
    $Status = "OKE";
  }else{
    $Status = "Gagal";
  }

  $ex->setCellValue("A".$num, $no);
  $ex->setCellValue("B".$num, $value->npsn);
  $ex->setCellValue("C".$num, $value->nama);
  $ex->setCellValue("D".$num, $value->kecamatan." ".$value->kabupaten);
  $ex->setCellValue("E".$num, intval($value->jumlah_kunjungan));
  $ex->setCellValue("F".$num, $value->tanggal_kunjungan !== null ? date("Y-m-d", strtotime($value->tanggal_kunjungan)) : "Belum ada");
  $ex->setCellValue("G".$num, $value->tanggal_rencana_berikutnya !== null ? date("Y-m-d", strtotime($value->tanggal_rencana_berikutnya)) : "Belum ada" );
  $ex->setCellValue("H".$num, $Status);

  $ex->getStyle('A'.$num.':H'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  $ex->getStyle('B'.$num.':D'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
  $object->getActiveSheet()->getStyle('A'.$num.':H'.$num)->applyFromArray($style_row);
  $object->getActiveSheet()->getStyle('A'.$num.':H'.$num)->getFont()->setSize(9);
  
  if($value->tanggal_kunjungan === null){
    $object->getActiveSheet()->getStyle('F'.$num)->getFont()->setItalic(true);
  }
  
  if($value->tanggal_rencana_berikutnya === null){
    $object->getActiveSheet()->getStyle('G'.$num)->getFont()->setItalic(true);
  }

  $num ++;
  $no++;
}

$object->getActiveSheet()->getStyle('A1:H'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Kunjungan Supervisi Sekolah Pengawas '.$pengawas['nama'].'.xlsx"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
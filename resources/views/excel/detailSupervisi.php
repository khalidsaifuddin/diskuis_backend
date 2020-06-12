<?php
$return = $return['data'][0];

$supervisi_pengawas_id          = $return->supervisi_pengawas_id;
$pengguna_id                    = $return->pengguna_id;
$sekolah_id                     = $return->sekolah_id;
$tanggal                        = date("d F Y", strtotime($return->tanggal));
$hasil_pengamatan               = $return->hasil_pengamatan;
$analisis_hasil                 = $return->analisis_hasil;
$berkas_pendukung               = $return->berkas_pendukung;
$create_date                    = $return->create_date;
$last_update                    = $return->last_update;
$soft_delete                    = $return->soft_delete;
$updater_id                     = $return->updater_id;
$last_sync                      = $return->last_sync;
$solusi                         = $return->solusi;
$aktivitas_pendampingan         = $return->aktivitas_pendampingan;
$jenis_formulir_supervisi_id    = $return->jenis_formulir_supervisi_id;
$induk_supervisi_pengawas_id    = $return->induk_supervisi_pengawas_id;
$jenis_kunjungan_id             = $return->jenis_kunjungan_id;
$nama                           = $return->nama;
$npsn                           = $return->npsn;
$nama_pengguna                  = $return->nama_pengguna;
$children                       = $return->children;

$headerTable = 8;

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

$style_row_top = array(
  'borders' => array(
    'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
  )
);

$style_row_left = array(
  'borders' => array(
    'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
  )
);

$style_row_bottom = array(
  'borders' => array(
    'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
  )
);

$style_row_right = array(
  'borders' => array(
    'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
  )
);

$object->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

//set FontSize
$object->getActiveSheet()->getStyle("A1")->getFont()->setSize(14);
$object->getActiveSheet()->getStyle("A3")->getFont()->setSize(14);
$object->getActiveSheet()->getStyle("A4")->getFont()->setSize(10);
$object->getActiveSheet()->getStyle("A5")->getFont()->setSize(10);
$object->getActiveSheet()->getStyle("A7")->getFont()->setSize(12);

// $styleArray = array(
//     'font'  => array(
//         'color' => array('rgb' => 'FFFFFF'),
//     ));
// $object->getActiveSheet()->getStyle('F6:J14')->applyFromArray($styleArray);

//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(2);

//set Height Row
$object->getActiveSheet()->getRowDimension('8')->setRowHeight(8);

//set font bold
$object->getActiveSheet()->getStyle('A1')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A3')->getFont()->setBold(true);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:J1');
$object->getActiveSheet()->mergeCells('A3:E3');
$object->getActiveSheet()->mergeCells('A4:E4');
$object->getActiveSheet()->mergeCells('A5:E5');
$object->getActiveSheet()->mergeCells('A7:J7');
$object->getActiveSheet()->mergeCells('A8:B8');

//set colum text center
// $object->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// $object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
// $object->getActiveSheet()->getStyle('B7:B10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
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
->setCellValue('A1', 'DETAIL SUPERVISI')
->setCellValue('A3', 'Supervisi tanggal '.$tanggal)
->setCellValue('A4', 'Sekolah yang dikunjungi : '.$nama)
->setCellValue('A5', 'Nama Pengawas : '.$nama_pengguna)
->setCellValue('A7', 'berkunjung');


//Border Table
// $object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->applyFromArray($style_row);
// $object->getActiveSheet()->getStyle('A'.$headerTable.':F'.$headerTable)->getFont()->setBold(true);

// Table
$num = $headerTable + 1;
$jam = "9";
foreach ($children as $value) {

  $ex->setCellValue("B".$num, $value->nama_formulir);
  $ex->setCellValue("B".($num+2), "Hasil Pengawatan");
  $ex->setCellValue("B".($num+3), $value->hasil_pengamatan === null ? "-" : $value->hasil_pengamatan);
  $ex->setCellValue("B".($num+5), "Analisis Hasil");
  $ex->setCellValue("B".($num+6), $value->analisis_hasil === null ? "-" : $value->analisis_hasil);
  $ex->setCellValue("B".($num+8), "Solusi");
  $ex->setCellValue("B".($num+9), $value->solusi === null ? "-" : $value->solusi);
  $ex->setCellValue("B".($num+11), "Aktivitas Pendampingan");
  $ex->setCellValue("B".($num+12), $value->aktivitas_pendampingan === null ? "-" : $value->aktivitas_pendampingan);
  $ex->setCellValue("B".($num+13), "Berkas Pendukung");
  $ex->setCellValue("B".($num+14), $value->berkas_pendukung === null ? "-" : $value->berkas_pendukung);

  $object->getActiveSheet()->getRowDimension($num + 1)->setRowHeight(4);
  
  // $ex->getStyle('A'.$num.':F'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
  // $ex->getStyle('B'.$num)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
  $object->getActiveSheet()->getStyle('B'.$num.':J'.$num)->applyFromArray($style_row_top);
  $object->getActiveSheet()->getStyle('B'.$num.':B'.($num + 14))->applyFromArray($style_row_left);
  $object->getActiveSheet()->getStyle('J'.$num.':J'.($num + 14))->applyFromArray($style_row_right);
  $object->getActiveSheet()->getStyle('B'.($num + 14).':J'.($num + 14))->applyFromArray($style_row_bottom);

  $num = $num + 16;
}

// $object->getActiveSheet()->getStyle('A1:F'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true);

// /--------------

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Detail Supervisi Pengawas '.$nama.'.xlsx"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
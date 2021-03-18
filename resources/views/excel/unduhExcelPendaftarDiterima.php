<?php
$dataObj = $return;

// return $dataObj;die;

$arrAlfabet = array('','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

// echo json_encode($dataObj[0]->kuis);die;
// $Sekolah = $sekolah;
$headerTable = 3;
// $logo = "lumajang.png";

/** PHPExcel */
require_once ('Classes/PHPExcel.php');
// Create new PHPExcel object
$filePath = __DIR__.'/template_data_pd.xlsx';
$object = PHPExcel_IOFactory::load($filePath);

// $object = new PHPExcel();

//style untuk row
$style_row = array(
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER, // Set text jadi di tengah secara vertical (middle)
    'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi di tengah secara vertical (middle)
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
$object->getActiveSheet()->getPageSetup()->setFitToWidth(1);

//set FontSize
$object->getActiveSheet()->getStyle("A1")->getFont()->setSize(14);

// Style
$style_header = array(
  'fill' => array(
      'type' => PHPExcel_Style_Fill::FILL_SOLID,
      'color' => array('rgb' => 'A6A6A6')
  ),
  'font'  => array(
      'color' => array('rgb' => '000000'),
  ),
  'alignment' => array(
    'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
  ),
);

$ex = $object->setActiveSheetIndex(0);

$num = $headerTable;
$no='1';

foreach ($dataObj as $value) {
    if($value->status_diterima_id > 0){
      $ex->setCellValue("A".$num, $no);
      $ex->setCellValue("B".$num, $value->nama);
      $ex->setCellValue("C".$num, $value->nik ? "'".(String)$value->nik : "");
      $ex->setCellValue("D".$num, $value->jenis_kelamin);
      $ex->setCellValue("E".$num, $value->tempat_lahir);
      $ex->setCellValue("F".$num, $value->tanggal_lahir);
      $ex->setCellValue("G".$num, $value->alamat_tempat_tinggal);
      $ex->setCellValue("H".$num, $value->rt);
      $ex->setCellValue("I".$num, $value->rw);
      $ex->setCellValue("J".$num, $value->desa_kelurahan);
      $ex->setCellValue("K".$num, $value->kecamatan);
      $ex->setCellValue("L".$num, $value->kabupaten);
      $ex->setCellValue("M".$num, $value->provinsi);
      $ex->setCellValue("N".$num, $value->kode_pos);
      $ex->setCellValue("O".$num, $value->lintang);
      $ex->setCellValue("P".$num, $value->bujur);
      $ex->setCellValue("Q".$num, $value->nisn);
      $ex->setCellValue("R".$num, $value->asal_sekolah);
      $ex->setCellValue("S".$num, $value->asal_sekolah_id);
      $ex->setCellValue("T".$num, $value->orang_tua_utama);
      $ex->setCellValue("U".$num, $value->nama_ibu);
      $ex->setCellValue("V".$num, $value->tempat_lahir_ibu);
      $ex->setCellValue("W".$num, $value->tanggal_lahir_ibu);
      $ex->setCellValue("X".$num, $value->no_telepon_ibu);
      $ex->setCellValue("Y".$num, $value->nama_ayah);
      $ex->setCellValue("Z".$num, $value->tempat_lahir_ayah);
      $ex->setCellValue("AA".$num, $value->tanggal_lahir_ayah);
      $ex->setCellValue("AB".$num, $value->no_telepon_ayah);
      $ex->setCellValue("AC".$num, $value->nama_wali);
      $ex->setCellValue("AD".$num, $value->tempat_lahir_wali);
      $ex->setCellValue("AE".$num, $value->tanggal_lahir_wali);
      $ex->setCellValue("AF".$num, $value->no_telepon_wali);
      $ex->setCellValue("AG".$num, $value->jalur);
      $ex->setCellValue("AH".$num, $value->jalur_id);
      $ex->setCellValue("AI".$num, ($value->status_konfirmasi_id === 1 ? 'Terkonfirmasi' : 'Belum Terkonfirmasi'));
      $ex->setCellValue("AJ".$num, ($value->status_diterima_id === 1 ? 'Diterima' : ($value->status_diterima_id === 2 ? 'Telah Daftar Ulang' : ($value->status_diterima_id === 3 ? 'Cabut Berkas' : '-'))));
      // $ex->setCellValue("AK".$num, $value->status_diterima_id);
      
      $num++;
      $no++;
    }
      
}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="daftar-pd-'.date('Y-m-d H:i:s').'.xls"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel5');
// $data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
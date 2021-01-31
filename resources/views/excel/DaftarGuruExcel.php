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
$filePath = __DIR__.'/template_unduh_ptk.xlsx';
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
    
      $ex->setCellValue("A".$num, $no);
      $ex->setCellValue("B".$num, $value->nama);
      $ex->setCellValue("C".$num, $value->nik ? "'".(String)$value->nik : "");
      $ex->setCellValue("D".$num, $value->no_kk ? "'".(String)$value->no_kk : "");
      $ex->setCellValue("E".$num, $value->jenis_kelamin);
      $ex->setCellValue("F".$num, $value->tempat_lahir);
      $ex->setCellValue("G".$num, $value->tanggal_lahir);
      $ex->setCellValue("H".$num, $value->nama_ibu_kandung);
      $ex->setCellValue("I".$num, $value->agama);
      $ex->setCellValue("J".$num, $value->alamat);
      $ex->setCellValue("K".$num, $value->rt);
      $ex->setCellValue("L".$num, $value->rw);
      $ex->setCellValue("M".$num, $value->nama_dusun);
      $ex->setCellValue("N".$num, $value->desa_kelurahan);
      $ex->setCellValue("O".$num, $value->kecamatan);
      $ex->setCellValue("P".$num, $value->kabupaten);
      $ex->setCellValue("Q".$num, $value->provinsi);
      $ex->setCellValue("R".$num, $value->kode_pos);
      $ex->setCellValue("S".$num, $value->npwp);
      $ex->setCellValue("T".$num, $value->nama_wajib_pajak);
      $ex->setCellValue("U".$num, $value->status_perkawinan);
      $ex->setCellValue("V".$num, $value->status_kepegawaian);
      $ex->setCellValue("W".$num, '');
      $ex->setCellValue("X".$num, $value->nip);
      $ex->setCellValue("Y".$num, $value->niy);
      $ex->setCellValue("X".$num, '');
      $ex->setCellValue("AA".$num, $value->nuptk ? "'".$value->nuptk : "");
      $ex->setCellValue("AB".$num, $value->npk);
      $ex->setCellValue("AC".$num, $value->jenis_guru);
      $ex->setCellValue("AD".$num, $value->sk_pengangkatan);
      $ex->setCellValue("AE".$num, $value->tmt_pengangkatan);
      $ex->setCellValue("AF".$num, $value->lembaga_pengangkat);
      $ex->setCellValue("AG".$num, $value->sk_cpns);
      $ex->setCellValue("AH".$num, $value->tmt_cpns);
      $ex->setCellValue("AI".$num, $value->masa_kerja." Tahun");
      $ex->setCellValue("AJ".$num, $value->username);
      $ex->setCellValue("AK".$num, $value->no_hp);
    
      $num++;
      $no++;
}

// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$object->setActiveSheetIndex(0);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="daftar-guru-'.date('Y-m-d H:i:s').'.xls"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel5');
// $data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
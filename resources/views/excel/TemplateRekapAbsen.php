<?php
$dataObj = $return;
$ruang_id = $ruang_id;
$nama_ruang = $nama_ruang;
// $hari_terpilih = json_decode($hari_terpilih);
$hari_terpilih = json_decode($hari_terpilih);

// echo var_dump(json_decode($hari_terpilih));die;

$arrAlfabet = array('','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');

$headerTable = 4;

/** PHPExcel */
require_once ('Classes/PHPExcel.php');
// Create new PHPExcel object
// $object = new PHPExcel();
$filePath = __DIR__.'/template_rekap_absensi.xlsx';
$object = PHPExcel_IOFactory::load($filePath);

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

$styleArrayBorder = array(
  'borders' => array(
      'allborders' => array(
          'style' => PHPExcel_Style_Border::BORDER_THIN
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

// //set Width Colum
// $object->getActiveSheet()->getColumnDimension('A')->setWidth(10);
// $object->getActiveSheet()->getColumnDimension('B')->setWidth(40);
// $object->getActiveSheet()->getColumnDimension('C')->setWidth(40);
// $object->getActiveSheet()->getColumnDimension('D')->setWidth(40);
// $object->getActiveSheet()->getColumnDimension('E')->setWidth(40);
// $object->getActiveSheet()->getColumnDimension('F')->setWidth(40);
// $object->getActiveSheet()->getColumnDimension('G')->setWidth(40);
// $object->getActiveSheet()->getColumnDimension('H')->setWidth(40);
// $object->getActiveSheet()->getColumnDimension('I')->setWidth(40);
// $object->getActiveSheet()->getColumnDimension('J')->setWidth(40);
// $object->getActiveSheet()->getColumnDimension('K')->setWidth(10);
// $object->getActiveSheet()->getColumnDimension('L')->setWidth(10);
// $object->getActiveSheet()->getColumnDimension('M')->setWidth(10);

// //set font bold
// $object->getActiveSheet()->getStyle('B2')->getFont()->setBold(true);
// $object->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->getFont()->setBold(true);

// $object->getActiveSheet()->getStyle('A'.$headerTable.':M'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

$ex = $object->setActiveSheetIndex(0);

// $object->setActiveSheetIndex(0)
// ->setCellValue('A1', 'Laporan Hasil Pengisan Kuis')
// ->setCellValue('A'.$headerTable, 'Kode')
// ->setCellValue('B'.$headerTable, 'Pertanyaan')
// ->setCellValue('C'.$headerTable, 'Pilihan Jawaban')
// ->setCellValue('D'.$headerTable, 'Isian')
// ->setCellValue("E".$headerTable, 'jawaban_kuis_id')
// ->setCellValue("f".$headerTable, 'pertanyaan_kuis_id')
// ->setCellValue("G".$headerTable, 'pilihan_pertanyaan_kuis_id')
// ->setCellValue("H".$headerTable, 'kuis_id')
// ->setCellValue("I".$headerTable, 'sesi_kuis_id')
// ;

// $ex->getStyle('A1')->getFont()->setBold(true);
// $ex->getStyle('B1')->getFont()->setBold(true);

// $ex->mergeCells('A2:F2');

// //judul
$kolom_judul_max = 2;

for ($iHari=0; $iHari < sizeof($hari_terpilih); $iHari++) { 
  $ex->setCellValueByColumnAndRow(($iHari+2),4, $hari_terpilih[$iHari]);
  $object->getActiveSheet()->getColumnDimension($arrAlfabet[($iHari+3)])->setWidth(15);
  $kolom_judul_max++;
}

$object->getActiveSheet()->mergeCells('C3:'.$arrAlfabet[$kolom_judul_max].'3');

$ex->setCellValueByColumnAndRow(($kolom_judul_max),3, 'Jumlah Kehadiran');
$object->getActiveSheet()->mergeCells($arrAlfabet[($kolom_judul_max+1)].'3:'.$arrAlfabet[($kolom_judul_max+1)].'4');
$object->getActiveSheet()->getColumnDimension($arrAlfabet[($kolom_judul_max+1)])->setWidth(15);
$ex->setCellValueByColumnAndRow(($kolom_judul_max)+1,3, 'Persentase Kehadiran (%)');
$object->getActiveSheet()->mergeCells($arrAlfabet[($kolom_judul_max+2)].'3:'.$arrAlfabet[($kolom_judul_max+2)].'4');
$object->getActiveSheet()->getColumnDimension($arrAlfabet[($kolom_judul_max+2)])->setWidth(15);


// $object->getActiveSheet()->mergeCells('A1:'.$arrAlfabet[($kolom_judul_max+2)].$kolom_judul_max);

// echo $kolom_judul_max;die;

// if(sizeof($dataObj) > 0){
  //   // setCellValueByColumnAndRow
  //   for ($iJudul=0; $iJudul < sizeof($dataObj[0]->kuis); $iJudul++) { 
    //     // $dataObj[0]->kuis[$iJudul]->judul
    //     $ex->setCellValueByColumnAndRow(($iJudul+2),4, $dataObj[0]->kuis[$iJudul]['judul']);
    //   }
    
    //   $ex->getStyle($arrAlfabet[3].'4'.':'.$arrAlfabet[($iJudul+2)].'4')->getFont()->setBold(true);
    
    //   $ex->getStyle($arrAlfabet[3].'4'.':'.$arrAlfabet[($iJudul+2)].'4')->applyFromArray($style_row);
    //   $ex->getStyle($arrAlfabet[1].'3'.':'.$arrAlfabet[($iJudul+2)].'4')->applyFromArray($style_row);
    
    //   $ex->setCellValue("C3", 'Judul Kuis');
    //   $object->getActiveSheet()->mergeCells('C3:'.$arrAlfabet[($iJudul+2)].'3');
    // }
    
    // // //Table
    $num = $headerTable + 1;
    $no='1';
    foreach ($dataObj as $value) {
      
      $ex->setCellValue("A".$num, $value->no_absen ? $value->no_absen : $no);
      $ex->setCellValue("B".$num, $value->nama);
      
      for ($ii=0; $ii < sizeof($hari_terpilih); $ii++) { 
        $ex->setCellValue($arrAlfabet[($ii+3)].$num, $value->{'masuk_'.str_replace("-","",$hari_terpilih[$ii])} > 0 ? $value->{'masuk_'.str_replace("-","",$hari_terpilih[$ii])} : 0);
      }
      // $ex->setCellValue("C".$num, 1);
      // $ex->setCellValue("D".$num, 1);
      // $ex->setCellValue("E".$num, 1);
      $ex->setCellValue("Z".$num, $kolom_judul_max);
      
      $ex->setCellValueByColumnAndRow(($kolom_judul_max),$num, '=SUM(C'.$num.':'.$arrAlfabet[(sizeof($hari_terpilih)+2)].$num.')');
      $ex->setCellValueByColumnAndRow(($kolom_judul_max+1),$num, '=(SUM(C'.$num.':'.$arrAlfabet[(sizeof($hari_terpilih)+2)].$num.')/'.sizeof($hari_terpilih).'*100)');
      
      $num ++;
      $no++;
    }
    
    $ex->setCellValueByColumnAndRow(($kolom_judul_max+1),2, $nama_ruang);
    
    $ex->getStyle('A3'.':'.$arrAlfabet[($kolom_judul_max+2)].($num-1))->applyFromArray($styleArrayBorder);
    $ex->mergeCells('A1:'.$arrAlfabet[($kolom_judul_max+2)].'1');
    
    // $object->getActiveSheet()->getStyle('A1:M'.$object->getActiveSheet()->getHighestRow())->getAlignment()->setWrapText(true); 
    
    // /--------------
    
    // Set active sheet index to the first sheet, so Excel opens this as the first sheet
    $object->setActiveSheetIndex(0);
    
    // Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="hasil_pengisian_kuis-'.date('Y-m-d H:i:s').'.xls"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel5');
// $data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;
?>
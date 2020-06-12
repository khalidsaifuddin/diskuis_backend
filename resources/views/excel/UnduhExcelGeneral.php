<?php
$headerTable = 5;

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
$object->getActiveSheet()->getStyle("A1")->getFont()->setSize(23);
$object->getActiveSheet()->getStyle("A2")->getFont()->setSize(20);


//set Width Colum
$object->getActiveSheet()->getColumnDimension('A')->setWidth(5);
$object->getActiveSheet()->getColumnDimension('B')->setWidth(35);

//set Height Row
$object->getActiveSheet()->getRowDimension('1')->setRowHeight(24);
$object->getActiveSheet()->getRowDimension('6')->setRowHeight(1);
$object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(20);

//set margeCell
$object->getActiveSheet()->mergeCells('A1:F1');
$object->getActiveSheet()->mergeCells('A2:B2');
$object->getActiveSheet()->mergeCells('A3:F3');
$object->getActiveSheet()->mergeCells('A4:F4');

//set colum text center
$object->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

// $object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//set colom text LEFT
// $object->getActiveSheet()->getStyle('B7:B10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
// $ex = $object->setActiveSheetIndex(0);


$object->setActiveSheetIndex(0)
->setCellValue('A1', @$judul)
->setCellValue('A2', @$sub_judul)
->setCellValue('A3', 'Tanggal Unduh: '.date('Y-m-d H:i:s'))
;

$iJudul = 0;
foreach ($return['rows'][0] as $key => $value) {

    if($key != 'id_level_wilayah' && $key != 'id_level_wilayah_kabupaten' && $key != 'id_level_wilayah_provinsi' && $key != 'id_level_wilayah_kecamatan' && $key != 'sekolah_id'){

        $judulHeader = ucwords( str_replace( "id", "", str_replace( "_", " ", $key ) ) );
    
        $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( $iJudul, 5, $judulHeader );
        $iJudul++;

    }

}

for ($i=0; $i < sizeof($return['rows']); $i++) {

    $iKolom = 0;

    foreach ($return['rows'][$i] as $key => $value) {
        
      if($key != 'id_level_wilayah' && $key != 'id_level_wilayah_kabupaten' && $key != 'id_level_wilayah_provinsi' && $key != 'id_level_wilayah_kecamatan' && $key != 'sekolah_id'){

            $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( $iKolom, ($i+6), $value );
    
            $iKolom++;
        
        }
    
    }
    
}

//set font bold
$object->getActiveSheet()->getStyle('A1:Z1')->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A'.$headerTable.':Z'.$headerTable)->getFont()->setBold(true);
$object->getActiveSheet()->getStyle('A'.$headerTable.':Z'.$headerTable)->getFont()->setSize(14);

$object->getActiveSheet()->getColumnDimension('A')->setWidth(10);
$object->getActiveSheet()->getColumnDimension('C')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('J')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('K')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('L')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('M')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('N')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('O')->setWidth(15);
$object->getActiveSheet()->getColumnDimension('P')->setWidth(15);

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.@$judul.'-'.@$sub_judul.'-'.date('Y-m-d H:i:s').'.xlsx');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;

?>
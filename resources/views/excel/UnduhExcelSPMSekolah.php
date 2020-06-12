<?php
$headerTable = 5;

/** PHPExcel */
require_once ('Classes/PHPExcel.php');
// Create new PHPExcel object
// $object = new PHPExcel();
$object = PHPExcel_IOFactory::load(__DIR__.'/SPM.xlsx');


// //style untuk colomn
// $style_col = array(
//     'font' => array('bold' => true), // Set font nya jadi bold
//     'alignment' => array(
//       'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER, // Set text jadi ditengah secara horizontal (center)
//       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
//     ),
//     'borders' => array(
//       'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
//       'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
//       'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
//       'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
//     )
// );

$style_col_induk = array(
    'font' => array('bold' => true)
);


// //style untuk row
// $style_row = array(
//     'alignment' => array(
//       'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER // Set text jadi di tengah secara vertical (middle)
//     ),
//     'borders' => array(
//       'top' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border top dengan garis tipis
//       'right' => array('style'  => PHPExcel_Style_Border::BORDER_THIN),  // Set border right dengan garis tipis
//       'bottom' => array('style'  => PHPExcel_Style_Border::BORDER_THIN), // Set border bottom dengan garis tipis
//       'left' => array('style'  => PHPExcel_Style_Border::BORDER_THIN) // Set border left dengan garis tipis
//     )
// );

// $object->getActiveSheet()->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

// //set FontSize
// $object->getActiveSheet()->getStyle("A1")->getFont()->setSize(23);
// $object->getActiveSheet()->getStyle("A2")->getFont()->setSize(20);


// //set Width Colum
// $object->getActiveSheet()->getColumnDimension('A')->setWidth(5);
// $object->getActiveSheet()->getColumnDimension('B')->setWidth(35);

// //set Height Row
// $object->getActiveSheet()->getRowDimension('1')->setRowHeight(24);
// $object->getActiveSheet()->getRowDimension('6')->setRowHeight(1);
// $object->getActiveSheet()->getRowDimension($headerTable)->setRowHeight(20);

// //set margeCell
// $object->getActiveSheet()->mergeCells('A1:F1');
// $object->getActiveSheet()->mergeCells('A2:B2');
// $object->getActiveSheet()->mergeCells('A3:F3');
// $object->getActiveSheet()->mergeCells('A4:F4');

// //set colum text center
// $object->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

// // $object->getActiveSheet()->getStyle('A'.$headerTable.':I'.$headerTable)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

// //set colom text LEFT
// // $object->getActiveSheet()->getStyle('B7:B10')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
// // $ex = $object->setActiveSheetIndex(0);


$object->setActiveSheetIndex(0)
// ->setCellValue('A1', @$sekolah['rows'][0]->nama)
->setCellValue('A2', '                           '.@$sekolah['rows'][0]->nama."(".@$sekolah['rows'][0]->npsn.")")
->setCellValue('A3', '                           Tanggal Unduh Rapor: '.date('Y-m-d H:i:s'))
->setCellValue('A4', '                           Periode Semester: ' . (int)substr(@$semester_id,0,4) . '/' . ((int)substr(@$semester_id,0,4)+1) .' '.((int)substr(@$semester_id,4,1) == 1 ? 'Ganjil' : 'Genap'))
;

// $object->setActiveSheetIndex(0)
// ->setCellValue( 'A2', 'SMA Dummy 11' )
// ->setCellValue( 'A3', "Tanggal Unduh: ".date('Y-m-d H:i:s') );

$baris = 7;
$persen_rata = 0;
$persen_baris = 0;

$baris_total = 0;
$persen_total = 0;

for ($i=0; $i < sizeof($return['rows']); $i++) {

    $persen_induk = 0;
    $baris_induk = 0;

    // $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 0, ($i+7), ($i+1) );
    $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 0, $baris, $return['rows'][$i]->nama . ". " . $return['rows'][$i]->keterangan );
    $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 3, $baris, $return['rows'][$i]->{'target'} );
    $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 4, $baris, $return['rows'][$i]->{'capaian'} );
    $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 5, $baris, $return['rows'][$i]->{'gap'} );
    $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 6, $baris, $return['rows'][$i]->{'satuan'} );
    // if(property_exists($return['rows'][$i], $return['rows'][$i]->{"capaian"})){
    // $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 1, $baris, $return['rows'][$i]->{"capaian"}.'%' );
    
    
    // if((int)$return['rows'][$i]->{"capaian"} == 100){
    //     $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 2, $baris, 'Tercapai' );
    // }else{
    //     $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 2, $baris, 'Belum Tercapai' );
    // }
    
    $object->setActiveSheetIndex(0)->getCellByColumnAndRow( 0, $baris )->getStyle()->getFont()->setBold( true );
    $object->setActiveSheetIndex(0)->getCellByColumnAndRow( 1, $baris )->getStyle()->getFont()->setBold( true );
    $object->setActiveSheetIndex(0)->getCellByColumnAndRow( 2, $baris )->getStyle()->getFont()->setBold( true );

    $object->setActiveSheetIndex(0)->getCellByColumnAndRow( 0, $baris )->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('EEEEEE');
    $object->setActiveSheetIndex(0)->getCellByColumnAndRow( 1, $baris )->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('EEEEEE');;
    $object->setActiveSheetIndex(0)->getCellByColumnAndRow( 2, $baris )->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('EEEEEE');;
    $object->setActiveSheetIndex(0)->getCellByColumnAndRow( 3, $baris )->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('EEEEEE');;
    $object->setActiveSheetIndex(0)->getCellByColumnAndRow( 4, $baris )->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('EEEEEE');;
    $object->setActiveSheetIndex(0)->getCellByColumnAndRow( 5, $baris )->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('EEEEEE');;
    $object->setActiveSheetIndex(0)->getCellByColumnAndRow( 6, $baris )->getStyle()->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setARGB('EEEEEE');;
    
    // }else{
    //     // $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 1, $baris, json_encode($return['rows'][$i]) );
    // }

    $baris_sekarang = $baris;
    $baris++;
    // $persen_rata = $persen_rata + (int)$return['rows'][$i]->{"capaian"};
    // $persen_baris++;

    if($return['rows'][$i]->anak_total > 0){
        for ($j=0; $j < sizeof($return['rows'][$i]->children['rows']); $j++) { 

            $record = $return['rows'][$i]->children['rows'][$j];
            // return var_dump($record->capaian);die;

            $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 0, $baris, $return['rows'][$i]->children['rows'][$j]->nama . ". " . $return['rows'][$i]->children['rows'][$j]->keterangan );

            if(property_exists($record, 'persen')){
                $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 1, $baris, $record->{'persen'}.'%' );
                // $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 2, $baris, json_encode($record) );

                if((int)$record->{'persen'} == 100){
                    $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 2, $baris, 'Tercapai' );
                }else{
                    $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 2, $baris, 'Belum Tercapai' );
                }

                $persen_induk = $persen_induk + (int)$record->{'persen'};
                $persen_total = $persen_total + (int)$record->{'persen'};

                $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 3, $baris, $record->{'target'} );
                $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 4, $baris, $record->{'capaian'} );
                $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 5, $baris, $record->{'gap'} );
                $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 6, $baris, $record->{'satuan'} );
            }else{
                $persen_induk = $persen_induk + 0;
                $persen_total = $persen_total + 0;
            }

            $baris++;
            $baris_induk++;
            $baris_total++;
        }
    }else{
        $persen_induk = (int)$return['rows'][$i]->{"persen"};
        $baris_induk = 1;

        $persen_total = $persen_total + (int)$return['rows'][$i]->{"persen"};
        $baris_total++;
    }

    $persen_induk = round(($persen_induk / $baris_induk),2);

    $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 1, $baris_sekarang, $persen_induk.'%' );
    
    if((int)$persen_induk == 100){
        $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 2, $baris_sekarang, 'Tercapai' );
    }else{
        $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 2, $baris_sekarang, 'Belum Tercapai' );
    }

    $persen_rata = $persen_rata + (int)$persen_induk;
    $persen_baris++;
}

$persen_rata = $persen_rata / $persen_baris;
$persen_total = $persen_total / $baris_total;

// $object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 4, 2, round($persen_rata,2)."%" );
$object->setActiveSheetIndex(0)->setCellValueByColumnAndRow( 4, 2, round($persen_total,0)."%" );

// Redirect output to a client’s web browser (Excel2007)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="SPM-'.@$sekolah['rows'][0]->nama.'-'.@$sekolah['rows'][0]->npsn.'-'.@$semester_id.'-'.date('Y-m-d H:i:s').'.xlsx"');
$data = PHPExcel_IOFactory::createWriter($object, 'Excel2007');
$data->setIncludeCharts(true);
$data->save('php://output');
exit;

?>
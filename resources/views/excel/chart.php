<?php

/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('Europe/London');

define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');

date_default_timezone_set('Europe/London');

// /**
//  * PHPExcel
//  *
//  * Copyright (C) 2006 - 2014 PHPExcel
//  *
//  * This library is free software; you can redistribute it and/or
//  * modify it under the terms of the GNU Lesser General Public
//  * License as published by the Free Software Foundation; either
//  * version 2.1 of the License, or (at your option) any later version.
//  *
//  * This library is distributed in the hope that it will be useful,
//  * but WITHOUT ANY WARRANTY; without even the implied warranty of
//  * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
//  * Lesser General Public License for more details.
//  *
//  * You should have received a copy of the GNU Lesser General Public
//  * License along with this library; if not, write to the Free Software
//  * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
//  *
//  * @category   PHPExcel
//  * @package    PHPExcel
//  * @copyright  Copyright (c) 2006 - 2014 PHPExcel (http://www.codeplex.com/PHPExcel)
//  * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
//  * @version    ##VERSION##, ##DATE##
//  */

/** PHPExcel */
require_once ('Classes/PHPExcel.php');
$excel = new PHPExcel();
$sheet = $excel->getActiveSheet();
$sheet->setTitle('Report Summary');

// $data = [
//     ['', 'N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'],
//     ['Frequency', 10, 20, 5, 10, 15, 30, 5, 5],
//     ['Arif', 12, 22, 25, 12, 12, 20, 25, 25],
// ];

// $sheet->fromArray($data, null, 'A1');

// $dataseriesLabels = array(
// 	new PHPExcel_Chart_DataSeriesValues('String','sheet!$A$2', NULL, 1),
// 	new PHPExcel_Chart_DataSeriesValues('String','sheet!$A$3', NULL, 1),
// );
// $xAxisTickValues = array(
// 	new PHPExcel_Chart_DataSeriesValues('String','sheet!$B$1:$I$1', NULL, 8),
// 	new PHPExcel_Chart_DataSeriesValues('String','sheet!$B$1:$I$1', NULL, 8),
// );
// $dataSeriesValues = array(
// 	new PHPExcel_Chart_DataSeriesValues('String','sheet!$B$2:$I$2', NULL, 8),
// 	new PHPExcel_Chart_DataSeriesValues('String','sheet!$B$3:$I$3', NULL, 8),
// );

// $series = new PHPExcel_Chart_DataSeries(
//     PHPExcel_Chart_DataSeries::TYPE_RADARCHART,
//     null,
//     range(0, count($dataSeriesValues)-1),
//     $dataseriesLabels,
//     $xAxisTickValues,
//     $dataSeriesValues,
//     null,
//     null,
//     PHPExcel_Chart_DataSeries::STYLE_MARKER
// );
// $plotarea = new PHPExcel_Chart_PlotArea(null, array($series));
// $legend = new PHPExcel_Chart_Legend(PHPExcel_Chart_Legend::POSITION_TOPRIGHT,
//     NULL, false);
// $title = new PHPExcel_Chart_Title('Wind Rose Diagram');
// $chart = new PHPExcel_Chart(
//     'chart1',
//     $title,
//     $legend,
//     $plotarea,
//     true,
//     0,
//     null,
//     null
// );
// $chart->setTopLeftPosition('G6');
// $chart->setBottomRightPosition('J14');
// $sheet->addChart($chart);

// $ExcelWrite = PHPExcel_IOFactory::createWriter($excel, "Excel2007");
// $ExcelWrite->setIncludeCharts(true);
// $ExcelWrite->save('D:\TestRadarChart.xlsx');


//ADD THE REPORT SUMMARY CHART
$rowNum = 10;
$labels = array(
    new PHPExcel_Chart_DataSeriesValues('String', "'Report Summary'!C1", null, 1),
    new PHPExcel_Chart_DataSeriesValues('String', "'Report Summary'!D1", null, 1)
);
$chrtCols = "'Report Summary'!B2:B$rowNum";
$chrtVals = "'Report Summary'!C2:C$rowNum";
$chrtVals2 = "'Report Summary'!D2:D$rowNum";
$periods = new PHPExcel_Chart_DataSeriesValues('String', $chrtCols, null, $rowNum-1);
$values = new PHPExcel_Chart_DataSeriesValues('Number', $chrtVals, null, $rowNum-1);
$values2 = new PHPExcel_Chart_DataSeriesValues('Number', $chrtVals2, null, $rowNum-1);
$series = new PHPExcel_Chart_DataSeries(
    PHPExcel_Chart_DataSeries::TYPE_LINECHART,       
    PHPExcel_Chart_DataSeries::GROUPING_STANDARD,  
    array(0,1),                                       
    $labels,                                       
    array($periods,$periods),                               
    array($values,$values2)                                  
);
$series->setPlotDirection(PHPExcel_Chart_DataSeries::DIRECTION_COL);
$layout = new PHPExcel_Chart_Layout();

$plotarea = new PHPExcel_Chart_PlotArea($layout, array($series));
$chart = new PHPExcel_Chart('sample', null, null, $plotarea);
$chart->setTopLeftPosition('A1', 24, 24);
$chart->setBottomRightPosition('H18', -24);
$sheet->addChart($chart);

$ExcelWrite = PHPExcel_IOFactory::createWriter($excel, "Excel2007");
$ExcelWrite->setIncludeCharts(true);
$ExcelWrite->save('D:\TestRadarChart.xlsx');
?>
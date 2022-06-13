<?php

namespace Sunnysideup\Vardump;


class ArrayToTable
{

    public static function convert(array $array, $maxCols = 20, $maxRows = 200) : string
    {
        $html = '
        <style>
            .vardump-data-table td {padding: 2px;}
        </style>
        <table class="vardump-data-table" border="1" width="100%;">
        ';

        $html .= '<tr>';
        $colCount = 0;
        if (count($array)) {
            $header = $array[0] ?? $array;
            if(is_array($header)) {
                foreach($header as $key => $value){
                    $colCount++;
                    if($colCount > $maxCols) {
                        $html .= '<th>...</th>';
                    } else {
                        $html .= '<th>' . htmlspecialchars($key) . '</th>';
                    }
                }
                $html .= '</tr>';

                // data rows
                $rowCount = 0;
                $hasInnerArray = false;
                $rows = '';
                foreach( $array as $key => $value){
                    if($rowCount < $maxRows) {
                        $rowCount++;
                        $colCount = 0;
                        if( is_array($value)) {
                            $hasInnerArray = true;
                            $rows .= '<tr>';
                            foreach($value as $key2 => $value2){
                                $colCount++;
                                if($colCount > $maxCols) {
                                    $rows .= '<td>...</td>';
                                }
                                $rows .= '<td>' . htmlspecialchars($value2) . '</td>';
                            }
                            $rows .= '</tr>';
                        } else {
                            $rows .= '<td>'.$value.'</td>';
                        }
                    }
                }
                if(! $hasInnerArray) {
                    $rows = '<tr>'.$rows.'</tr>';
                }
                $html .= $rows.'</table>';
                if($rowCount === $maxRows) {
                    $html = '<p>not all rows shown</p>';
                }
            } else {
                $html .= print_r($array, 1);
            }
        } else {
            $html = '<p>no data available</p>';
        }

        return $html;
    }

}

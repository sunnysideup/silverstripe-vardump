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
        <table class="vardump-data-table" border="1">
        ';

        $html .= '<tr>';
        $colCount = 0;
        foreach($array[0] as $key=>$value){
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
        foreach( $array as $key=>$value){
            if($rowCount < $maxRows) {
                $rowCount++;
                $html .= '<tr>';
                $colCount = 0;
                foreach($value as $key2=>$value2){
                    $colCount++;
                    if($colCount > $maxCols) {
                        $html .= '<td>...</td>';
                    }
                    $html .= '<td>' . htmlspecialchars($value2) . '</td>';
                }
                $html .= '</tr>';
            }
        }
        $html .= '</table>';
        if($rowCount === $maxRows) {
            $html = '<p>not all rows shown</p>';
        }

        return $html;
    }

}

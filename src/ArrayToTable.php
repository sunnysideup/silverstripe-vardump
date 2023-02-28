<?php

namespace Sunnysideup\Vardump;

class ArrayToTable
{
    public static function convert(array $array, $maxCols = 20, $maxRows = 20): string
    {
        $maxRows = 9999999999;
        $html = '';
        $rowCount = 0;
        if ([] !== $array) {
            $html = '
            <style>
                .vardump-data-table {
                    width: 100%;
                }
                .vardump-data-table td,
                .vardump-data-table th {
                    padding: 2px;
                    font-size: 10px;
                    text-align: left;
                }
            </style>
            <table class="vardump-data-table" border="1">
            ';
            if (self::isMultiDimensionalArray($array)) {
                $header = $array[0] ?? $array;
                $html .= '<tr>';
                $colCount = 0;
                foreach ($header as $key => $value) {
                    ++$colCount;
                    if ($colCount < $maxCols) {
                        $html .= '<th>' . htmlspecialchars($key) . '</th>';
                    } else {
                        $html .= '<th>...</th>';
                    }
                }

                $html .= '</tr>';
                // data rows

                foreach ($array as $key => $value) {
                    if ($rowCount < $maxRows) {
                        ++$rowCount;
                        $colCount = 0;
                        $html .= '<tr>';
                        foreach ($value as $key2 => $value2) {
                            ++$colCount;
                            if ($colCount < $maxCols) {
                                $html .= '<td>' . strip_tags( (string) $value2) . '</td>';
                            } else {
                                $html .= '<td>...</td>';
                            }
                        }

                        $html .= '</tr>';
                    }
                }
            } else {
                foreach ($array as $key => $value) {
                    if ($rowCount < $maxRows) {
                        ++$rowCount;
                        $html .= '
                            <tr>
                                <th>' . $key . '</th>
                                <td>' . strip_tags( (string) $value) . '</td>
                            </tr>';
                    }
                }
            }

            $html .= '</table>';
            if ($rowCount === $maxRows) {
                $html .= '<p>not all rows shown</p>';
            }
        } else {
            $html .= '<p>no data available</p>';
        }

        return $html;
    }

    protected static function isMultiDimensionalArray(array $array): bool
    {
        return count($array) !== count($array, COUNT_RECURSIVE);
    }
}

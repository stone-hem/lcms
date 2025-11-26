<?php

namespace App\Helpers;

class QueryHelper
{
    public static function get_where_clause_with_match_mode(
        string $matchMode,
        string $search,
        $column
    ) {
        if ($matchMode == "startsWith") {
            return [$column, "like", "{$search}%"];
        } elseif ($matchMode == "contains") {
            return [$column, "like", "%{$search}%"];
        } elseif ($matchMode == "endsWith") {
            return [$column, "like", "%{$search}"];
        } elseif ($matchMode == "equals") {
            return [$column, "=", $search];
        } elseif ($matchMode == "notEquals") {
            return [$column, "!=", $search];
        }
        return [$column, "like", "%{$search}%"]; //return  contains by default
    }

    /**
     * ie inactive ==0
     *    active ==1
     */
    public static function get_where_clause_for_column_alias(
        $searchFields,
        string $matchMode,
        string $search
    ) {
        $filtered_values = [];
        foreach ($searchFields as $key => $value) {
            if (
                $matchMode == "contains" &&
                str_contains($key, strtolower($search))
            ) {
                array_push($filtered_values, [$key => $value]);
            } elseif ($matchMode == "notEquals" && $key != $search) {
                array_push($filtered_values, [$key => $value]);
            } elseif ($matchMode == "equals" && $key == $search) {
                array_push($filtered_values, [$key => $value]);
            } elseif (
                $matchMode == "endsWith" &&
                str_ends_with(strtolower($key), strtolower($search))
            ) {
                array_push($filtered_values, [$key => $value]);
            } elseif (
                $matchMode == "startsWith" &&
                str_starts_with(strtolower($key), strtolower($search))
            ) {
                array_push($filtered_values, [$key => $value]);
            } elseif (
                $matchMode == "startsWith" &&
                str_contains(strtolower($key), strtolower($search))
            ) {
                array_push($filtered_values, [$key => $value]);
            }
        }

        // echo 'XXX';
        //echo json_encode($filtered_values);
        //echo 'YYY';

        $where_array = [];
        foreach ($searchFields as $key => $value) {
            foreach ($filtered_values as $filtered_value) {
                if (key_exists($key, $filtered_value)) {
                    $value = $filtered_value[$key];
                    array_push($where_array, [$value[0], $value[1], $value[2]]);
                }
            }
        }

        if (count($where_array) == 0) {
            $search_field_value = "";
            foreach ($searchFields as $key => $value) {
                $search_field_value = $value[0];
                break;
            }
            $where_array = [[$search_field_value, "=", "-100212120010212"]];
        }

        //echo json_encode($where_array);

        return $where_array;
    }
}

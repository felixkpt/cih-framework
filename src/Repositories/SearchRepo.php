<?php
/**
 * Created by PhpStorm.
 * User: iankibet
 * Date: 7/12/17
 * Time: 4:09 AM
 */

namespace Cih\Framework\Repositories;

use phpDocumentor\Reflection\Types\Self_;
use Route;

class SearchRepo
{
    protected static $data;
    protected static $request_data;
    protected static $instance;
    protected static $tmp_key;
    protected static $tmp_value;
    protected static $records_count;

    public static function of($model)
    {
        self::$instance = new self();
        $request_data = request()->all();
        self::$request_data = $request_data;
        if (isset($request_data['filter_value'])) {
            $value = $request_data['filter_value'];
            $searchFieldName = $request_data['search_field'];

            $model = $model->where(function ($query) use ($searchFieldName, $request_data, $value) {
                $searchFieldNames = explode('&', $searchFieldName);
                $index = 0;
                foreach ($searchFieldNames as $index => $fieldName) {
                    if (!strpos($fieldName, '.') && $request_data['base_table'] != null)
                        $fieldName = $request_data['base_table'] . '.' . $fieldName;
                    if ($index == 0)
                        $query->where([
                            [$fieldName, 'like', '%' . $value . '%']
                        ]);
                    else
                        $query->orWhere([
                            [$fieldName, 'like', '%' . $value . '%']
                        ]);

                }

            });
        }


        $request_data = self::$request_data;
        if (isset($request_data['order_by']) && isset($request_data['order_method'])) {
            $model = $model->orderBy($request_data['order_by'], $request_data['order_method']);
        } else {
            $model = $model->orderBy('created_at', 'desc');
        }
        //count model records
        self::$records_count = $model->count();
        if (isset($request_data['per_page'])) {
            $data = $model->paginate(round($request_data['per_page'], 0));
        } else {
            $data = $model->paginate(10);
        }
        self::$data = $data;

        return self::$instance;
    }

    public static function make($pagination = true)
    {
        $data = self::$data;
        $request_data = self::$request_data;
        unset($request_data['page']);
        $data->appends($request_data);
        if ($pagination) {
            $pagination = $data->links()->__toString();
            $data = $data->toArray();
            $data['pagination'] = $pagination;
        }
        $data['results_count'] = number_format(self::$records_count);
        return $data;

    }

    public static function addColumn($column, $function)
    {
        $records = self::$data;
        foreach ($records as $index => $record) {
            $record->$column = $function($record);
            $records[$index] = $record;
        }
        self::$data = $records;
        return self::$instance;
    }
}

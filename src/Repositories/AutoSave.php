<?php

/**
 * Created by PhpStorm.
 * User: iankibet
 * Date: 4/13/17
 * Time: 9:01 AM
 */

namespace Cih\Framework\Repositories;


class AutoSave
{

    static function model($request_data)
    {
        $request_data = (object)$request_data;
        $class = $request_data->form_model;
        $model = $class::findOrNew(@$request_data->id);
        foreach ($request_data as $key => $value) {
            if (!in_array($key, ['id', '_token', 'entity_name', 'form_model', 'password_confirmation', 'tab'])) {
                if ($key == 'password') {
                    $model->$key = bcrypt($value);
                } else {
                    $model->$key = $value;
                }
            }
        }
        $model->save();
        return $model;
    }

    static function autoSaveModel($data)
    {
        $model = self::model($data);
        return $model;
    }

    static function getValidationFields($fillables = null)
    {
        $data = request()->all();
        if (!$fillables) {
            $model = new $data['form_model']();
            $fillables = $model->getFillable();
        }
        $validation_array = [];
        foreach ($fillables as $field) {
            $validation_array[$field] = 'required';
        }
        if (in_array("file", $fillables)) {
            $validation_array['file'] = 'required|max:50000';
        }
        $validation_array['id'] = '';
        $validation_array['form_model'] = '';
        return $validation_array;
    }
}

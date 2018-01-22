<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {

	    $rules = [];
	    /*$photos = count($this->all()['files']);
	    foreach(range(0, $photos - 1) as $index) {
		    $rules['photos.' . $index] = 'max:2000';
	    }*/
	    return $rules;
    }
}

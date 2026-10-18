<?php

namespace App\Http\Requests\Admin;

use App\Http\Requests\Admin\Concerns\ValidatesContentItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateContentItemRequest extends FormRequest
{
    use ValidatesContentItem;
}

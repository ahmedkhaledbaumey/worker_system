<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    // protected $guard; // متغير الحارس

    // // تمرير الحارس عند إنشاء الكلاس
    // public function __construct($guard)
    // {
    //     parent::__construct(); // استدعاء البناء الأصلي لـ FormRequest
    //     $this->guard = $guard; // تخزين الحارس في المتغير المحلي
    // }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $guard = $this->route('guard'); // الحصول على الحارس من مسار الطلب
    
        return [
            'name' => 'required|string',
            'email' => 'required|email|unique:' . $guard . 's', // تأكد من أن اسم الجدول صحيح هنا
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:17',
            'photo' => 'nullable|image|mimes:png,jpg,jpeg,pdf',
            'location' => 'nullable|string',
        ];
    }
    
}

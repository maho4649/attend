<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class ShowRequest extends FormRequest
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
        return [
            'clock_in'   => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'clock_out'  => ['nullable', 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'breaks.*.clock_in'  => ['nullable','regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'breaks.*.clock_out' => ['nullable','regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'],
            'description' => 'required|string',
        ];
    }

    public function withValidator($validator)
{
    $validator->after(function ($validator) {
        $date     = now()->toDateString();
        $clockIn = $this->input('clock_in');
        $clockOut = $this->input('clock_out');

        // 出勤・退勤の前後チェック
        if ($clockIn && $clockOut) {
            $today = now()->toDateString();
            $clockInDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . $clockIn);
            $clockOutDateTime = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $today . ' ' . $clockOut);

            if ($clockOutDateTime->lessThanOrEqualTo($clockInDateTime)) {
                $validator->errors()->add('clock_out', '出勤時間もしくは退勤時間が不適切な値です');
            }
        }

        // 休憩の整合性チェック
            if ($this->has('breaks')) {
                foreach ($this->input('breaks') as $i => $break) {

                    if (!empty($break['clock_in']) && $clockIn) {
                        $breakIn = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $break['clock_in']);
                        $in      = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $clockIn);

                        if ($breakIn->lt($in)) {
                            $validator->errors()->add("breaks.$i.clock_in", '休憩時間が不適切な値です');
                        }
                    }

                    if (!empty($break['clock_in']) && $clockOut) {
                      $breakIn = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $break['clock_in']);
                       $out     = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $clockOut);

                        if ($breakIn->gt($out)) {
                            $validator->errors()->add("breaks.$i.clock_in", '休憩時間が不適切な値です');
                        }
                    }


                    if (!empty($break['clock_out']) && $clockOut) {
                        $breakOut = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $break['clock_out']);
                        $out      = Carbon::createFromFormat('Y-m-d H:i', $date . ' ' . $clockOut);

                        if ($breakOut->gt($out)) {
                            $validator->errors()->add("breaks.$i.clock_out", '休憩時間もしくは退勤時間が不適切な値です');
                        }
                    }
                }
            }
    });
}


    public function messages()
    {
        return [
            // 1. 出退勤
            'clock_in.regex'     => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.regex'    => '出勤時間もしくは退勤時間が不適切な値です',

            // 2. 休憩開始
            'breaks.*.clock_in.regex' => '休憩時間が不適切な値です',

            // 3. 休憩終了
            'breaks.*.clock_out.regex' => '休憩時間もしくは退勤時間が不適切な値です',

            // 4. 備考
            'description.required' => '備考を記入してください',
        ];
    }
}

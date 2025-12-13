<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
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
        return [
            "price"=> ["numeric", "gt:0", 'required'],
            "user_id" => ["integer", "gt:0", 'required'],
            "seat_number" => ["integer", "gt:0", 'required'],
            "movie_id" => ["integer", "gt:0", 'required'],
            "party_date" => ["date", 'required'],
            "party_number" => ["string", 'required'],
            "extras" => ["array", "nullable"],
            "extras.*.name" => ["string", "required_with:extras"],
            "extras.*.price" => ["numeric", "min:0", "required_with:extras"],
            "extras.*.quantity" => ["integer", "min:0", "required_with:extras"],
        ];
    }
}

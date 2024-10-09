<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'rates.*.category' => 'required|string',
        'rates.*.15_min' => 'required|numeric|min:0',
        'rates.*.30_min' => 'required|numeric|min:0',
        'rates.*.1_hour' => 'required|numeric|min:0',
        'rates.*.2_hour' => 'required|numeric|min:0',
        'rates.*.4_hour' => 'required|numeric|min:0',
        'rates.*.overnight' => 'required|numeric|min:0',
    ], [
        'rates.*.category.required' => 'The category field is required for :attribute rates.',
        'rates.*.category.string' => 'The category must be a string.',
        'rates.*.15_min.required' => 'The 15 min rate is required for each rate.',
        'rates.*.15_min.numeric' => 'The 15 min rate must be a number.',
        // Add other custom messages as needed...
    ]);

    if ($validator->fails()) {
        $errors = $validator->errors()->toArray();
        
        // Transform errors to your desired format
        $formattedErrors = [];
        foreach ($errors as $key => $messages) {
            // Extract the field name (e.g., rates.0.category)
            preg_match('/rates\.(\d+)\.(.*)/', $key, $matches);
            if (isset($matches[1]) && isset($matches[2])) {
                $index = $matches[1];
                $field = $matches[2];
                
                // Create the category for each index if it doesn't exist
                if (!isset($formattedErrors[$field])) {
                    $formattedErrors[$field] = [];
                }
                
                // Append the message to the corresponding field
                foreach ($messages as $message) {
                    $formattedErrors[$field][] = str_replace(':attribute', $index, $message);
                }
            }
        }

        return response()->json(['error' => $formattedErrors], 422);
    }

    // Your logic for storing the rates goes here

    return response()->json(['message' => 'Rates saved successfully.']);
}
?>
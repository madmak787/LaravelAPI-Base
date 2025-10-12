<?php

// To return a success response
if (! function_exists('success')) {
    function success($message = '', $data = false)
    {
        $return = [
            'success' => true,
            'message' => $message,
        ];
        if ($data) {
            // $return['data'] = attachments($data);
            $return['data'] = $data;
        }

        return response()->json($return);
    }
}

// To return a failure response
if (! function_exists('failure')) {
    function failure($message = '', $data = false)
    {
        $return = [
            'success' => false,
            'message' => $message,
        ];
        if ($data) {
            $return['data'] = $data;
        }

        return response()->json($return);
    }
}

// To to replace ids with actual image data
if (! function_exists('attachments')) {
    function attachments($data): array
    {
        $process = function ($item) {
            // Convert Eloquent model to array if needed
            if ($item instanceof Model) {
                $item = $item->toArray();
            }

            foreach ($item as $key => $value) {
                if (str_ends_with($key, '_attachment_id') && ! empty($value)) {
                    $image = Media::find($value);

                    $newKey = str_replace('_attachment_id', '', $key);
                    $item[$newKey] = $image
                        ? [
                            'id' => $image->id,
                            'name' => $image->name,
                            'url' => $image->url,
                        ]
                        : null;

                    unset($item[$key]);
                }
            }

            return $item;
        };

        // Handle Laravel Collection
        if ($data instanceof Collection) {
            $data = $data->toArray();
        }

        // If it's an Eloquent model, convert to array and process as single item
        if ($data instanceof Model) {
            return $process($data->toArray());
        }

        // If it's a list of models or arrays
        if (is_array($data)) {
            return array_is_list($data)
                ? array_map($process, $data)
                : $process($data); // Single associative array
        }

        // Fallback
        return [];
    }
}

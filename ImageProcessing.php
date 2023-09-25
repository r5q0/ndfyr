<?php
// $payload = [
//     "prompt" => "puppy dog",
//     "cfg_scale" => 20,
//     "steps" => 25,
//     "height" => 768,
//     "seed" => 519753005,
//     "sampler_name" => "DPM++ SDE Karras",
//     "enable_hr" => true,
//     "denoising_strength" => 0.4,
//     "hr_scale" => 2,
//     "hr_upscaler" => "4x-UltraSharp",
//     "hr_second_pass_steps" => 10,
//     "alwayson_scripts" => [
//         "Dynamic Thresholding (CFG Scale Fix)" => [
//             "args" => [true, 7, 100, "Half Cosine Up", 5, "Half Cosine Up", 5, 4.0]
//         ]
//     ]
// ];

$url = 'http://127.0.0.1:7860/sdapi/v1/txt2img';

$data = array(
    'enable_hr' => false,
    'denoising_strength' => 0,
    'firstphase_width' => 0,
    'firstphase_height' => 0,
    'hr_scale' => 2,
    'hr_upscaler' => 'string',
    'hr_second_pass_steps' => 0,
    'hr_resize_x' => 0,
    'hr_resize_y' => 0,
    'hr_sampler_name' => 'string',
    'hr_prompt' => '',
    'hr_negative_prompt' => '',
    'prompt' => 'shoes',
    'styles' => array('string'),
    'subseed_strength' => 0,
    'seed_resize_from_h' => -1,
    'seed_resize_from_w' => -1,
    'sampler_name' => 'string',
    'batch_size' => 1,
    'n_iter' => 1,
    'steps' => 24,
    'cfg_scale' => 7,
    'width' => 512,
    'height' => 512,
    'restore_faces' => false,
    'tiling' => false,
    'do_not_save_samples' => false,
    'do_not_save_grid' => false,
    'negative_prompt' => 'feet, hair, wood',
    'eta' => 0,
    's_min_uncond' => 0,
    's_churn' => 0,
    's_tmax' => 0,
    's_tmin' => 0,
    's_noise' => 1,
    'script_args' => array(),
    'sampler_index' => 'Euler',
    'send_images' => true,
    'save_images' => false,
    "alwayson_scripts" => [
        "Dynamic Thresholding (CFG Scale Fix)" => [
            "args" => [true, 7, 100, "Half Cosine Up", 5, "Half Cosine Up", 5, 4.0]
        ]
    ]


);

$options = array(
    'http' => array(
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($data)
    )
);

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo "Error sending request.";
} else {
    // Decode the JSON response
    $responseData = json_decode($response, true);

    if (isset($responseData['image']) && !empty($responseData['image'])) {
        // Save the base64 encoded image to a file
        $imageData = base64_decode($responseData['image']);
        file_put_contents('image.png', $imageData);
        echo "Image saved as 'image.png'";
    } else {
        echo "No image data received in the response.";
    }
}

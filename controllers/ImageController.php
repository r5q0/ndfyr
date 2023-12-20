<?php

namespace Controllers;

class ImageController
{


    public static $samEndpoint = 'http://127.0.0.1:7860/sam/sam-predict';
    public static $i2iEndpoint = 'http://127.0.0.1:7860/sdapi/v1/img2img';
    public static $headers = [
        'Accept: application/json',
        'Content-Type: application/json'
    ];
    public static $samConfig = [
        // 'sam_model_name' => 'sam_hq_vit_h.pth',
        'sam_model_name' => 'mobile_sam.pt',
        'sam_positive_points' => [],
        'sam_negative_points' => [],
        'dino_enabled' => true,
        'dino_model_name' => 'GroundingDINO_SwinT_OGC (694MB)',
        'dino_text_prompt' => 'clothing. dress. straps. bikini. clothes. tops. pants',
        'dino_box_threshold' => 0.3,
        'dino_preview_checkbox' => false,
        'dino_preview_boxes_selection' => [0]
    ];

    public static $img2imgConfig = [
        "denoising_strength" => 1,
        "firstphase_width" => 0,
        "firstphase_height" => 0,
        "prompt" => "nude body, breasts, nippels",
        "negative_prompt" => "(clothing),(drawing, painting),(pubic hair),(blurry), ink on skin, tatoo, markings on skin, shiny skin, plastic skin",
        "seed" => -1,
        "subseed_strength" => -1,
        "sampler_name" => "DPM2 a Karras",
        "batch_size" => 1,
        "n_iter" => 1,
        "steps" => 24,
        "cfg_scale" => 4,
        "width" => 512,
        "height" => 512,
        "inpainting_fill" => 2,
        "inpaint_full_res" => true,
        "inpaint_full_res_padding" => 100,
        "mask_blur" => 8,
        "alwayson_scripts" => [
            "ADetailer" => [
                "args" => [
                    true,
                    [
                        "ad_model" => "pussyV2.pt",
                        "ad_prompt" => " <lora:BetterStandingSlit:1>, bsp, <lora:InniePussy1 v4 Inpainting:1>",
                        "ad_negative_prompt" => "pubic hair, blurry"
                    ]
                ]
            ]
        ]
    ];


    public static function getMask($image)
    {
        self::$samConfig['input_image'] = $image;
        $ch = curl_init(self::$samEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(self::$samConfig));
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::$headers);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        $base64Mask = $data['masks'][1];
        return $base64Mask;
    }
    public static function maskLarger($mask, $img)
    {
        $settings =  json_encode(array(
            "input_image" =>  $img,
            "mask" => $mask,
            "dilate_amount" => 30
        ));
        $url =   'http://127.0.0.1:7860/sam/dilate-mask';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $settings);
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::$headers);
        $response = curl_exec($ch);
        curl_close($ch);
        $json = json_decode($response, true);
        $response = $json['mask'];
        return $response;
    }

    public static function getND($img, $mask)
    {
        $tmask = self::maskLarger($mask, $img);
        self::$img2imgConfig["mask"] = $tmask;
        self::$img2imgConfig["init_images"] = array($img);
        self::switchModel();
        $ch = curl_init(self::$i2iEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(self::$img2imgConfig));
        curl_setopt($ch, CURLOPT_HTTPHEADER, self::$headers);
        $response = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($response, true);
        $image = $data['images'][0];
        return $image;
    }


    public static function getQueue()
    {
        $data = file_get_contents('http://127.0.0.1:7860/queue/status');
        $json = json_decode($data, true);
        return $json['queue_size'];
    }
    public static function switchModel()
    {


        // $opt = ['sd_model_checkpoint' => 'epicphotogasm_v4-inpainting.safetensors'];
        // $post_data = json_encode($opt);

        // $ch = curl_init('http://127.0.0.1:7860/sdapi/v1/options');
        // curl_setopt($ch, CURLOPT_POST, 1);
        // curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        // curl_exec($ch);
        // curl_close($ch);
    }
}

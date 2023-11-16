<?php

namespace Controllers;

class ImageController
{
    private static $samEndpoint;
    private static $headers;
    private static $samConfig;
    private static $i2iEndpoint;
    private static $posPrompt;
    private static $negPrompt;
    private static $img2imgConfig;
    public static function init()
    {
        self::$samEndpoint = 'http://127.0.0.1:7860/sam/sam-predict';
        self::$i2iEndpoint = 'http://127.0.0.1:7860/sdapi/v1/img2img';
        self::$headers = [
            'Accept: application/json',
            'Content-Type: application/json'
        ];
        self::$samConfig = [
            'sam_model_name' => 'mobile_sam.pt',
            // 'sam_model_name' => 'sam_hq_vit_h.pth',
            'sam_positive_points' => [],
            'sam_negative_points' => [],
            'dino_enabled' => true,
            'dino_model_name' => 'GroundingDINO_SwinT_OGC (694MB)',
            'dino_text_prompt' => 'clothing. dress. straps. bikini. clothes. tops. pants',
            'dino_box_threshold' => 0.3,
            'dino_preview_checkbox' => false,
            'dino_preview_boxes_selection' => [0]
        ];
        // self::$posPrompt = 'subsurface scattering, nudity, nude, naked, symmetrical, (((max detail))), (((centered))), spread_anus, spread_ass, perfect pussy, anus, pussy exposed, fully nude, keep proportions, keep same limbs, girl bare body, 8k high definition, smooth hairless vagina, normal female body type, small nipples, tiny nipples, natural nipples, perfect round nipples, pornstar nipples, pornstar pussy, smooth vagina, vagina slit';
        // self::$negPrompt = 'cartoon, child, bad art, ugly face, messed up face, poorly drawn hands, bad hands , photoshop, doll, plastic_doll, silicone, anime, cartoon, fake, airbrush, 3d max, infant, featureless, colourless, monochrome, impassive, shaders, vagina hair, pubic hair, hairy pussy, (3d:1.6), (3d render:1.6), (3dcg:1.6), (portrait:1.6), (cropped head), (close up:1.6), ((deformed, deformed body, deformed hands, deformed feet, deformed legs)), drawing, duplicate, error, extra arms, extra breasts, extra calf, extra digit, extra ears, extra eyes, extra feet, (extra fingers), extra heads, extra knee, extra legs, extra limb, extra limbs, extra shoes, extra thighs, extra limb, failure, fewer digits, floating limbs, grainy, gross, gross proportions, short arm, illustration, image corruption, irregular, jpeg artifacts, long body, long neck, lopsided, low quality, messy drawing, misshapen, (out of focus), (out of frame), oversaturated, body hairs, hairy, ((female_pubic_hair, pubic_hair)), ((penis, dick)), (fake skin, porcelain skin, (wrinkles), navel piercing), (((duplicate))), ((morbid)), ((mutilated)), [out of frame], extra fingers, mutated hands, ((poorly drawn hands)), ((poorly drawn face)), (((mutation))), (((deformed))), ((ugly)), blurry, ((bad anatomy)), (((bad proportions))), ((extra limbs)), cloned face, (((disfigured))), ugly, extra limbs, (bad anatomy), gross proportions, (malformed limbs), ((missing arms)), ((missing legs)), (((extra arms))), (((extra legs))), mutated hands, (fused fingers), (too many fingers), (((long neck))), blurry, belly bar, necklace, jewelry, pussy hair, belly piercing, shiny skin, unblended skin tone, unnatural skin look, plastic skin, not full body, oversized nipples, large nipples, change hair, change hair colour/ shade, ((clothing)), (monochrome:1.3), (deformed, distorted, disfigured:1.3), (hair), jeans, tattoo, wet, water, clothing, shadow, 3d render, ((blurry)), duplicate, ((duplicate body parts)), (disfigured), (poorly drawn), ((missing limbs)), boring, artifacts, bad art, gross, ugly, poor quality, low quality, poorly drawn, bad anatomy, wrong anatomy, belly bar, belly piercing, logo, signature, text, words, watermark, badges, banners, username, ink on skin, tatoo, markins on skin, shiny skin, plastic skin, reflections, bad lighting, large vagina, hairy pussy, pubic hair on vagina, by <bad-hands-5:0.8>';
        self::$img2imgConfig = [
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
            "inpaint_full_res_padding" => 124,
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
    }

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


        $opt = ['sd_model_checkpoint' =>'epicphotogasm_v4-inpainting.safetensors'];
        $post_data = json_encode($opt);

        $ch = curl_init('http://127.0.0.1:7860/sdapi/v1/options');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_exec($ch);
        curl_close($ch);
    }
}

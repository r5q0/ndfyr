import Express from "express";
import fs from "fs/promises";
import crypto from "crypto";
import { error } from "console";
import { Builder, By, Key, until } from "selenium-webdriver";
import path from "path";
import { fileURLToPath } from 'url';
import checkImageTelegramFetch from "../../middlewares/checkPostBodyTelegramID.js";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

export default {
    type: "post",
    middlewares: [checkImageTelegramFetch, Express.json({limit: '50mb'})],
    start: async (req, res) => {
        const settings =  (await import("../../../settings.json", {
            assert: {
                type: "json"
            }
        })).default;

        const autoInpainterSettings = settings.autoInpainterSettings;
        const maskSettings = autoInpainterSettings.maskCreatorSettings;
        const inpaintSettings = autoInpainterSettings.inpaintPictureSettings;

        await fs.mkdir(`./imageFolder/${req.body.username}-${req.body.id}`).catch(() => { return });

        const key = crypto.randomUUID();
        await fs.mkdir(`./imageFolder/${req.body.username}-${req.body.id}/${key}`).catch(console.error);

        const inputBase64 = req.body.inputBase64.split('base64,')[1] || req.body.inputBase64;
        const inputBuffer = Buffer.from(inputBase64, "base64");
        console.log(inputBase64.length + " characters, " + Buffer.byteLength(inputBase64, 'utf8')/ 1024 / 1024 + " MB");

        maskSettings.input_image = inputBase64;

        await fs.writeFile(`./imageFolder/${req.body.username}-${req.body.id}/${key}/inputImage.jpg`, inputBuffer);

        try {
            if (req.body.sensibility) {
                maskSettings.dino_box_threshold = req.body.sensibility;
            }

            //console.log(maskSettings)

            let maskResponse = await fetch("http://0.0.0.0:7860/sam/sam-predict", {
                method: "post",
                headers: {
                    'Content-Type': 'application/json; charset=utf-8',
                    Accept: 'application/json'
                },
                body: JSON.stringify(maskSettings)
            })

            let maskJson = await maskResponse.json();

            if (!maskJson.masks) { 
                await res.status(404).json({
                    status: "error",
                    message: "no clothes found"
                });
                throw error("no clothes found");
            }

            inpaintSettings.init_images = [];
            inpaintSettings.init_images[0] = inputBase64;
            inpaintSettings.mask =  maskJson.masks[0];

            let maskDilateResponse = await fetch("http://0.0.0.0:7860/sam/dilate-mask", {
                method: "post",
                headers: {
                    'Content-Type': 'application/json; charset=utf-8',
                    Accept: 'application/json'
                },
                body: JSON.stringify({
                    "input_image": inputBase64,
                    "mask": inpaintSettings.mask,
                    "dilate_amount": autoInpainterSettings.maskDilate.pixels
                })
            })

            let maskDilateJson = await maskDilateResponse.json();
            inpaintSettings.mask = maskDilateJson.mask;

            const outputMaskBuffer = Buffer.from(inpaintSettings.mask, "base64");
            await fs.writeFile(`./imageFolder/${req.body.username}-${req.body.id}/${key}/mask.jpg`, outputMaskBuffer);

        }
        catch(err) {
            console.error(err);
            
            return;
        }

        const driver = new Builder()
            .forBrowser("chrome")
            .build();

        try {
            await driver.get("http://127.0.0.1:7860");
            await driver.sleep(2000);

            const img2imgBtn = await driver.findElement(By.css(".tabs .tab-nav button:nth-child(2)"));
            await img2imgBtn.click();

            const inpaintUploadBtn = await driver.findElement(By.css("#img2img_settings .tabs button:nth-child(5)"));
            await inpaintUploadBtn.click();

            const inputSampling = await driver.findElement(By.css("#img2img_sampling label div .wrap-inner div input"));
            await inputSampling.click();

            const choice = await driver.findElement(By.css("#img2img_sampling label div ul li[data-value='DPM2 a Karras']"));
            await choice.click();
        }
        catch(err) {
            console.error(err);
            await driver.close();
            return;
        }

        try {
            const imageUploader = await driver.findElement(By.css("#img_inpaint_base .image-container div input[type='file']"));
            await imageUploader.sendKeys(path.join(__dirname, `/../../../imageFolder/${req.body.username}-${req.body.id}/${key}/inputImage.jpg`));

            const maskUploader = await driver.findElement(By.css("#img_inpaint_mask .image-container div input[type='file']"));
            await maskUploader.sendKeys(path.join(__dirname, `/../../../imageFolder/${req.body.username}-${req.body.id}/${key}/mask.jpg`));

            const generateBtn = await driver.findElement(By.css("#img2img_generate"));
            await generateBtn.click();

            await driver.wait(until.elementLocated(By.css("#img2img_gallery .grid-wrap .grid-container button img")));
            //await driver.sleep(1000);

            const result = await driver.findElement(By.css("#img2img_gallery .grid-wrap .grid-container button img"));
            let location = await result.getAttribute("src");

            if (location) {
                const responseImg = await fetch(location);
                const contents = await responseImg.arrayBuffer();
                const resultBuffer = Buffer.from(contents, "base64");

                await fs.writeFile(`./imageFolder/${req.body.username}-${req.body.id}/${key}/result.jpg`, resultBuffer);
                await res.status(200).json({
                    status: "success",
                    data: resultBuffer.toString("base64")
                });
            }
        }
        catch(err) {
            console.error(err);
            return;
        }

        await driver.close()
    }
}

# System-related instructions

## Verify passwoard `VfyPwd` / `verifyPassword()`

Verifies the sensors' access password (default password is 0x0000000). A good way to also check if the sensors is active and responding.

## Read valid template number `TempleteNum` / `getTemplateCount()`

Ask the sensor for the number of templates stored in memory. The number is stored in `templateCount` on success.

##  Fingerprint verification `GR_Auto Search` / ?NOT FOUND?

Description: Match captured fingerprint with fingerprint library ,then return the result. Self-definetime for capture, Search start position code and search quantity.

## Automatic Fingerprint verification `GR_Identify` / ?NOT FOUND?

Description: Automatic collect fingerprint, match captured fingerprint with fingerprint library and return result.

# Fingerprint-processing instructions

## To collect finger image `GenImg` /  `getImage()`

Ask the sensor to take an image of the finger pressed on surface.

Description: Detecting finger and store the detected finger image in ImageBuffer whilereturning successfull confirmation code. If there is no finger, returned confirmationcodewould be “can’t detect finger”.

## Upload image `UpImage` / ?NOT FOUND?

Description: to upload the image in Img_Buffer to upper computer. Refer to 1.1.1 for more about image buffer

## Download the image `DownImage` / ?NOT FOUND?

Description: to download image from upper computer to Img_Buffer. Refer to 1.1.1 for more about the image buffer.

## To generate character file from image `Img2Tz` / `image2Tz`

Ask the sensor to convert image to feature template.

Description: to generate character file from the original finger image in ImageBuffer and store the file in CharBuffer1 or CharBuffer2.

## To generate template `RegModel` / `createModel()`

Ask the sensor to take two print feature template and create a model.

Description: To combine information of character files from CharBuffer1 and CharBuffer2 and generate a template which is stored back in both CharBuffer1 and CharBuffer2.

## To upload character or template `UpChar` / `getModel()`

Ask the sensor to transfer 256-byte fingerprint template from the buffer to the UART.

Description: to upload the character file or template of CharBuffer1/CharBuffer2 to upper computer.

## To download character file or template `DownChar` / ?NOT FOUND?

Description: to download character file or template from upper computer to the specified buffer of Module.

## To store template `Store` / `storeModel()`

Ask the sensor to store the calculated model for later matching.

Description: to store the template of specified buffer (Buffer1/Buffer2) at the designated location of Flash library

## To read template from Flash library `LoadChar` / `loadModel()`

Ask the sensor to load a fingerprint model from flash into buffer **1** (only buffer **1**)

Description: to load template at the specified location (PageID) of Flash library to template bufferCharBuffer1/CharBuffer2

## To delete template `DeletChar` / modified as `deleteModel()`

Description: to delete a segment (N) of templates of Flash library started fromthe specified location (or PageID).

Ask the sensor to delete a model in memory.

## To empty finger library `Empty` / `emptyDatabase()`

Ask the sensor to delete ALL models in memory.

Description: to delete all the templates in the Flash library.

## To carry out precise matching of two finger templates `Match` / possibly ?NOT FOUND?

Description: to carry out precise matching of templates from CharBuffer1 and CharBuffer2, providing matching results.

## To search finger library `Search` / `fingerSearch(slot = 1)`

Ask the sensor to search the current slot fingerprint features to match saved templates. The matching location is stored in fingerID and the matching confidence in confidence.

Description: to search the whole finger library for the template that matches the one in CharBuffer1or CharBuffer2. When found, PageID will be returned.


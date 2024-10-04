<?php defined('BILLINGMASTER') or die; ?>

<?php
if (isset($element['params']['gallery'])) {
    define('useGallery', $element['params']['gallery']);
}

$galleryImages = [];
$element['params']['showImages'] = 1;//TODO
switch (@$element['params']['showImages']) {
    case 0://Из "списка элементов галереи"
        $galleryImages =  $element['params']['images'] ?? [];
        break;
    case 1:// Изображения из категории галереи
        $galleryImages = Gallery::getImageList(1, 999, $element['params']['galleryCat']);
        if (!is_array($galleryImages)) {
            break;
        }
        foreach ($galleryImages as $key => $imagedata) {
            $alt = $imagedata['alt'] ?? "";
            $galleryImages[$key]['alt'] = $alt;
            $galleryImages[$key]['url'] = "/images/gallery/".$imagedata['file'] ?? $imagedata['link'] ?? "";
            $galleryImages[$key]['title'] = $imagedata['title'] ?? "";
            $galleryImages[$key]['desc'] = $imagedata['item_desc'] ?? "";
        }
        break;
    case 2://Оба типа
        $galleryImages = $element['params']['images'] ?? [];

        $galleryImagesData = Gallery::getImageList(1, 999, $element['params']['galleryCat']);

        if ($galleryImagesData) {
            foreach ($galleryImagesData as $key => $imagedata) {
                $alt = $imagedata['alt'] ?? "";
                $galleryImagesData[$key]['alt'] = $alt;
                $galleryImagesData[$key]['url'] = "/images/gallery/" . $imagedata['file'] ?? $imagedata['link'] ?? "";
                $galleryImagesData[$key]['title'] = $imagedata['title'] ?? "";
                $galleryImagesData[$key]['desc'] = $imagedata['item_desc'] ?? "";
            }
        }
        if ($galleryImagesData) {
            $galleryImages = $galleryImages + $galleryImagesData;
        }

        break;
}

?>
<div id="gallery" class="ug-gallery-wrapper ug-under-480 ug-theme-slider">
    <?php if (is_iterable($galleryImages)) { foreach ($galleryImages as $image) { ?>
        <img alt="<?= $image['alt'] ?>" src="<?= $image['url'] ?>" title="<?= $image['title'] ?>" data-image="<?= $image['url'] ?>" data-description="<?= $image['desc'] ?>" style="display:none">
    <?php } }?>
</div>

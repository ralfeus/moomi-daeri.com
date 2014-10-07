<?= $header ?>
<div id="content">
    <div class="breadcrumb">
<?php foreach ($breadcrumbs as $breadcrumb): ?>
        <?= $breadcrumb['separator'] ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
<?php endforeach; ?>
    </div>
    <div class="box">
        <div class="heading">
            <h1><img src="view/image/product.png" alt="" /> <?= $headingTitle ?></h1>
            <div class="buttons">
                <a onclick="image_upload()" class="button"><?= $textImageManager ?></a>
                <a onclick="$('#form').submit();" class="button"><?= $textSave ?></a>
                <a href="<?= $urlCancel ?>" class="button"><?= $textCancel ?></a>
            </div>
        </div>
        <div class="content">
            <div id="tabs" class="vtabs">
<?php foreach ($languages as $language): ?>
                <a href="#language<?= $language['language_id'] ?>">
                    <img src="view/image/flags/<?= $language['image'] ?>" title="<?= $language['name'] ?>" /> <?= $language['name'] ?>
                </a>
<?php endforeach; ?>
            </div>
            <form id="form" action="<?= $urlSaveForm ?>" method="post">
                <input type="hidden" name="contentId" value="<?= $contentId ?>" />
<?php foreach ($languages as $language): ?>
                <div id="language<?= $language['language_id'] ?>" class="vtabs-content">
                    <textarea name="text[<?= $language['language_id'] ?>]" id="text<?= $language['language_id'] ?>">
                        <?= $text[$language['language_id']] ?>
                    </textarea>
                </div>
<?php endforeach; ?>
            </form>
        </div>
    </div>
</div>
<?= $footer ?>
<script type="text/javascript" src="view/javascript/ckeditor/ckeditor.js"></script>
<script type="text/javascript"><!--
<?php foreach ($languages as $language): ?>
CKEDITOR.replace('text<?= $language['language_id'] ?>', {
    filebrowserBrowseUrl: '<?= $urlFileManager ?>',
    filebrowserImageBrowseUrl: '<?= $urlFileManager ?>',
    filebrowserFlashBrowseUrl: '<?= $urlFileManager ?>',
    filebrowserUploadUrl: '<?= $urlFileManager ?>',
    filebrowserImageUploadUrl: '<?= $urlFileManager ?>',
    filebrowserFlashUploadUrl: '<?= $urlFileManager ?>'
});
<?php endforeach; ?>

$('#tabs a').tabs();
//--></script>
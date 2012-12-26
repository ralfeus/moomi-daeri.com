<?= $header ?><?= $column_left ?><?= $column_right ?>
<div id="content">
    <div class="breadcrumb">
<?php foreach ($breadcrumbs as $breadcrumb): ?>
        <?= $breadcrumb['separator']; ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
<?php endforeach; ?>
    </div>
    <h1><?= $heading_title ?></h1>
    <div class="content">
<?php foreach ($images as $imagePath): ?>
        <img src="<?= $imagePath ?>" />
<?php endforeach; ?>
    </div>
</div>
<?= $footer ?>
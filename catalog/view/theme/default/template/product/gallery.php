<?= $header ?><?= $column_left ?><?= $column_right ?>
<div id="content">
    <div class="breadcrumb">
<?php foreach ($breadcrumbs as $breadcrumb): ?>
        <?= $breadcrumb['separator']; ?><a href="<?= $breadcrumb['href'] ?>"><?= $breadcrumb['text'] ?></a>
<?php endforeach; ?>
    </div>
    <table width="100%">
    	<tr>
    		<td>
    			<h1><?= $heading_title ?></h1>
    		</td>
    		<td align="right">
    			<a class="button" href="/moomidaeri/index.php?route=gallery/photo/addPhoto"><span><?= $gallery_add_photo ?></span></a>
    		</td>
    	</tr>
    </table>
    <div class="content">
<?php foreach ($images as $imagePath): ?>
        <img src="<?= $imagePath ?>" width="500" />
<?php endforeach; ?>
    </div>
</div>
<?= $footer ?>
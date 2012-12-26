<?php if ($reviews): ?>
    <?php foreach ($reviews as $review): ?>
<div class="content">
    <p>
        <b><?= $review['author'] ?></b>&nbsp;|&nbsp;<img src="catalog/view/theme/default/image/stars-<?php echo $review['rating'] . '.png'; ?>" alt="<?php echo $review['reviews']; ?>" />
        <br />
        <?= $review['date_added'] ?>
    </p>
    <p><?= $review['text'] ?></p>
        <?php foreach ($review['images'] as $imagePath): ?>
    <img src="<?= HTTP_IMAGE . $imagePath ?>" />
        <?php endforeach; ?>
</div>
    <?php endforeach; ?>
<div class="pagination"><?php echo $pagination; ?></div>
<?php else: ?>
<div class="content"><?php echo $text_no_reviews; ?></div>
<?php endif; ?>

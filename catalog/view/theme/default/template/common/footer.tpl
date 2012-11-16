<div id="footer">
    <?php foreach ($informations as $information) { ?>
    <div class="column"><a href="<?php echo $information['href']; ?>"><?php echo $information['title']; ?></a></div>
    <?php } ?>
</div>

<div id="powered"><?php echo $powered; ?></div>
</div>
</body></html>
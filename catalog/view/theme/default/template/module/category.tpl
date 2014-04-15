<style>
    .category_box_heading {
        padding: 8px 10px 7px 10px;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 14px;
        font-weight: bold;
        line-height: 14px;
        color: #333;
        border-bottom: 2px solid #000000;
    }
</style>
<div class="box">
	<div class="category_box_heading"><?php echo $heading_title; ?></div>
    <!-- 	<div class="box-content"> -->
    <div>
<?php /*?>
        <div class="box-category">
            <ul>
<?php foreach($categories as $category) { ?>
                <li>
    <?php if($category['category_id'] == $category_id) { ?>
                    <a href="<?php echo $category['href']; ?>" class="active"><?php echo $category['name']; ?></a>
    <?php } else { ?>
                    <a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a>
    <?php } ?>
    <?php if($category['children']) { ?>
                    <ul>
        <?php foreach($category['children'] as $child) { ?>
                        <li>
            <?php if($child['category_id'] == $child_id) { ?>
                            <a href="<?php echo $child['href']; ?>" class="active"> - <?php echo $child['name']; ?></a>
            <?php } else { ?>
                            <a href="<?php echo $child['href']; ?>"> - <?php echo $child['name']; ?></a>
            <?php } ?>
                        </li>
        <?php } ?>
                    </ul>
    <?php } ?>
                </li>
<?php } ?>
            </ul>
        </div>
<?php*/ ?>
<?php if($categories) { ?>
        <div id="menu">
            <ul>
    <?php foreach($categories as $category) { ?>
                <li>
        <?php if($category['active']) { ?>
                    <a href="<?php echo $category['href']; ?>" class="active"><?php echo $category['name']; ?></a>
        <?php } else { ?>
                    <a href="<?php echo $category['href']; ?>"><?php echo $category['name']; ?></a>
        <?php } ?>
        <?php if($category['children']) { ?>
                    <div>
            <?php for($i = 0; $i < count($category['children']);) { ?>
                        <ul>
                <?php $j = $i + ceil(count($category['children']) / $category['column']); ?>
                <?php for(; $i < $j; $i++) { ?>
                    <?php if(isset($category['children'][$i])) { ?>
                            <li>
                                <a href="<?php echo $category['children'][$i]['href']; ?>">
                                    <?php echo $category['children'][$i]['name']; ?>
                                </a>
                            </li>
                    <?php } ?>
                <?php } ?>
                        </ul>
            <?php } ?>
                    </div>
        <?php } ?>
                </li>
    <?php } ?>
            </ul>
        </div>
<?php } ?>
    </div>
</div>
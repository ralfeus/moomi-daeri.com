<div id="column_left">
    <?php foreach ($modules as $module) {
        if (isset($module['code']))
            echo ${$module['code']};
    } ?>
</div>

<?= $header ?>
<?php
/** @var \model\extension\ImportSourceSite $sourceSite */
/** @var \model\catalog\Supplier[] $suppliers */
/** @var \model\catalog\Manufacturer[] $manufacturers */
?>
<div id="content">
  <div class="breadcrumb">
<?php foreach ($breadcrumbs as $breadcrumb): ?>
    <?= $breadcrumb['separator'] ?><a href="<?= $breadcrumb['href'] ?>"><? $breadcrumb['text'] ?></a>
<?php endforeach; ?>
  </div>
<?php foreach ($notifications as $class => $notification)
    echo "<div class=\"$class\">" . nl2br($notification) . "</div>";
?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/total.png" alt="" /> <?= $headingTitle; ?></h1>
      <div class="buttons">
          <a onclick="save();" class="button"><?= $textSave ?></a>
          <a onclick="saveContinue();" class="button"><?= $textSaveContinueEdit ?></a>
          <a href="<?= $urlList ?>" class="button"><?= $textCancel ?></a>
      </div>
    </div>
    <div class="content">
      <form action="<?= $urlAction ?>" method="post" enctype="multipart/form-data" id="form">
          <input type="hidden" name="continue" value="0" />
        <table class="form">
          <tr>
            <td><?= $textClassName ?></td>
            <td><?= $importSite->getClassName() ?></td>
          </tr>
          <tr>
            <td><?= $textSiteName ?></td>
            <td><input type="text" name="siteName" value="<?= $importSite->getName() ?>"/></td>
          </tr>
            <tr>
                <td><?= $textDefaultManufacturer ?></td>
                <td>
                    <select name="defaultManufacturerId">
<?php foreach ($manufacturers as $manufacturer):
    $selected = $manufacturer->getId() == $importSite->getDefaultManufacturer()->getId() ? " selected" : ""; ?>
                        <option value="<?= $manufacturer->getId() ?>" <?= $selected ?>><?= $manufacturer->getName() ?></option>
<?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label for="defaultSupplierId"><?= $textDefaultSupplier ?></label></td>
                <td>
                    <select id="defaultSupplierId" name="defaultSupplierId">
                        <?php foreach ($suppliers as $supplier):
                            $selected = $supplier->getId() == $importSite->getDefaultSupplier()->getId() ? " selected" : ""; ?>
                            <option value="<?= $supplier->getId() ?>" <?= $selected ?>><?= $supplier->getName() ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><?= $textDefaultCategories ?></td>
                <td><input type="text" name="defaultCategories" value="<?= implode(',', $importSite->getDefaultCategories()) ?>"/></td>
            </tr>
            <tr>
                <td><?= $textStores ?></td>
                <td><input type="text" name="stores" value="<?= implode(',', $importSite->getStores()) ?>"/></td>
            </tr>
            <tr>
                <td><?= $textRegularCustomerPriceRate ?></td>
                <td><input type="text" name="regularCustomerPriceRate" value="<?= $importSite->getRegularCustomerPriceRate() ?>"/></td>
            </tr>
            <tr>
                <td><?= $textWholesaleCustomerPriceRate ?></td>
                <td><input type="text" name="wholesaleCustomerPriceRate" value="<?= $importSite->getWholesaleCustomerPriceRate() ?>"/></td>
            </tr>
            <tr>
                <table border="1">
                    <tr id="categoryMapEntry">
                        <td>

                        </td>
                    </tr>
                </table>
            </tr>
        </table>
      </form>
    </div>
  </div>
</div>
<script type="javascript">//<!--
function save() {
    $('#form').submit();
}

function saveContinue() {
    $('input[name=continue]').val(1);
    $('#form').submit();
}
//--></script>
<?= $footer ?>
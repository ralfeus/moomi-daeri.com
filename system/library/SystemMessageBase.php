<?php
namespace system\library;

/**
 * Created by JetBrains PhpStorm.
 * User: dev
 * Date: 29.7.12
 * Time: 20:56
 * To change this template use File | Settings | File Templates.
 */
abstract class SystemMessageBase extends LibraryClass {
    public abstract function handleCreate($messageId);

    public abstract function handleDelete($messageId);

    public abstract function handleUpdate($messageId);
}

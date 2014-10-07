<?php
// Heading
$_['heading_error_log']    = 'Журнал ошибок';
$_['heading_settings']     = 'Настройка и Поддержка';
$_['heading_title']        = 'VQMod Менеджер';
$_['heading_vqmods']       = 'Скрипты VQMod';

// Columns
$_['column_action']        = 'Установить / Удалить';
$_['column_author']        = 'Автор';
$_['column_delete']        = 'Действие';
$_['column_file_name']     = 'Имя файла';
$_['column_id']            = 'Название / Описание';
$_['column_status']        = 'Состояние';
$_['column_version']       = 'Версия скрипта';
$_['column_vqmver']        = 'Версия VQMod';

// Entry
$_['entry_backup']         = 'Резервное копирование файлов:';
$_['entry_ext_version']    = 'Версия VQMod Менеджера:';
$_['entry_status']         = 'VQMod Manager Status:';
$_['entry_upload']         = 'Загрузить скрипт VQMod:';
$_['entry_vqmod_path']     = 'Директория VQMod:';
$_['entry_vqcache']        = 'Кэш VQMod:';
$_['entry_use_cache']      = 'Вывод кэша:';

// Text
$_['text_autodetect']      = 'VQMod будет установлен по этому пути. Нажмите Сохранить, чтобы подтвердить путь для полной установки.';
$_['text_autodetect_fail'] = 'Файл установки VQMod не лбнаружен.  Пожалуйста, скачайте и установите <a href="http://code.google.com/p/vqmod/downloads/list" target="_blank">последнюю версию</a> или введите путь нестандартной установки сервера.';
$_['text_delete']          = 'Удалить';
$_['text_disabled']        = 'Отключен';
$_['text_enabled']         = 'Включен';
$_['text_install']         = 'Установить';
$_['text_module']          = 'Модули';
$_['text_no_results']      = 'Скрипты VQMod не найдены!';
$_['text_success']         = 'Настройки VQMod Менеджера обновлены!';
$_['text_unavailable']     = '&mdash;';
$_['text_uninstall']       = 'Удалить';
$_['text_vqcache_help']    = 'Некоторые системные файлы будут присутствовать всегда, даже после очистки кэша.';
$_['text_vqmod_path']      = 'Путь к директории VQMod';

// Button
$_['button_backup']        = 'Сохранить';
$_['button_cancel']        = 'Отмена';
$_['button_clear']         = 'Очистить';
$_['button_save']          = 'Сохранить';
$_['button_upload']        = 'Загрузить';

// Error
$_['error_delete']         = 'Не удается удалить VQMod скрипт!';
$_['error_filetype']       = 'Неверный тип файла! Нужно загрузить файл с раширением xml';
$_['error_install']        = 'Не удается установить VQMod скрипт!';
$_['error_invalid_xml']    = 'Синтаксис XML-файла неправильный для скрипта VQMod !';
$_['error_log_size']       = 'Смотрите журнал ошибок %sMBs. Предел для VQMod Менеджера-6 МБ. Если вам нужно просмотреть ошибки, вам необходимо скачать журнал по FTP. В противном случае очистите его ниже.';
$_['error_moddedfile']     = 'Сценарий не может модифицировать файл \'%s\' ввиду его отсутствия!';
$_['error_move']           = 'Не удается сохранить файл на сервере. Пожалуйста, проверьте разрешения  на запись для каталога.';
$_['error_permission']     = 'Вы не имеете права изменять модуль VQMod Менеджер!';
$_['error_uninstall']      = 'Не удается удалить VQMod скрипт!';

// $_FILE Upload Errors
$_['error_form_max_file_size']   = 'Скрипт VQMod превышает максимальный допустимый размер!';
$_['error_ini_max_file_size']    = 'Скрипт VQMod превышает максимальный допустимый размер загружаемого файла в настройках php.ini!';
$_['error_no_temp_dir']          = 'Временная папка не найдена!';
$_['error_no_upload']            = 'Файл для загрузки не выбран!';
$_['error_partial_upload']       = 'Загрузка неполная!';
$_['error_php_conflict']         = 'Неизвестный PHP конфликт!';
$_['error_unknown']              = 'Неизвестная ошибка!';
$_['error_write_fail']           = 'Ошибка записи в VQMod скрипт!';

// VQMod Log Errors
$_['error_mod_aborted']          = 'Модификация прервана';
$_['error_mod_skipped']          = 'Орперация пропущена';

// Success
$_['success_clear_cache']        = 'Кэш VQMod очищен!';
$_['success_clear_log']          = 'Журнал ошибок очищен';
$_['success_delete']             = 'Скрипт VQMod удален!';
$_['success_install']            = 'Скрипт VQMod установлен!!';
$_['success_uninstall']          = 'Скрипт VQMod удален!';
$_['success_upload']             = 'Скрипт VQMod загружен!';

// Javascript Warnings
$_['warning_required_delete']    = 'Внимание! Удаление\\\'vqmod_opencart.xml\\\' Приведет к остановке всех скриптов VQMod. Продолжить?';
$_['warning_required_uninstall'] = 'Внимание! Удаление\\\'vqmod_opencart.xml\\\' Приведет к остановке всех скриптов VQMod. Продолжить?';
$_['warning_vqmod_delete']       = 'Внимание! Удаление скриптов VQMod не может быть отменено! Вы уверены, что хотите это сделать?';

// Version
$_['vqmod_manager_version']      = '1.0.1';
?>
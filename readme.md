## Модуль для удобного создания пользовательских настроек, работает также как и globalPlaceholder

### Из удобств:
* Быстрая работа на ajax
* Поддержка multiTv

###Вывод
* Для вывода настройки в шаблоне [(g_название параметра)]  
* Для вывода multiTV необходимо задать в docid=`0`

Пока для работы multiTV нужно добавить в 37 строку код
```php
elseif(isset($docid) && $docid == 0){
    $tvSettings = array(
        'name' => $tvName,
    );
    $fromJson  = $modx->getConfig('g_'.$tvName);
    $fromJson = json_decode($fromJson,true);
    $fromJson = $fromJson['fieldValue'];
    $fromJson = json_encode($fromJson);
}
```

тоесть, должно быть
```php
if (!empty($fromJson)) {
    $tvSettings = array(
        'name' => $tvName,
        'value' => $fromJson
    );
}elseif(isset($docid) && $docid == 0){
    $tvSettings = array(
        'name' => $tvName,
    );
    $fromJson  = $modx->getConfig('g_'.$tvName);
    $fromJson = json_decode($fromJson,true);
    $fromJson = $fromJson['fieldValue'];
    $fromJson = json_encode($fromJson);
}
else {
    $res = $modx->db->select('*', $modx->getFullTableName('site_tmplvars'), 'name="' . $tvName . '"');
    $tvSettings = $modx->db->getRow($res);
}
```
Потом внесу изменения в ядро multiTV.